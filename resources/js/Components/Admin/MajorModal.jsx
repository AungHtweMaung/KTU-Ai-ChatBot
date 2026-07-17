import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function MajorModal({ show, major, departments = [], onClose }) {
    const isEdit = Boolean(major);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        department_id: '',
        name: '',
        description: '',
    });

    useEffect(() => {
        if (show) {
            setData({
                department_id: major?.department_id ?? '',
                name: major?.name ?? '',
                description: major?.description ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, major]);

    const handleSubmit = (e) => {
        e.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        };

        if (isEdit) {
            put(route('admin.majors.update', major.id), options);
        } else {
            post(route('admin.majors.store'), options);
        }
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal show={show} onClose={onClose} title={isEdit ? 'Edit Major' : 'Add Major'}>
            <form onSubmit={handleSubmit}>
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
                    <label htmlFor="description" className="form-label">
                        Description
                    </label>
                    <textarea
                        id="description"
                        rows={3}
                        className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                    {errors.description && (
                        <div className="invalid-feedback">{errors.description}</div>
                    )}
                </div>

                <div className="d-flex justify-content-end gap-2">
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
