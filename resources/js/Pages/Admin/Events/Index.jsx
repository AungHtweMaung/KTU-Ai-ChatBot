import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import EventModal from '../../../Components/Admin/EventModal';
import EventViewModal from '../../../Components/Admin/EventViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

const fmtDate = (v) =>
    v
        ? new Date(v).toLocaleString(undefined, {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '—';

export default function Index({ events, majors, majorYears, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selected, setSelected] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.events.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelected(null);
        setShowFormModal(true);
    };

    const openViewModal = (ev) => {
        setSelected(ev);
        setShowViewModal(true);
    };

    const openEditModal = (ev) => {
        setSelected(ev);
        setShowFormModal(true);
    };

    const openDeleteModal = (ev) => {
        setSelected({ ...ev, name: ev.title });
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (_, index) => (events.from ?? 1) + index },
        {
            key: 'cover',
            title: 'Cover',
            render: (e) => (
                <div
                    className="rounded overflow-hidden d-flex align-items-center justify-content-center"
                    style={{
                        width: 60,
                        height: 45,
                        backgroundColor: 'var(--bs-tertiary-bg, #f8f9fa)',
                        border: '1px solid var(--bs-border-color)',
                    }}
                >
                    {e.cover_image_url ? (
                        <img
                            src={e.cover_image_url}
                            alt={e.title}
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                        />
                    ) : (
                        <i className="bi bi-image text-muted"></i>
                    )}
                </div>
            ),
        },
        { key: 'title', title: 'Title' },
        { key: 'type', title: 'Type', render: (e) => e.type || '—' },
        { key: 'starts_at', title: 'Starts', render: (e) => fmtDate(e.starts_at) },
        {
            key: 'target',
            title: 'Target',
            render: (e) => {
                const parts = [];
                if (e.major?.name) parts.push(e.major.name);
                if (e.major_year?.name) parts.push(e.major_year.name);
                return parts.length ? parts.join(' · ') : 'All';
            },
        },
        {
            key: 'is_published',
            title: 'Status',
            render: (e) => (
                <span
                    className={`badge ${e.is_published ? 'text-bg-success' : 'text-bg-secondary'}`}
                >
                    {e.is_published ? 'Published' : 'Draft'}
                </span>
            ),
        },
    ];

    return (
        <AdminLayout active="events">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            Events
                        </h2>
                        <p className="text-muted mb-0">
                            Manage university events, seminars, and holidays.
                        </p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Event
                    </button>
                </div>

                <div
                    className="card rounded-3"
                    style={{
                        backgroundColor: 'var(--bs-body-bg)',
                        color: 'var(--bs-body-color)',
                        borderColor: 'var(--bs-border-color)',
                    }}
                >
                    <div
                        className="card-header d-flex justify-content-between align-items-center flex-wrap gap-3"
                        style={{ borderColor: 'var(--bs-border-color)' }}
                    >
                        <SearchInput
                            value={filters?.search ?? ''}
                            onChange={handleSearch}
                            placeholder="Search by title, type, location, or major..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={events.data}
                        actions={(ev) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(ev)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(ev)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(ev)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {events.links && events.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {events.links.map((link, index) => (
                                        <li
                                            key={index}
                                            className={`page-item ${link.active ? 'active' : ''} ${
                                                !link.url ? 'disabled' : ''
                                            }`}
                                        >
                                            <button
                                                type="button"
                                                className="page-link"
                                                disabled={!link.url}
                                                onClick={() =>
                                                    link.url &&
                                                    router.get(
                                                        link.url,
                                                        {},
                                                        { preserveState: true, preserveScroll: true },
                                                    )
                                                }
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        </li>
                                    ))}
                                </ul>
                            </nav>
                        </div>
                    )}
                </div>
            </div>

            <EventModal
                show={showFormModal}
                event={selected}
                majors={majors}
                majorYears={majorYears}
                onClose={() => setShowFormModal(false)}
            />

            <EventViewModal
                show={showViewModal}
                event={selected}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selected}
                routeName="admin.events.destroy"
                title="Delete Event"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
