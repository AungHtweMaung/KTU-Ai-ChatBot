import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Admin/DataTable';
import SearchInput from '../../../Components/Admin/SearchInput';
import FeeModal from '../../../Components/Admin/FeeModal';
import FeeViewModal from '../../../Components/Admin/FeeViewModal';
import DeleteConfirmModal from '../../../Components/Admin/DeleteConfirmModal';

const formatMmk = (v) =>
    new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(Number(v || 0));

export default function Index({ fees, majors, filters }) {
    const [showFormModal, setShowFormModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showViewModal, setShowViewModal] = useState(false);
    const [selected, setSelected] = useState(null);

    const handleSearch = (search) => {
        router.get(
            route('admin.fees.index'),
            search ? { search } : {},
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setSelected(null);
        setShowFormModal(true);
    };

    const openViewModal = (fee) => {
        setSelected(fee);
        setShowViewModal(true);
    };

    const openEditModal = (fee) => {
        setSelected(fee);
        setShowFormModal(true);
    };

    const openDeleteModal = (fee) => {
        const label = fee.major?.name
            ? `${fee.name} (${fee.major.name})`
            : fee.name;
        setSelected({ ...fee, name: label });
        setShowDeleteModal(true);
    };

    const columns = [
        { key: 'no', title: 'No', render: (_, index) => (fees.from ?? 1) + index },
        { key: 'name', title: 'Fee Type' },
        {
            key: 'major',
            title: 'Major',
            render: (f) => f.major?.name ?? <span className="text-muted">All majors</span>,
        },
        {
            key: 'amount',
            title: 'Amount (MMK)',
            render: (f) => (
                <span className="fw-medium">{formatMmk(f.amount)}</span>
            ),
        },
        {
            key: 'description',
            title: 'Description',
            render: (f) =>
                f.description ? (
                    <span title={f.description}>
                        {f.description.length > 60
                            ? f.description.slice(0, 60).trim() + '…'
                            : f.description}
                    </span>
                ) : (
                    '—'
                ),
        },
    ];

    return (
        <AdminLayout active="fees">
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2
                            className="fw-700 mb-1"
                            style={{ fontSize: '1.75rem', letterSpacing: '-0.5px' }}
                        >
                            Registration Fees
                        </h2>
                        <p className="text-muted mb-0">
                            Manage tuition and other registration fees by major.
                        </p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-primary d-flex align-items-center gap-2"
                        onClick={openCreateModal}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Fee
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
                            placeholder="Search by fee type or major..."
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={fees.data}
                        actions={(fee) => (
                            <div className="btn-group btn-group-sm">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="View"
                                    onClick={() => openViewModal(fee)}
                                >
                                    <i className="bi bi-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title="Edit"
                                    onClick={() => openEditModal(fee)}
                                >
                                    <i className="bi bi-pencil"></i>
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    title="Delete"
                                    onClick={() => openDeleteModal(fee)}
                                >
                                    <i className="bi bi-trash"></i>
                                </button>
                            </div>
                        )}
                    />

                    {fees.links && fees.links.length > 3 && (
                        <div
                            className="card-footer d-flex justify-content-end"
                            style={{ borderColor: 'var(--bs-border-color)' }}
                        >
                            <nav aria-label="Pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    {fees.links.map((link, index) => (
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

            <FeeModal
                show={showFormModal}
                fee={selected}
                majors={majors}
                onClose={() => setShowFormModal(false)}
            />

            <FeeViewModal
                show={showViewModal}
                fee={selected}
                onClose={() => setShowViewModal(false)}
            />

            <DeleteConfirmModal
                show={showDeleteModal}
                item={selected}
                routeName="admin.fees.destroy"
                title="Delete Registration Fee"
                onClose={() => setShowDeleteModal(false)}
            />
        </AdminLayout>
    );
}
