import { router } from '@inertiajs/react';
import Modal from './Modal';

export default function DeleteConfirmModal({ show, department, onClose }) {
    const handleDelete = () => {
        if (!department) return;

        router.delete(route('admin.departments.destroy', department.id), {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    return (
        <Modal show={show} onClose={onClose} title="Delete Department">
            <p className="mb-4">
                Are you sure you want to delete <strong>{department?.name}</strong>? This action
                cannot be undone.
            </p>
            <div className="d-flex justify-content-end gap-2">
                <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                    Cancel
                </button>
                <button type="button" className="btn btn-danger" onClick={handleDelete}>
                    Delete
                </button>
            </div>
        </Modal>
    );
}
