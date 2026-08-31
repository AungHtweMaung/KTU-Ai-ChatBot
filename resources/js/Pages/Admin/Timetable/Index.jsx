import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import TimetableModal from '../../../Components/Admin/TimetableModal';
import TimetableViewModal from '../../../Components/Admin/TimetableViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

const SEMESTER_LABELS = {
    1: 'First Semester',
    2: 'Second Semester',
};

const classLabel = (majorYear) =>
    majorYear ? `${majorYear.name} — ${majorYear.major?.name ?? '—'}` : '—';

export default function Index({ timetables, classes, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selectedTimetable, setSelectedTimetable] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.timetable.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelectedTimetable(null);
        setShowFormModal(true);
    };

    const openViewModal = (timetable) => {
        setSelectedTimetable(timetable);
        setShowViewModal(true);
    };

    const openEditModal = (timetable) => {
        setSelectedTimetable(timetable);
        setShowFormModal(true);
    };

    const openDeleteModal = (timetable) => {
        setSelectedTimetable(timetable);
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (t, index) => (timetables.from ?? 1) + index },
        {
            key: 'preview',
            title: 'Timetable',
            render: (t) => (
                <div
                    className="overflow-hidden d-flex align-items-center justify-content-center rounded"
                    style={{
                        width: 64,
                        height: 44,
                        backgroundColor: 'var(--bs-tertiary-bg, #f8f9fa)',
                        border: '1px solid var(--bs-border-color)',
                    }}
                >
                    {t.image_url ? (
                        <img
                            src={t.image_url}
                            alt="Timetable"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                        />
                    ) : (
                        <i className="bi bi-calendar-event text-muted"></i>
                    )}
                </div>
            ),
        },
        { key: 'class', title: 'Class', render: (t) => classLabel(t.major_year) },
        {
            key: 'semester',
            title: 'Semester',
            render: (t) => SEMESTER_LABELS[t.semester] ?? '—',
        },
        {
            key: 'updated',
            title: 'Updated',
            render: (t) => new Date(t.updated_at).toLocaleDateString(),
        },
    ];

    return (
        <AdminLayout active="timetable">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            Timetable
                        </h2>
                        <p className="text-muted mb-0">
                            Upload and manage class timetable images.
                        </p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Timetable
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
                            placeholder="Search by class or major..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={timetables.data}
                        actions={(timetable) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(timetable)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(timetable)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(timetable)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {timetables.links && timetables.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {timetables.links.map((link, index) => (
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

            <TimetableModal
                show={showFormModal}
                timetable={selectedTimetable}
                classes={classes}
                onClose={() => setShowFormModal(false)}
            />

            <TimetableViewModal
                show={showViewModal}
                timetable={selectedTimetable}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selectedTimetable}
                routeName="admin.timetable.destroy"
                title="Delete Timetable"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
