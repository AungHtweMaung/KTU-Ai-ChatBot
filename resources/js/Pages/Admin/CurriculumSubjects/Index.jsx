import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import CurriculumSubjectModal from '../../../Components/Admin/CurriculumSubjectModal';
import CurriculumSubjectViewModal from '../../../Components/Admin/CurriculumSubjectViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

export default function Index({ curriculumSubjects, majors, majorYears, subjects, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selected, setSelected] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.curriculum-subjects.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreate = () => { setSelected(null); setShowFormModal(true); };
    const openView = (row) => { setSelected(row); setShowViewModal(true); };
    const openEdit = (row) => { setSelected(row); setShowFormModal(true); };
    const openDelete = (row) => { setSelected(row); setShowDeleteModal(true); };

    const columns = [
        { key: 'no', title: 'No', render: (_, i) => (curriculumSubjects.from ?? 1) + i },
        { key: 'major', title: 'Major', render: (r) => r.major?.name ?? '—' },
        {
            key: 'year',
            title: 'Year',
            render: (r) => r.major_year ? `Y${r.major_year.year_number}` : '—',
        },
        { key: 'semester', title: 'Sem', render: (r) => `S${r.semester}` },
        {
            key: 'subject',
            title: 'Subject',
            render: (r) => r.subject ? `${r.subject.code} — ${r.subject.name}` : '—',
        },
        { key: 'credits', title: 'Credits', render: (r) => r.subject?.credits ?? '—' },
    ];

    // Give the selected row a synthetic display name for the DeleteConfirmModal
    const deleteItem = selected
        ? {
            id: selected.id,
            name: selected.subject ? `${selected.subject.code} — ${selected.subject.name}` : 'this entry',
        }
        : null;

    return (
        <AdminLayout active="curriculum-subjects">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2 className="fw-700 mb-1" style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}>Curriculum</h2>
                        <p className="text-muted mb-0">Map subjects to majors, years, and semesters.</p>
                    </div>
                    <button type="button" className="btn btn-primary d-flex align-items-center gap-2" onClick={openCreate}>
                        <i className="bi bi-plus-lg"></i>
                        Add Curriculum Subject
                    </button>
                </div>

                <div className="card rounded-3"
                     style={{
                         backgroundColor: 'var(--bs-body-bg)',
                         color: 'var(--bs-body-color)',
                         borderColor: 'var(--bs-border-color)',
                     }}>
                    <div className="card-header d-flex justify-content-between align-items-center flex-wrap gap-3"
                         style={{ borderColor: 'var(--bs-border-color)' }}>
                        <SearchInput
                            value={filters?.search ?? ''}
                            onChange={handleSearch}
                            placeholder="Search by subject or major..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={curriculumSubjects.data}
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

                    {curriculumSubjects.links && curriculumSubjects.links.length > 3 && (
                        <div className="card-footer d-flex justify-content-end" style={{ borderColor: 'var(--bs-border-color)' }}>
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {curriculumSubjects.links.map((link, index) => (
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

            <CurriculumSubjectModal
                show={showFormModal}
                curriculumSubject={selected}
                majors={majors}
                majorYears={majorYears}
                subjects={subjects}
                onClose={() => setShowFormModal(false)}
            />
            <CurriculumSubjectViewModal
                show={showViewModal}
                curriculumSubject={selected}
                onClose={() => setShowViewModal(false)}
            />
            <DeleteConfirmModal
                show={showDeleteModal}
                item={deleteItem}
                routeName="admin.curriculum-subjects.destroy"
                title="Delete Curriculum Subject"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
