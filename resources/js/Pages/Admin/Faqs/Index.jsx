import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import FaqModal from '../../../Components/Admin/FaqModal';
import FaqViewModal from '../../../Components/Admin/FaqViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

const truncate = (str, len = 60) =>
    str && str.length > len ? str.slice(0, len).trim() + '…' : str;

export default function Index({ faqs, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selected, setSelected] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.faqs.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelected(null);
        setShowFormModal(true);
    };

    const openViewModal = (faq) => {
        setSelected(faq);
        setShowViewModal(true);
    };

    const openEditModal = (faq) => {
        setSelected(faq);
        setShowFormModal(true);
    };

    const openDeleteModal = (faq) => {
        setSelected({ ...faq, name: truncate(faq.question, 60) });
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (_, index) => (faqs.from ?? 1) + index },
        { key: 'category', title: 'Category', render: (f) => f.category || '—' },
        {
            key: 'question',
            title: 'Question',
            render: (f) => <span title={f.question}>{truncate(f.question, 80)}</span>,
        },
        { key: 'sort_order', title: 'Order' },
        {
            key: 'is_published',
            title: 'Status',
            render: (f) => (
                <span
                    className={`badge ${f.is_published ? 'text-bg-success' : 'text-bg-secondary'}`}
                >
                    {f.is_published ? 'Published' : 'Draft'}
                </span>
            ),
        },
    ];

    return (
        <AdminLayout active="faq">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            FAQs
                        </h2>
                        <p className="text-muted mb-0">
                            Manage frequently asked questions for students and the chatbot.
                        </p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add FAQ
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
                            placeholder="Search by category, question, or answer..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={faqs.data}
                        actions={(faq) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(faq)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(faq)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(faq)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {faqs.links && faqs.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {faqs.links.map((link, index) => (
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

            <FaqModal
                show={showFormModal}
                faq={selected}
                onClose={() => setShowFormModal(false)}
            />

            <FaqViewModal
                show={showViewModal}
                faq={selected}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selected}
                routeName="admin.faqs.destroy"
                title="Delete FAQ"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
