import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import TeacherAssignmentModal from '../../../Components/Admin/TeacherAssignmentModal';
import TeacherAssignmentViewModal from '../../../Components/Admin/TeacherAssignmentViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

export default function Index({ assignments, teachers, curriculumSubjects, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selected, setSelected] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.teacher-assignments.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreate = () => { setSelected(null); setShowFormModal(true); };
    const openView = (row) => { setSelected(row); setShowViewModal(true); };
    const openEdit = (row) => { setSelected(row); setShowFormModal(true); };
    const openDelete = (row) => { setSelected(row); setShowDeleteModal(true); };

    const columns = [
        { key: 'no', title: 'No', render: (_, i) => (assignments.from ?? 1) + i },
        { key: 'teacher', title: 'Teacher', render: (r) => r.teacher?.name ?? '—' },
        {
            key: 'subject',
            title: 'Subject',
            render: (r) => r.curriculum_subject?.subject
                ? `${r.curriculum_subject.subject.code} — ${r.curriculum_subject.subject.name}`
                : '—',
        },
        {
            key: 'major',
            title: 'Major / Year / Sem',
            render: (r) => {
                const cs = r.curriculum_subject;
                if (!cs) return '—';
                return `${cs.major?.name ?? '?'} · Y${cs.major_year?.year_number ?? '?'} · S${cs.semester}`;
            },
        },
        { key: 'school_year', title: 'School Year' },
    ];

    const deleteItem = selected
        ? {
            id: selected.id,
            name: selected.teacher
                ? `${selected.teacher.name} — ${selected.school_year}`
                : 'this assignment',
        }
        : null;

    return (
        <AdminLayout active="teacher-assignments">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2 className="fw-700 mb-1" style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}>Teacher Assignments</h2>
                        <p className="text-muted mb-0">Assign teachers to curriculum subjects per school year.</p>
                    </div>
                    <button type="button" className="btn btn-primary d-flex align-items-center gap-2" onClick={openCreate}>
                        <i className="bi bi-plus-lg"></i>
                        Add Assignment
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
                            placeholder="Search by teacher, subject, or school year..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={assignments.data}
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

                    {assignments.links && assignments.links.length > 3 && (
                        <div className="card-footer d-flex justify-content-end" style={{ borderColor: 'var(--bs-border-color)' }}>
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {assignments.links.map((link, index) => (
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

            <TeacherAssignmentModal
                show={showFormModal}
                assignment={selected}
                teachers={teachers}
                curriculumSubjects={curriculumSubjects}
                onClose={() => setShowFormModal(false)}
            />
            <TeacherAssignmentViewModal
                show={showViewModal}
                assignment={selected}
                onClose={() => setShowViewModal(false)}
            />
            <DeleteConfirmModal
                show={showDeleteModal}
                item={deleteItem}
                routeName="admin.teacher-assignments.destroy"
                title="Delete Assignment"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
