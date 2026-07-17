import Modal from './Modal';

export default function TeacherViewModal({ show, teacher, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Teacher Details" size="modal-lg">
            {teacher && (
                <div className="row g-4">
                    <div className="col-md-4 text-center">
                        <div
                            className="rounded-circle mx-auto d-flex align-items-center justify-content-center overflow-hidden"
                            style={{
                                width: 140,
                                height: 140,
                                backgroundColor: 'var(--bs-tertiary-bg, #f8f9fa)',
                                border: '1px solid var(--bs-border-color)',
                            }}
                        >
                            {teacher.image_url ? (
                                <img
                                    src={teacher.image_url}
                                    alt={teacher.name}
                                    style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                />
                            ) : (
                                <i className="bi bi-person text-muted" style={{ fontSize: '3.5rem' }}></i>
                            )}
                        </div>
                    </div>
                    <div className="col-md-8">
                        <dl className="row mb-0">
                            <dt className="col-sm-4 text-muted fw-normal">Name</dt>
                            <dd className="col-sm-8">{teacher.name}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Department</dt>
                            <dd className="col-sm-8">{teacher.department?.name ?? '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Email</dt>
                            <dd className="col-sm-8">{teacher.email}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Phone</dt>
                            <dd className="col-sm-8">{teacher.phone || '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Position</dt>
                            <dd className="col-sm-8">{teacher.position || '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Degree</dt>
                            <dd className="col-sm-8">{teacher.degree || '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Bio</dt>
                            <dd className="col-sm-8">{teacher.bio || '—'}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Created</dt>
                            <dd className="col-sm-8">{new Date(teacher.created_at).toLocaleString()}</dd>

                            <dt className="col-sm-4 text-muted fw-normal">Last Updated</dt>
                            <dd className="col-sm-8 mb-0">
                                {new Date(teacher.updated_at).toLocaleString()}
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
