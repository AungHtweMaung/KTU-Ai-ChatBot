import Modal from './Modal';

const formatMmk = (v) =>
    new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(Number(v || 0));

export default function FeeViewModal({ show, fee, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Registration Fee Details">
            {fee && (
                <dl className="row mb-0">
                    <dt className="col-sm-4 text-muted fw-normal">Major</dt>
                    <dd className="col-sm-8">{fee.major?.name ?? 'All majors'}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Fee Type</dt>
                    <dd className="col-sm-8">{fee.name}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Amount</dt>
                    <dd className="col-sm-8">
                        <strong>{formatMmk(fee.amount)}</strong> MMK
                    </dd>

                    <dt className="col-sm-4 text-muted fw-normal">Description</dt>
                    <dd className="col-sm-8" style={{ whiteSpace: 'pre-wrap' }}>
                        {fee.description || '—'}
                    </dd>

                    <dt className="col-sm-4 text-muted fw-normal">Created</dt>
                    <dd className="col-sm-8">{new Date(fee.created_at).toLocaleString()}</dd>

                    <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                    <dd className="col-sm-8 mb-0">
                        {new Date(fee.updated_at).toLocaleString()}
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
