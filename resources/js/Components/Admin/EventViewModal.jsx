import Modal from './Modal';

const fmtDate = (v) => (v ? new Date(v).toLocaleString() : '—');

export default function EventViewModal({ show, event, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Event Details" size="modal-lg">
            {event && (
                <div className="row g-4">
                    <div className="col-md-5">
                        <div
                            className="rounded d-flex align-items-center justify-content-center overflow-hidden"
                            style={{
                                aspectRatio: '4 / 3',
                                backgroundColor: 'var(--bs-tertiary-bg, #f8f9fa)',
                                border: '1px solid var(--bs-border-color)',
                            }}
                        >
                            {event.cover_image_url ? (
                                <img
                                    src={event.cover_image_url}
                                    alt={event.title}
                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                />
                            ) : (
                                <i
                                    className="bi bi-image text-muted"
                                    style={{ fontSize: '3rem' }}
                                ></i>
                            )}
                        </div>
                    </div>
                    <div className="col-md-7">
                        <dl className="row mb-0">
                            <dt className="col-sm-4 text-muted fw-normal">Title</dt>
                            <dd className="col-sm-8">{event.title}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Type</dt>
                            <dd className="col-sm-8">{event.type || '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Location</dt>
                            <dd className="col-sm-8">{event.location || '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Starts</dt>
                            <dd className="col-sm-8">{fmtDate(event.starts_at)}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Ends</dt>
                            <dd className="col-sm-8">{fmtDate(event.ends_at)}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Major</dt>
                            <dd className="col-sm-8">{event.major?.name ?? 'All'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Year</dt>
                            <dd className="col-sm-8">{event.major_year?.name ?? 'All'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Status</dt>
                            <dd className="col-sm-8">
                                <span
                                    className={`badge ${event.is_published ? 'text-bg-success' : 'text-bg-secondary'}`}
                                >
                                    {event.is_published ? 'Published' : 'Draft'}
                                </span>
                            </dd>

                            <dt className="col-sm-4 text-muted fw-normal">Description</dt>
                            <dd
                                className="col-sm-8 mb-0"
                                style={{ whiteSpace: 'pre-wrap' }}
                            >
                                {event.description || '—'}
                            </dd>
                        </dl>
                    </div>
                </div>
            )}

            <div className="d-flex justify-content-end mt-4">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                    Close
                </button>
            </div>
        </Modal>
    );
}
