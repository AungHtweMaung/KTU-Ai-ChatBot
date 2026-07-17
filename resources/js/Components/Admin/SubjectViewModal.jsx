import Modal from './Modal';

export default function SubjectViewModal({ show, subject, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Subject Details">
            {subject && (
                <dl className="row mb-0">
                    <dt className="col-sm-4 text-muted fw-normal">Code</dt>
                    <dd className="col-sm-8">{subject.code}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Name</dt>
                    <dd className="col-sm-8">{subject.name}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Credits</dt>
                    <dd className="col-sm-8">{subject.credits}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Description</dt>
                    <dd className="col-sm-8">{subject.description || '—'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Created</dt>
                    <dd className="col-sm-8">{new Date(subject.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-8 mb-0">{new Date(subject.updated_at).toLocaleString()}</dd>
                </dl>
            )}
            <div className="d-flex justify-content-end mt-3">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Close</button>
            </div>
        </Modal>
    );
}
