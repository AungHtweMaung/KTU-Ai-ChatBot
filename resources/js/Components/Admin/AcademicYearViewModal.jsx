import Modal from './Modal';

export default function AcademicYearViewModal({ show, academicYear, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Academic Year Details">
            {academicYear && (
                <dl className="row mb-0">
                    <dt className="col-sm-4 text-muted fw-normal">Major</dt>
                    <dd className="col-sm-8">{academicYear.major?.name ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Year Number</dt>
                    <dd className="col-sm-8">{academicYear.year_number}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Name</dt>
                    <dd className="col-sm-8">{academicYear.name}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Created</dt>
                    <dd className="col-sm-8">{new Date(academicYear.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-8 mb-0">
                        {new Date(academicYear.updated_at).toLocaleString()}
                    </dd>
                </dl>
            )}

            <div className="d-flex justify-content-end mt-3">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Close</button>
            </div>
        </Modal>
    );
}
