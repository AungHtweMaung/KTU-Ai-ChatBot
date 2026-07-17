import { useEffect, useMemo } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function CurriculumSubjectModal({
    show,
    curriculumSubject,
    majors = [],
    majorYears = [],
    subjects = [],
    onClose,
}) {
    const isEdit = Boolean(curriculumSubject);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        major_id: '',
        major_year_id: '',
        subject_id: '',
        semester: 1,
    });

    useEffect(() => {
        if (show) {
            setData({
                major_id: curriculumSubject?.major_id ?? '',
                major_year_id: curriculumSubject?.major_year_id ?? '',
                subject_id: curriculumSubject?.subject_id ?? '',
                semester: curriculumSubject?.semester ?? 1,
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, curriculumSubject]);

    // Years filtered by selected major
    const filteredYears = useMemo(
        () => majorYears.filter((y) => String(y.major_id) === String(data.major_id)),
        [majorYears, data.major_id],
    );

    // If the selected year no longer matches the selected major (e.g. major changed),
    // clear it silently so the submit picks up a valid year_number.
    useEffect(() => {
        if (data.major_year_id && !filteredYears.some((y) => String(y.id) === String(data.major_year_id))) {
            setData('major_year_id', '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.major_id]);

    const handleSubmit = (e) => {
        e.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => { reset(); onClose(); },
        };

        if (isEdit) {
            put(route('admin.curriculum-subjects.update', curriculumSubject.id), options);
        } else {
            post(route('admin.curriculum-subjects.store'), options);
        }
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal show={show} onClose={onClose} title={isEdit ? 'Edit Curriculum Subject' : 'Add Curriculum Subject'}>
            <form onSubmit={handleSubmit}>
                <div className="row g-3">
                    <div className="col-md-6">
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

                    <div className="col-md-6">
                        <label htmlFor="major_year_id" className="form-label">Academic Year</label>
                        <select
                            id="major_year_id"
                            className={`form-select ${errors.major_year_id ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.major_year_id}
                            onChange={(e) => setData('major_year_id', e.target.value)}
                            disabled={!data.major_id}
                        >
                            <option value="">
                                {data.major_id ? 'Select a year' : 'Select a major first'}
                            </option>
                            {filteredYears.map((y) => (
                                <option key={y.id} value={y.id}>
                                    Year {y.year_number} — {y.name}
                                </option>
                            ))}
                        </select>
                        {errors.major_year_id && <div className="invalid-feedback">{errors.major_year_id}</div>}
                    </div>

                    <div className="col-md-8">
                        <label htmlFor="subject_id" className="form-label">Subject</label>
                        <select
                            id="subject_id"
                            className={`form-select ${errors.subject_id ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.subject_id}
                            onChange={(e) => setData('subject_id', e.target.value)}
                        >
                            <option value="">Select a subject</option>
                            {subjects.map((s) => (
                                <option key={s.id} value={s.id}>{s.code} — {s.name}</option>
                            ))}
                        </select>
                        {errors.subject_id && <div className="invalid-feedback">{errors.subject_id}</div>}
                    </div>

                    <div className="col-md-4">
                        <label htmlFor="semester" className="form-label">Semester</label>
                        <select
                            id="semester"
                            className={`form-select ${errors.semester ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.semester}
                            onChange={(e) => setData('semester', e.target.value)}
                        >
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                        {errors.semester && <div className="invalid-feedback">{errors.semester}</div>}
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
