import Modal from './Modal';

export default function TeacherAssignmentViewModal({ show, assignment, onClose }) {
    const cs = assignment?.curriculum_subject;

    return (
        <Modal show={show} onClose={onClose} title="Assignment Details">
            {assignment && (
                <dl className="row mb-0">
                    <dt className="col-sm-4 text-muted fw-normal">Teacher</dt>
                    <dd className="col-sm-8">{assignment.teacher?.name ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Email</dt>
                    <dd className="col-sm-8">{assignment.teacher?.email ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Major</dt>
                    <dd className="col-sm-8">{cs?.major?.name ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Year / Semester</dt>
                    <dd className="col-sm-8">
                        {cs?.major_year
                            ? `Year ${cs.major_year.year_number} — Semester ${cs.semester}`
                            : '—'}
                    </dd>

                    <dt className="col-sm-4 text-muted fw-normal">Subject</dt>
                    <dd className="col-sm-8">
                        {cs?.subject ? `${cs.subject.code} — ${cs.subject.name}` : '—'}
                    </dd>

                    <dt className="col-sm-4 text-muted fw-normal">School Year</dt>
                    <dd className="col-sm-8">{assignment.school_year}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Created</dt>
                    <dd className="col-sm-8">{new Date(assignment.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-8 mb-0">{new Date(assignment.updated_at).toLocaleString()}</dd>
                </dl>
            )}
            <div className="d-flex justify-content-end mt-3">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Close</button>
            </div>
        </Modal>
    );
}
