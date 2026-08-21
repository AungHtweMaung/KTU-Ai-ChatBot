/**
 * Kyaukse Technological University crest.
 *
 * Rendered directly as an <img> so the transparent PNG shows cleanly without
 * any surrounding box or gradient. The source file lives at
 * `public/images/ktu-logo.png` — swap that single file to update the crest
 * everywhere.
 */
export default function KtuLogo({ size = 42, className = '', alt = 'KTU', style = {} }) {
    return (
        <img
            src="/images/ktu-logo.png"
            alt={alt}
            width={size}
            height={size}
            className={className}
            style={{ objectFit: 'contain', ...style }}
            draggable="false"
        />
    );
}
