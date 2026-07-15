import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import DepartmentModal from '../../../Components/Admin/DepartmentModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';
import DepartmentViewModal from '../../../Components/Admin/DepartmentViewModal';

export default function Index({ departments, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selectedDepartment, setSelectedDepartment] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.departments.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelectedDepartment(null);
        setShowFormModal(true);
    };

    const openViewModal = (department) => {
        setSelectedDepartment(department);
        setShowViewModal(true);
    };

    const openEditModal = (department) => {
        setSelectedDepartment(department);
        setShowFormModal(true);
    };

    const openDeleteModal = (department) => {
        setSelectedDepartment(department);
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (d, index) => (departments.from ?? 1) + index },
        { key: 'name', title: 'Name' },
        { key: 'code', title: 'Code' },
        { key: 'description', title: 'Description', render: (d) => d.description || '—' },
        {
            key: 'created_at',
            title: 'Created Date',
            render: (d) => new Date(d.created_at).toLocaleDateString(),
        },
    ];

    return (
        <AdminLayout active="departments">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            Departments
                        </h2>
                        <p className="text-muted mb-0">Manage academic departments.</p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Department
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
                            placeholder="Search by name or code..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={departments.data}
                        actions={(department) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(department)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(department)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(department)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {departments.links && departments.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {departments.links.map((link, index) => (
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

            <DepartmentModal
                show={showFormModal}
                department={selectedDepartment}
                onClose={() => setShowFormModal(false)}
            />

            <DepartmentViewModal
                show={showViewModal}
                department={selectedDepartment}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                department={selectedDepartment}
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
