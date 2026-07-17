import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import TeacherModal from '../../../Components/Admin/TeacherModal';
import TeacherViewModal from '../../../Components/Admin/TeacherViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

export default function Index({ teachers, departments, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selectedTeacher, setSelectedTeacher] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.teachers.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelectedTeacher(null);
        setShowFormModal(true);
    };

    const openViewModal = (teacher) => {
        setSelectedTeacher(teacher);
        setShowViewModal(true);
    };

    const openEditModal = (teacher) => {
        setSelectedTeacher(teacher);
        setShowFormModal(true);
    };

    const openDeleteModal = (teacher) => {
        setSelectedTeacher(teacher);
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (t, index) => (teachers.from ?? 1) + index },
        {
            key: 'avatar',
            title: 'Image',
            render: (t) => (
                <div
                    className="rounded-circle overflow-hidden d-flex align-items-center justify-content-center"
                    style={{
                        width: 40,
                        height: 40,
                        backgroundColor: 'var(--bs-tertiary-bg, #f8f9fa)',
                        border: '1px solid var(--bs-border-color)',
                    }}
                >
                    {t.image_url ? (
                        <img
                            src={t.image_url}
                            alt={t.name}
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                        />
                    ) : (
                        <i className="bi bi-person text-muted"></i>
                    )}
                </div>
            ),
        },
        { key: 'name', title: 'Name' },
        {
            key: 'department',
            title: 'Department',
            render: (t) => t.department?.name ?? '—',
        },
        { key: 'email', title: 'Email' },
        { key: 'phone', title: 'Phone', render: (t) => t.phone || '—' },
        { key: 'position', title: 'Position', render: (t) => t.position || '—' },
    ];

    return (
        <AdminLayout active="teachers">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            Teachers
                        </h2>
                        <p className="text-muted mb-0">Manage all teachers and their information.</p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Teacher
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
                            placeholder="Search by name, email, position, or department..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={teachers.data}
                        actions={(teacher) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(teacher)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(teacher)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(teacher)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {teachers.links && teachers.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {teachers.links.map((link, index) => (
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

            <TeacherModal
                show={showFormModal}
                teacher={selectedTeacher}
                departments={departments}
                onClose={() => setShowFormModal(false)}
            />

            <TeacherViewModal
                show={showViewModal}
                teacher={selectedTeacher}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selectedTeacher}
                routeName="admin.teachers.destroy"
                title="Delete Teacher"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
