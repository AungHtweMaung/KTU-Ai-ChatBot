import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function DepartmentModal({ show, department, onClose }) {
    const isEdit = Boolean(department);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        name: '',
        code: '',
        description: '',
    });

    useEffect(() => {
        if (show) {
            setData({
                name: department?.name ?? '',
                code: department?.code ?? '',
                description: department?.description ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, department]);

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
            put(route('admin.departments.update', department.id), options);
        } else {
            post(route('admin.departments.store'), options);
        }
    };

    return (
        <Modal show={show} onClose={onClose} title={isEdit ? 'Edit Department' : 'Add Department'}>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="name" className="form-label">
                        Name
                    </label>
                    <input
                        id="name"
                        type="text"
                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                        style={{
                            backgroundColor: 'var(--bs-body-bg)',
                            color: 'var(--bs-body-color)',
                            borderColor: 'var(--bs-border-color)',
                        }}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                </div>

                <div className="mb-3">
                    <label htmlFor="code" className="form-label">
                        Code
                    </label>
                    <input
                        id="code"
                        type="text"
                        className={`form-control ${errors.code ? 'is-invalid' : ''}`}
                        style={{
                            backgroundColor: 'var(--bs-body-bg)',
                            color: 'var(--bs-body-color)',
                            borderColor: 'var(--bs-border-color)',
                        }}
                        value={data.code}
                        onChange={(e) => setData('code', e.target.value)}
                    />
                    {errors.code && <div className="invalid-feedback">{errors.code}</div>}
                </div>

                <div className="mb-3">
                    <label htmlFor="description" className="form-label">
                        Description
                    </label>
                    <textarea
                        id="description"
                        rows={3}
                        className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                        style={{
                            backgroundColor: 'var(--bs-body-bg)',
                            color: 'var(--bs-body-color)',
                            borderColor: 'var(--bs-border-color)',
                        }}
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
