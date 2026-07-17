import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function SubjectModal({ show, subject, onClose }) {
    const isEdit = Boolean(subject);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        code: '',
        name: '',
        credits: 3,
        description: '',
    });

    useEffect(() => {
        if (show) {
            setData({
                code: subject?.code ?? '',
                name: subject?.name ?? '',
                credits: subject?.credits ?? 3,
                description: subject?.description ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, subject]);

    const handleSubmit = (e) => {
        e.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => { reset(); onClose(); },
        };

        if (isEdit) {
            put(route('admin.subjects.update', subject.id), options);
        } else {
            post(route('admin.subjects.store'), options);
        }
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal show={show} onClose={onClose} title={isEdit ? 'Edit Subject' : 'Add Subject'}>
            <form onSubmit={handleSubmit}>
                <div className="row g-3">
                    <div className="col-md-4">
                        <label htmlFor="code" className="form-label">Code</label>
                        <input
                            id="code"
                            type="text"
                            className={`form-control ${errors.code ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                        />
                        {errors.code && <div className="invalid-feedback">{errors.code}</div>}
                    </div>
                    <div className="col-md-8">
                        <label htmlFor="name" className="form-label">Name</label>
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

                    <div className="col-md-4">
                        <label htmlFor="credits" className="form-label">Credits</label>
                        <input
                            id="credits"
                            type="number"
                            min="0"
                            max="20"
                            className={`form-control ${errors.credits ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.credits}
                            onChange={(e) => setData('credits', e.target.value)}
                        />
                        {errors.credits && <div className="invalid-feedback">{errors.credits}</div>}
                    </div>

                    <div className="col-12">
                        <label htmlFor="description" className="form-label">Description</label>
                        <textarea
                            id="description"
                            rows={3}
                            className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        {errors.description && <div className="invalid-feedback">{errors.description}</div>}
                    </div>
                </div>

                <div className="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
                    <button type="submit" className="btn btn-primary" disabled={processing}>
                        {isEdit ? 'Update' : 'Save'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
