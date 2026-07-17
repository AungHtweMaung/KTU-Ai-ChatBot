import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import AcademicYearModal from '../../../Components/Admin/AcademicYearModal';
import AcademicYearViewModal from '../../../Components/Admin/AcademicYearViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

export default function Index({ academicYears, majors, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selected, setSelected] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.academic-years.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreate = () => { setSelected(null); setShowFormModal(true); };
    const openView = (row) => { setSelected(row); setShowViewModal(true); };
    const openEdit = (row) => { setSelected(row); setShowFormModal(true); };
    const openDelete = (row) => { setSelected(row); setShowDeleteModal(true); };

    const columns = [
        { key: 'no', title: 'No', render: (_, i) => (academicYears.from ?? 1) + i },
        { key: 'major', title: 'Major', render: (r) => r.major?.name ?? '—' },
        { key: 'year_number', title: 'Year' },
        { key: 'name', title: 'Name' },
        {
            key: 'created_at',
            title: 'Created Date',
            render: (r) => new Date(r.created_at).toLocaleDateString(),
        },
    ];

    return (
        <AdminLayout active="academic-years">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2 className="fw-700 mb-1" style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}>Academic Years</h2>
                        <p className="text-muted mb-0">Manage academic years for each major.</p>
                    </div>
                    <button type="button" className="btn btn-primary d-flex align-items-center gap-2" onClick={openCreate}>
                        <i className="bi bi-plus-lg"></i>
                        Add Academic Year
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
                    <div className="card-header d-flex justify-content-between align-items-center flex-wrap gap-3"
                         style={{ borderColor: 'var(--bs-border-color)' }}>
                        <SearchInput
                            value={filters?.search ?? ''}
                            onChange={handleSearch}
                            placeholder="Search by name or major..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={academicYears.data}
                        actions={(row) => (
                            <div className="btn-group btn-group-sm">
                                <button type="button" className="btn btn-outline-secondary" title="View" onClick={() => openView(row)}>
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button type="button" className="btn btn-outline-secondary" title="Edit" onClick={() => openEdit(row)}>
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button type="button" className="btn btn-outline-danger" title="Delete" onClick={() => openDelete(row)}>
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {academicYears.links && academicYears.links.length > 3 && (
                        <div className="card-footer d-flex justify-content-end" style={{ borderColor: 'var(--bs-border-color)' }}>
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {academicYears.links.map((link, index) => (
                                        <li key={index} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                                            <button
                                                type="button"
                                                className="page-link"
                                                disabled={!link.url}
                                                onClick={() => link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })}
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

            <AcademicYearModal
                show={showFormModal}
                academicYear={selected}
                majors={majors}
                onClose={() => setShowFormModal(false)}
            />

            <AcademicYearViewModal
                show={showViewModal}
                academicYear={selected}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selected}
                routeName="admin.academic-years.destroy"
                title="Delete Academic Year"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
