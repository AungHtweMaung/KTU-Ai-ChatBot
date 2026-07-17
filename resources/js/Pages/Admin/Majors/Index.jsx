import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import MajorModal from '../../../Components/Admin/MajorModal';
import MajorViewModal from '../../../Components/Admin/MajorViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

export default function Index({ majors, departments, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selectedMajor, setSelectedMajor] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.majors.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelectedMajor(null);
        setShowFormModal(true);
    };

    const openViewModal = (major) => {
        setSelectedMajor(major);
        setShowViewModal(true);
    };

    const openEditModal = (major) => {
        setSelectedMajor(major);
        setShowFormModal(true);
    };

    const openDeleteModal = (major) => {
        setSelectedMajor(major);
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (m, index) => (majors.from ?? 1) + index },
        { key: 'name', title: 'Name' },
        {
            key: 'department',
            title: 'Department',
            render: (m) => m.department?.name ?? '—',
        },
        { key: 'description', title: 'Description', render: (m) => m.description || '—' },
        {
            key: 'created_at',
            title: 'Created Date',
            render: (m) => new Date(m.created_at).toLocaleDateString(),
        },
    ];

    return (
        <AdminLayout active="majors">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            Majors
                        </h2>
                        <p className="text-muted mb-0">Manage academic majors.</p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Major
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
                            placeholder="Search by major or department..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={majors.data}
                        actions={(major) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(major)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(major)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(major)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {majors.links && majors.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {majors.links.map((link, index) => (
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

            <MajorModal
                show={showFormModal}
                major={selectedMajor}
                departments={departments}
                onClose={() => setShowFormModal(false)}
            />

            <MajorViewModal
                show={showViewModal}
                major={selectedMajor}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selectedMajor}
                routeName="admin.majors.destroy"
                title="Delete Major"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
