import Modal from './Modal';

export default function FaqViewModal({ show, faq, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="FAQ Details" size="modal-lg">
            {faq && (
                <dl className="row mb-0">
                    <dt className="col-sm-3 text-muted fw-normal">Category</dt>
                    <dd className="col-sm-9">{faq.category || '—'}</dd>

                    <dt className="col-sm-3 text-muted fw-normal">Question</dt>
                    <dd className="col-sm-9" style={{ whiteSpace: 'pre-wrap' }}>
                        {faq.question}
                    </dd>

                    <dt className="col-sm-3 text-muted fw-normal">Answer</dt>
                    <dd className="col-sm-9" style={{ whiteSpace: 'pre-wrap' }}>
                        {faq.answer}
                    </dd>

                    <dt className="col-sm-3 text-muted fw-normal">Sort Order</dt>
                    <dd className="col-sm-9">{faq.sort_order}</dd>

                    <dt className="col-sm-3 text-muted fw-normal">Status</dt>
                    <dd className="col-sm-9">
                        <span
                            className={`badge ${faq.is_published ? 'text-bg-success' : 'text-bg-secondary'}`}
                        >
                            {faq.is_published ? 'Published' : 'Draft'}
                        </span>
                    </dd>

                    <dt className="col-sm-3 text-muted fw-normal">Created</dt>
                    <dd className="col-sm-9">{new Date(faq.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-3 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-9 mb-0">
                        {new Date(faq.updated_at).toLocaleString()}
                    </dd>
                </dl>
            )}

            <div className="d-flex justify-content-end mt-4">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                    Close
                </button>
            </div>
        </Modal>
    );
}
