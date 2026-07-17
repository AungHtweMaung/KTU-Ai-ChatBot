import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function TeacherAssignmentModal({
    show,
    assignment,
    teachers = [],
    curriculumSubjects = [],
    onClose,
}) {
    const isEdit = Boolean(assignment);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        teacher_id: '',
        curriculum_subject_id: '',
        school_year: '',
    });

    useEffect(() => {
        if (show) {
            setData({
                teacher_id: assignment?.teacher_id ?? '',
                curriculum_subject_id: assignment?.curriculum_subject_id ?? '',
                school_year: assignment?.school_year ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, assignment]);

    const handleSubmit = (e) => {
        e.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => { reset(); onClose(); },
        };

        if (isEdit) {
            put(route('admin.teacher-assignments.update', assignment.id), options);
        } else {
            post(route('admin.teacher-assignments.store'), options);
        }
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal show={show} onClose={onClose} title={isEdit ? 'Edit Assignment' : 'Add Assignment'} size="modal-lg">
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="teacher_id" className="form-label">Teacher</label>
                    <select
                        id="teacher_id"
                        className={`form-select ${errors.teacher_id ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.teacher_id}
                        onChange={(e) => setData('teacher_id', e.target.value)}
                    >
                        <option value="">Select a teacher</option>
                        {teachers.map((t) => (
                            <option key={t.id} value={t.id}>{t.name}</option>
                        ))}
                    </select>
                    {errors.teacher_id && <div className="invalid-feedback">{errors.teacher_id}</div>}
                </div>

                <div className="mb-3">
                    <label htmlFor="curriculum_subject_id" className="form-label">Curriculum Subject</label>
                    <select
                        id="curriculum_subject_id"
                        className={`form-select ${errors.curriculum_subject_id ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.curriculum_subject_id}
                        onChange={(e) => setData('curriculum_subject_id', e.target.value)}
                    >
                        <option value="">Select a curriculum subject</option>
                        {curriculumSubjects.map((cs) => (
                            <option key={cs.id} value={cs.id}>{cs.label}</option>
                        ))}
                    </select>
                    {errors.curriculum_subject_id && (
                        <div className="invalid-feedback">{errors.curriculum_subject_id}</div>
                    )}
                </div>

                <div className="mb-3">
                    <label htmlFor="school_year" className="form-label">School Year</label>
                    <input
                        id="school_year"
                        type="text"
                        placeholder="e.g. 2025-2026"
                        className={`form-control ${errors.school_year ? 'is-invalid' : ''}`}
                        style={fieldStyle}
                        value={data.school_year}
                        onChange={(e) => setData('school_year', e.target.value)}
                    />
                    {errors.school_year && <div className="invalid-feedback">{errors.school_year}</div>}
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
