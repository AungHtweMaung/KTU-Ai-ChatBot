import Modal from './Modal';

export default function CurriculumSubjectViewModal({ show, curriculumSubject, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Curriculum Subject Details">
            {curriculumSubject && (
                <dl className="row mb-0">
                    <dt className="col-sm-4 text-muted fw-normal">Major</dt>
                    <dd className="col-sm-8">{curriculumSubject.major?.name ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Academic Year</dt>
                    <dd className="col-sm-8">
                        {curriculumSubject.major_year
                            ? `Year ${curriculumSubject.major_year.year_number} — ${curriculumSubject.major_year.name}`
                            : '—'}
                    </dd>

                    <dt className="col-sm-4 text-muted fw-normal">Subject</dt>
                    <dd className="col-sm-8">
                        {curriculumSubject.subject
                            ? `${curriculumSubject.subject.code} — ${curriculumSubject.subject.name}`
                            : '—'}
                    </dd>

                    <dt className="col-sm-4 text-muted fw-normal">Credits</dt>
                    <dd className="col-sm-8">{curriculumSubject.subject?.credits ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Semester</dt>
                    <dd className="col-sm-8">Semester {curriculumSubject.semester}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Created</dt>
                    <dd className="col-sm-8">{new Date(curriculumSubject.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-8 mb-0">
                        {new Date(curriculumSubject.updated_at).toLocaleString()}
                    </dd>
                </dl>
            )}
            <div className="d-flex justify-content-end mt-3">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Close</button>
            </div>
        </Modal>
    );
}
