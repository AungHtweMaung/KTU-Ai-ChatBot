import { useEffect, useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function TeacherModal({ show, teacher, departments = [], onClose }) {
    const isEdit = Boolean(teacher);
    const fileInputRef = useRef(null);
    const [preview, setPreview] = useState(null);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        _method: 'post',
        department_id: '',
        name: '',
        email: '',
        phone: '',
        position: '',
        degree: '',
        bio: '',
        image: null,
    });

    useEffect(() => {
        if (show) {
            setData({
                _method: isEdit ? 'put' : 'post',
                department_id: teacher?.department_id ?? '',
                name: teacher?.name ?? '',
                email: teacher?.email ?? '',
                phone: teacher?.phone ?? '',
                position: teacher?.position ?? '',
                degree: teacher?.degree ?? '',
                bio: teacher?.bio ?? '',
                image: null,
            });
            setPreview(teacher?.image_url ?? null);
            clearErrors();
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, teacher]);

    const handleImageChange = (e) => {
        const file = e.target.files?.[0] ?? null;
        setData('image', file);
        setPreview(file ? URL.createObjectURL(file) : (teacher?.image_url ?? null));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const url = isEdit
            ? route('admin.teachers.update', teacher.id)
            : route('admin.teachers.store');

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
            title={isEdit ? 'Edit Teacher' : 'Add Teacher'}
            size="modal-lg"
        >
            <form onSubmit={handleSubmit}>
                <div className="row g-3">
                    <div className="col-md-4 text-center">
                        <div
                            className="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center overflow-hidden"
                            style={{
                                width: 120,
                                height: 120,
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
                                <i className="bi bi-person text-muted" style={{ fontSize: '3rem' }}></i>
                            )}
                        </div>
                        <input
                            ref={fileInputRef}
                            type="file"
                            id="image"
                            accept="image/jpeg,image/png,image/webp"
                            className={`form-control form-control-sm ${errors.image ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            onChange={handleImageChange}
                        />
                        {errors.image && <div className="invalid-feedback d-block">{errors.image}</div>}
                        <small className="text-muted d-block mt-1">JPG, PNG, or WebP. Max 2MB.</small>
                    </div>

                    <div className="col-md-8">
                        <div className="mb-3">
                            <label htmlFor="department_id" className="form-label">
                                Department
                            </label>
                            <select
                                id="department_id"
                                className={`form-select ${errors.department_id ? 'is-invalid' : ''}`}
                                style={fieldStyle}
                                value={data.department_id}
                                onChange={(e) => setData('department_id', e.target.value)}
                            >
                                <option value="">Select a department</option>
                                {departments.map((department) => (
                                    <option key={department.id} value={department.id}>
                                        {department.name}
                                    </option>
                                ))}
                            </select>
                            {errors.department_id && (
                                <div className="invalid-feedback">{errors.department_id}</div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label htmlFor="name" className="form-label">
                                Name
                            </label>
                            <input
                                id="name"
                                type="text"
                                className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                style={fieldStyle}
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                            />
                            {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                        </div>

                        <div className="mb-3">
                            <label htmlFor="email" className="form-label">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                style={fieldStyle}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                        </div>
                    </div>

                    <div className="col-md-6">
                        <label htmlFor="phone" className="form-label">
                            Phone
                        </label>
                        <input
                            id="phone"
                            type="text"
                            className={`form-control ${errors.phone ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                        {errors.phone && <div className="invalid-feedback">{errors.phone}</div>}
                    </div>

                    <div className="col-md-6">
                        <label htmlFor="position" className="form-label">
                            Position
                        </label>
                        <input
                            id="position"
                            type="text"
                            className={`form-control ${errors.position ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.position}
                            onChange={(e) => setData('position', e.target.value)}
                        />
                        {errors.position && (
                            <div className="invalid-feedback">{errors.position}</div>
                        )}
                    </div>

                    <div className="col-md-6">
                        <label htmlFor="degree" className="form-label">
                            Degree
                        </label>
                        <input
                            id="degree"
                            type="text"
                            className={`form-control ${errors.degree ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.degree}
                            onChange={(e) => setData('degree', e.target.value)}
                        />
                        {errors.degree && <div className="invalid-feedback">{errors.degree}</div>}
                    </div>

                    <div className="col-12">
                        <label htmlFor="bio" className="form-label">
                            Bio
                        </label>
                        <textarea
                            id="bio"
                            rows={3}
                            className={`form-control ${errors.bio ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.bio}
                            onChange={(e) => setData('bio', e.target.value)}
                        />
                        {errors.bio && <div className="invalid-feedback">{errors.bio}</div>}
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
