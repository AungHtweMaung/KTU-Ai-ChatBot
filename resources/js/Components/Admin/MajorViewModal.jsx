import Modal from './Modal';

export default function MajorViewModal({ show, major, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Major Details">
            {major && (
                <dl className="row mb-0">
                    <dt className="col-sm-4 text-muted fw-normal">Department</dt>
                    <dd className="col-sm-8">{major.department?.name ?? '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Name</dt>
                    <dd className="col-sm-8">{major.name}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Description</dt>
                    <dd className="col-sm-8">{major.description || '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Created Date</dt>
                    <dd className="col-sm-8">{new Date(major.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-8 mb-0">
                        {new Date(major.updated_at).toLocaleString()}
                    </dd>
                </dl>
            )}

            <div className="d-flex justify-content-end mt-3">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                    Close
                </button>
            </div>
        </Modal>
    );
}
