import { useEffect, useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import {
    imageValidationError,
    prepareImageForUpload,
    TIMETABLE_IMAGE_TYPES,
    TIMETABLE_MAX_BYTES,
    TARGET_IMAGE_BYTES,
} from '../../lib/imageUpload';
import Modal from './Modal';

const classLabel = (cls) => `${cls.name} — ${cls.major?.name ?? '—'}`;

export default function TimetableModal({ show, timetable, classes = [], onClose }) {
    const isEdit = Boolean(timetable);
    const fileInputRef = useRef(null);
    const [preview, setPreview] = useState(null);
    const [localError, setLocalError] = useState(null);
    const [optimising, setOptimising] = useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        _method: 'post',
        major_year_id: '',
        semester: '',
        image: null,
    });

    useEffect(() => {
        if (show) {
            setData({
                _method: isEdit ? 'put' : 'post',
                major_year_id: timetable?.major_year_id ?? '',
                semester: timetable?.semester ?? '',
                image: null,
            });
            setPreview(timetable?.image_url ?? null);
            setLocalError(null);
            setOptimising(false);
            clearErrors();
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, timetable]);

    const handleImageChange = async (e) => {
        const file = e.target.files?.[0] ?? null;

        if (!file) {
            setData('image', null);
            setPreview(timetable?.image_url ?? null);
            setLocalError(null);

            return;
        }

        const invalid = imageValidationError(file, {
            maxBytes: TIMETABLE_MAX_BYTES,
            acceptedTypes: TIMETABLE_IMAGE_TYPES,
        });

        if (invalid) {
            // Refuse before the request is built. An oversized multipart body is
            // rejected by PHP before Laravel runs, so letting it through would
            // show a bare "413 Content Too Large" page instead of this message.
            setData('image', null);
            setPreview(timetable?.image_url ?? null);
            setLocalError(invalid);
            if (fileInputRef.current) fileInputRef.current.value = '';

            return;
        }

        setLocalError(null);
        setPreview(URL.createObjectURL(file));
        setData('image', file);

        if (file.size > TARGET_IMAGE_BYTES) {
            setOptimising(true);

            try {
                const prepared = await prepareImageForUpload(file);

                setData('image', prepared);
                setPreview(URL.createObjectURL(prepared));
            } finally {
                setOptimising(false);
            }
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (localError || optimising) {
            return;
        }

        const url = isEdit
            ? route('admin.timetable.update', timetable.id)
            : route('admin.timetable.store');

        post(url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setPreview(null);
                onClose();
            },
        });
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal
            show={show}
            onClose={onClose}
            title={isEdit ? 'Edit Timetable' : 'Add Timetable'}
            size="modal-lg"
        >
            <form onSubmit={handleSubmit}>
                <div className="row g-3">
                    <div className="col-md-6">
                        <label htmlFor="major_year_id" className="form-label">
                            Class
                        </label>
                        <select
                            id="major_year_id"
                            className={`form-select ${errors.major_year_id ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.major_year_id}
                            onChange={(e) => setData('major_year_id', e.target.value)}
                        >
                            <option value="">Select a class</option>
                            {classes.map((cls) => (
                                <option key={cls.id} value={cls.id}>
                                    {classLabel(cls)}
                                </option>
                            ))}
                        </select>
                        {errors.major_year_id && (
                            <div className="invalid-feedback">{errors.major_year_id}</div>
                        )}
                    </div>

                    <div className="col-md-6">
                        <label htmlFor="semester" className="form-label">
                            Semester
                        </label>
                        <select
                            id="semester"
                            className={`form-select ${errors.semester ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.semester}
                            onChange={(e) => setData('semester', e.target.value)}
                        >
                            <option value="">Select a semester</option>
                            <option value="1">First Semester</option>
                            <option value="2">Second Semester</option>
                        </select>
                        {errors.semester && (
                            <div className="invalid-feedback">{errors.semester}</div>
                        )}
                    </div>

                    <div className="col-12">
                        <label htmlFor="image" className="form-label">
                            Timetable Image
                        </label>
                        <input
                            ref={fileInputRef}
                            type="file"
                            id="image"
                            accept="image/jpeg,image/png"
                            className={`form-control ${errors.image || localError ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            onChange={handleImageChange}
                        />
                        {(localError || errors.image) && (
                            <div className="invalid-feedback d-block">{localError ?? errors.image}</div>
                        )}
                        <small className="text-muted d-block mt-1">
                            JPG or PNG, up to 8MB. Larger images are optimised automatically before
                            upload.
                            {isEdit && ' Leave empty to keep the current image.'}
                        </small>

                        {preview && (
                            <div
                                className="mt-3 rounded overflow-hidden"
                                style={{ border: '1px solid var(--bs-border-color)' }}
                            >
                                <img
                                    src={preview}
                                    alt="Timetable preview"
                                    style={{ width: '100%', height: 'auto', display: 'block' }}
                                />
                            </div>
                        )}
                    </div>
                </div>

                <div className="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                        Cancel
                    </button>
                    <button type="submit" className="btn btn-primary" disabled={processing || optimising}>
                        {optimising ? 'Optimising…' : isEdit ? 'Update' : 'Save'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
