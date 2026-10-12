/**
 * Client-side preparation for image uploads.
 *
 * The browser is the only place an oversized upload can be stopped cleanly.
 * `post_max_size` / `upload_max_filesize` (PHP) and `client_max_body_size`
 * (nginx) all live outside the application, and when a request exceeds them
 * PHP discards the body and Laravel's ValidatePostSize middleware throws
 * before any of our code runs — so there is no validation message to show,
 * only a bare "413 Content Too Large" page.
 *
 * So we do two things here, in order:
 *
 *   1. reject what is clearly too big, with a message that names the limit;
 *   2. shrink what is merely large, so a 6 MB phone photo still uploads.
 *
 * Compression is best-effort: if the browser cannot decode or re-encode the
 * file we hand back the original rather than block the upload.
 */

/** What we aim to actually send — comfortably under a default 2 MB server cap. */
export const TARGET_IMAGE_BYTES = 1.5 * 1024 * 1024;

/** Mirrors `max:8192` on the timetable request. */
export const TIMETABLE_MAX_BYTES = 8 * 1024 * 1024;

/** Mirrors `max:2048` on the teacher and event requests. */
export const PHOTO_MAX_BYTES = 2 * 1024 * 1024;

export const TIMETABLE_IMAGE_TYPES = ['image/jpeg', 'image/png'];

export const PHOTO_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

/** Longest edge of a re-encoded image. Enough to read a photographed timetable. */
const MAX_DIMENSION = 2000;

const JPEG_QUALITY = 0.82;

export function formatBytes(bytes) {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    return `${Math.max(1, Math.round(bytes / 1024))} KB`;
}

/**
 * Check a file the user just picked.
 *
 * @returns {string|null} a message to show under the field, or null when fine.
 */
export function imageValidationError(file, options = {}) {
    const { maxBytes = PHOTO_MAX_BYTES, acceptedTypes = PHOTO_IMAGE_TYPES } = options;

    if (!file) {
        return null;
    }

    if (!acceptedTypes.includes(file.type)) {
        const labels = acceptedTypes.map((type) => type.replace('image/', '').toUpperCase());

        return `That file type is not supported. Please choose ${labels.join(', ')}.`;
    }

    if (file.size > maxBytes) {
        return `That image is ${formatBytes(file.size)}. The largest we can accept is ${formatBytes(maxBytes)}.`;
    }

    return null;
}

/**
 * Shrink an image so it survives the server's upload limits.
 *
 * Returns the original file when it is already small enough, or when the
 * browser could not re-encode it.
 *
 * @returns {Promise<File>}
 */
export async function prepareImageForUpload(file) {
    if (!file || file.size <= TARGET_IMAGE_BYTES) {
        return file;
    }

    try {
        return await compress(file);
    } catch {
        return file;
    }
}

async function compress(file) {
    const source = await decode(file);

    const scale = Math.min(1, MAX_DIMENSION / Math.max(source.width, source.height));
    const width = Math.max(1, Math.round(source.width * scale));
    const height = Math.max(1, Math.round(source.height * scale));

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Canvas is unavailable.');
    }

    // JPEG has no alpha channel, so flatten onto white first — otherwise a
    // transparent PNG would come back with a black background.
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, width, height);
    context.drawImage(source, 0, 0, width, height);

    if (typeof source.close === 'function') {
        source.close();
    }

    const blob = await toJpegBlob(canvas);

    // A "compressed" file that is larger than the original helps nobody.
    if (!blob || blob.size >= file.size) {
        return file;
    }

    return new File([blob], withJpegExtension(file.name), {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}

/**
 * Decode to something drawable, preferring createImageBitmap because it can
 * honour the EXIF orientation tag that phone cameras write.
 */
async function decode(file) {
    if (typeof createImageBitmap === 'function') {
        try {
            return await createImageBitmap(file, { imageOrientation: 'from-image' });
        } catch {
            // Older Safari rejects the options bag — fall through to <img>.
        }
    }

    return await loadViaImageElement(file);
}

function loadViaImageElement(file) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        const url = URL.createObjectURL(file);

        image.onload = () => {
            URL.revokeObjectURL(url);
            resolve(image);
        };

        image.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('The image could not be read.'));
        };

        image.src = url;
    });
}

function toJpegBlob(canvas) {
    return new Promise((resolve) => {
        if (typeof canvas.toBlob !== 'function') {
            resolve(null);

            return;
        }

        canvas.toBlob((blob) => resolve(blob), 'image/jpeg', JPEG_QUALITY);
    });
}

function withJpegExtension(name) {
    const base = (name || 'image').replace(/\.[^./\\]+$/, '');

    return `${base}.jpg`;
}
