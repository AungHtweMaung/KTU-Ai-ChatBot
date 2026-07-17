import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function AcademicYearModal({ show, academicYear, majors = [], onClose }) {
    const isEdit = Boolean(academicYear);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        major_id: '',
        year_number: '',
        name: '',
    });

    useEffect(() => {
        if (show) {
            setData({
                major_id: academicYear?.major_id ?? '',
                year_number: academicYear?.year_number ?? '',
                name: academicYear?.name ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, academicYear]);

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
            put(route('admin.academic-years.update', academicYear.id), options);
        } else {
            post(route('admin.academic-years.store'), options);
        }
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal show={show} onClose={onClose} title={isEdit ? 'Edit Academic Year' : 'Add Academic Year'}>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="major_id" className="form-label">Major</label>
                    <select
                        id="major_id"
                        className={`form-select ${errors.major_id ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.major_id}
                        onChange={(e) => setData('major_id', e.target.value)}
                    >
                        <option value="">Select a major</option>
                        {majors.map((m) => (
                            <option key={m.id} value={m.id}>{m.name}</option>
                        ))}
                    </select>
                    {errors.major_id && <div className="invalid-feedback">{errors.major_id}</div>}
                </div>

                <div className="mb-3">
                    <label htmlFor="year_number" className="form-label">Year Number</label>
                    <input
                        id="year_number"
                        type="number"
                        min="1"
                        max="10"
                        className={`form-control ${errors.year_number ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.year_number}
                        onChange={(e) => setData('year_number', e.target.value)}
                    />
                    {errors.year_number && <div className="invalid-feedback">{errors.year_number}</div>}
                </div>

                <div className="mb-3">
                    <label htmlFor="name" className="form-label">Name</label>
                    <input
                        id="name"
                        type="text"
                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="e.g. First Year"
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                </div>

                <div className="d-flex justify-content-end gap-2">
                    <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
                    <button type="submit" className="btn btn-primary" disabled={processing}>
                        {isEdit ? 'Update' : 'Save'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
