import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function FeeModal({ show, fee, majors = [], onClose }) {
    const isEdit = Boolean(fee);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        major_id: '',
        name: '',
        amount: '',
        description: '',
    });

    useEffect(() => {
        if (show) {
            setData({
                major_id: fee?.major_id ?? '',
                name: fee?.name ?? '',
                amount: fee?.amount ?? '',
                description: fee?.description ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, fee]);

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
            put(route('admin.fees.update', fee.id), options);
        } else {
            post(route('admin.fees.store'), options);
        }
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
            title={isEdit ? 'Edit Registration Fee' : 'Add Registration Fee'}
        >
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="major_id" className="form-label">
                        Major <small className="text-muted">(optional — leave empty for all majors)</small>
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

                <div className="mb-3">
                    <label htmlFor="name" className="form-label">
                        Fee Type
                    </label>
                    <input
                        id="name"
                        type="text"
                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        placeholder="e.g. Tuition, Lab Fee, Exam Fee"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                </div>

                <div className="mb-3">
                    <label htmlFor="amount" className="form-label">
                        Amount (MMK)
                    </label>
                    <div className="input-group">
                        <input
                            id="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            className={`form-control ${errors.amount ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.amount}
                            onChange={(e) => setData('amount', e.target.value)}
                        />
                        <span className="input-group-text" style={fieldStyle}>
                            MMK
                        </span>
                        {errors.amount && (
                            <div className="invalid-feedback">{errors.amount}</div>
                        )}
                    </div>
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
