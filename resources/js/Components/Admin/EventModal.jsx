import { useEffect, useMemo, useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

/**
 * Convert an ISO/date string to the `YYYY-MM-DDTHH:mm` format that
 * <input type="datetime-local"> expects, in the local timezone.
 */
const toLocalInputValue = (value) => {
    if (!value) return '';
    const d = new Date(value);
    if (isNaN(d)) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return (
        d.getFullYear() +
        '-' +
        pad(d.getMonth() + 1) +
        '-' +
        pad(d.getDate()) +
        'T' +
        pad(d.getHours()) +
        ':' +
        pad(d.getMinutes())
    );
};

export default function EventModal({ show, event, majors = [], majorYears = [], onClose }) {
    const isEdit = Boolean(event);
    const fileInputRef = useRef(null);
    const [preview, setPreview] = useState(null);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        _method: 'post',
        title: '',
        description: '',
        type: '',
        location: '',
        major_id: '',
        major_year_id: '',
        starts_at: '',
        ends_at: '',
        cover_image: null,
        is_published: true,
    });

    useEffect(() => {
        if (show) {
            setData({
                _method: isEdit ? 'put' : 'post',
                title: event?.title ?? '',
                description: event?.description ?? '',
                type: event?.type ?? '',
                location: event?.location ?? '',
                major_id: event?.major_id ?? '',
                major_year_id: event?.major_year_id ?? '',
                starts_at: toLocalInputValue(event?.starts_at),
                ends_at: toLocalInputValue(event?.ends_at),
                cover_image: null,
                is_published: event?.is_published ?? true,
            });
            setPreview(event?.cover_image_url ?? null);
            clearErrors();
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, event]);

    // Cascade: filter major years by selected major (if any).
    const filteredYears = useMemo(() => {
        if (!data.major_id) return majorYears;
        return majorYears.filter((y) => String(y.major_id) === String(data.major_id));
    }, [data.major_id, majorYears]);

    // If the selected year no longer matches the selected major, clear it.
    useEffect(() => {
        if (data.major_year_id && data.major_id) {
            const stillValid = filteredYears.some(
                (y) => String(y.id) === String(data.major_year_id),
            );
            if (!stillValid) setData('major_year_id', '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.major_id]);

    const handleImageChange = (e) => {
        const file = e.target.files?.[0] ?? null;
        setData('cover_image', file);
        setPreview(file ? URL.createObjectURL(file) : (event?.cover_image_url ?? null));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const url = isEdit
            ? route('admin.events.update', event.id)
            : route('admin.events.store');

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
            title={isEdit ? 'Edit Event' : 'Add Event'}
            size="modal-lg"
        >
            <form onSubmit={handleSubmit}>
                <div className="row g-3">
                    <div className="col-md-4">
                        <div
                            className="rounded d-flex align-items-center justify-content-center overflow-hidden mb-2"
                            style={{
                                aspectRatio: '4 / 3',
                                backgroundColor: 'var(--bs-tertiary-bg, #f8f9fa)',
                                border: '1px solid var(--bs-border-color)',
                            }}
                        >
                            {preview ? (
                                <img
                                    src={preview}
                                    alt="Preview"
                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                />
                            ) : (
                                <i
                                    className="bi bi-image text-muted"
                                    style={{ fontSize: '3rem' }}
                                ></i>
                            )}
                        </div>
                        <input
                            ref={fileInputRef}
                            type="file"
                            id="cover_image"
                            accept="image/jpeg,image/png,image/webp"
                            className={`form-control form-control-sm ${errors.cover_image ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            onChange={handleImageChange}
                        />
                        {errors.cover_image && (
                            <div className="invalid-feedback d-block">{errors.cover_image}</div>
                        )}
                        <small className="text-muted d-block mt-1">
                            Cover image. JPG/PNG/WebP, max 2MB.
                        </small>
                    </div>

                    <div className="col-md-8">
                        <div className="mb-3">
                            <label htmlFor="title" className="form-label">
                                Title
                            </label>
                            <input
                                id="title"
                                type="text"
                                className={`form-control ${errors.title ? 'is-invalid' : ''}`}
                                style={fieldStyle}
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                            />
                            {errors.title && <div className="invalid-feedback">{errors.title}</div>}
                        </div>

                        <div className="row g-3">
                            <div className="col-md-6">
                                <label htmlFor="type" className="form-label">
                                    Type
                                </label>
                                <input
                                    id="type"
                                    type="text"
                                    className={`form-control ${errors.type ? 'is-invalid' : ''}`}
                                    style={fieldStyle}
                                    placeholder="e.g. academic, sports, seminar"
                                    value={data.type}
                                    onChange={(e) => setData('type', e.target.value)}
                                />
                                {errors.type && (
                                    <div className="invalid-feedback">{errors.type}</div>
                                )}
                            </div>

                            <div className="col-md-6">
                                <label htmlFor="location" className="form-label">
                                    Location
                                </label>
                                <input
                                    id="location"
                                    type="text"
                                    className={`form-control ${errors.location ? 'is-invalid' : ''}`}
                                    style={fieldStyle}
                                    value={data.location}
                                    onChange={(e) => setData('location', e.target.value)}
                                />
                                {errors.location && (
                                    <div className="invalid-feedback">{errors.location}</div>
                                )}
                            </div>

                            <div className="col-md-6">
                                <label htmlFor="starts_at" className="form-label">
                                    Starts At
                                </label>
                                <input
                                    id="starts_at"
                                    type="datetime-local"
                                    className={`form-control ${errors.starts_at ? 'is-invalid' : ''}`}
                                    style={fieldStyle}
                                    value={data.starts_at}
                                    onChange={(e) => setData('starts_at', e.target.value)}
                                />
                                {errors.starts_at && (
                                    <div className="invalid-feedback">{errors.starts_at}</div>
                                )}
                            </div>

                            <div className="col-md-6">
                                <label htmlFor="ends_at" className="form-label">
                                    Ends At
                                </label>
                                <input
                                    id="ends_at"
                                    type="datetime-local"
                                    className={`form-control ${errors.ends_at ? 'is-invalid' : ''}`}
                                    style={fieldStyle}
                                    value={data.ends_at}
                                    onChange={(e) => setData('ends_at', e.target.value)}
                                />
                                {errors.ends_at && (
                                    <div className="invalid-feedback">{errors.ends_at}</div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-md-6">
                        <label htmlFor="major_id" className="form-label">
                            Target Major <small className="text-muted">(optional)</small>
                        </label>
                        <select
                            id="major_id"
                            className={`form-select ${errors.major_id ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.major_id}
                            onChange={(e) => setData('major_id', e.target.value)}
                        >
                            <option value="">All majors</option>
                            {majors.map((m) => (
                                <option key={m.id} value={m.id}>
                                    {m.name}
                                </option>
                            ))}
                        </select>
                        {errors.major_id && (
                            <div className="invalid-feedback">{errors.major_id}</div>
                        )}
                    </div>

                    <div className="col-md-6">
                        <label htmlFor="major_year_id" className="form-label">
                            Target Year <small className="text-muted">(optional)</small>
                        </label>
                        <select
                            id="major_year_id"
                            className={`form-select ${errors.major_year_id ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.major_year_id}
                            onChange={(e) => setData('major_year_id', e.target.value)}
                        >
                            <option value="">All years</option>
                            {filteredYears.map((y) => (
                                <option key={y.id} value={y.id}>
                                    {y.name}
                                </option>
                            ))}
                        </select>
                        {errors.major_year_id && (
                            <div className="invalid-feedback">{errors.major_year_id}</div>
                        )}
                    </div>

                    <div className="col-12">
                        <label htmlFor="description" className="form-label">
                            Description
                        </label>
                        <textarea
                            id="description"
                            rows={4}
                            className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        {errors.description && (
                            <div className="invalid-feedback">{errors.description}</div>
                        )}
                    </div>

                    <div className="col-12">
                        <div className="form-check">
                            <input
                                id="is_published"
                                type="checkbox"
                                className="form-check-input"
                                checked={data.is_published}
                                onChange={(e) => setData('is_published', e.target.checked)}
                            />
                            <label htmlFor="is_published" className="form-check-label">
                                Published (visible to students)
                            </label>
                        </div>
                    </div>
                </div>

                <div className="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                        Cancel
                    </button>
                    <button type="submit" className="btn btn-primary" disabled={processing}>
                        {isEdit ? 'Update' : 'Save'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
