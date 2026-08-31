import Modal from './Modal';

const SEMESTER_LABELS = {
    1: 'First Semester',
    2: 'Second Semester',
};

export default function TimetableViewModal({ show, timetable, onClose }) {
    return (
        <Modal show={show} onClose={onClose} title="Timetable Details" size="modal-lg">
            {timetable && (
                <div className="row g-4">
                    <div className="col-12">
                        <dl className="row mb-0">
                            <dt className="col-sm-3 text-muted fw-normal">Class</dt>
                            <dd className="col-sm-9">
                                {timetable.major_year
                                    ? `${timetable.major_year.name} — ${
                                          timetable.major_year.major?.name ?? '—'
                                      }`
                                    : '—'}
                            </dd>

                            <dt className="col-sm-3 text-muted fw-normal">Semester</dt>
                            <dd className="col-sm-9">
                                {SEMESTER_LABELS[timetable.semester] ?? '—'}
                            </dd>

                            <dt className="col-sm-3 text-muted fw-normal">Updated</dt>
                            <dd className="col-sm-9 mb-0">
                                {new Date(timetable.updated_at).toLocaleString()}
                            </dd>
                        </dl>
                    </div>

                    <div className="col-12">
                        {timetable.image_url ? (
                            <a
                                href={timetable.image_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="d-block rounded overflow-hidden"
                                style={{ border: '1px solid var(--bs-border-color)' }}
                                title="Open full size in a new tab"
                            >
                                <img
                                    src={timetable.image_url}
                                    alt="Timetable"
                                    style={{ width: '100%', height: 'auto', display: 'block' }}
                                />
                            </a>
                        ) : (
                            <div className="text-muted text-center py-5">
                                <i
                                    className="bi bi-calendar-event d-block mb-2"
                                    style={{ fontSize: '2rem' }}
                                ></i>
                                No image uploaded.
                            </div>
                        )}
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
