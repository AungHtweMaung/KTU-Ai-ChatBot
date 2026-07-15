import { useEffect, useRef } from 'react';
import { Modal as BootstrapModal } from 'bootstrap/dist/js/bootstrap.bundle';

export default function Modal({ show, title, children, onClose, size = '' }) {
    const modalRef = useRef(null);
    const instanceRef = useRef(null);

    useEffect(() => {
        const el = modalRef.current;
        instanceRef.current = BootstrapModal.getOrCreateInstance(el, {
            backdrop: 'static',
        });

        const handleHidden = () => onClose?.();
        el.addEventListener('hidden.bs.modal', handleHidden);

        return () => {
            el.removeEventListener('hidden.bs.modal', handleHidden);
            instanceRef.current?.dispose();
        };
    }, []);

    useEffect(() => {
        if (show) {
            instanceRef.current?.show();
        } else {
            instanceRef.current?.hide();
        }
    }, [show]);

    return (
        <div className="modal fade" ref={modalRef} tabIndex="-1" aria-hidden="true">
            <div className={`modal-dialog modal-dialog-centered ${size}`}>
                <div
                    className="modal-content rounded-3"
                    style={{
                        backgroundColor: 'var(--bs-body-bg)',
                        color: 'var(--bs-body-color)',
                        border: '1px solid var(--bs-border-color)',
                    }}
                >
                    <div
                        className="modal-header"
                        style={{ borderColor: 'var(--bs-border-color)' }}
                    >
                        <h5 className="modal-title">{title}</h5>
                        <button
                            type="button"
                            className="btn-close"
                            onClick={onClose}
                            aria-label="Close"
                        ></button>
                    </div>
                    <div className="modal-body">{children}</div>
                </div>
            </div>
        </div>
    );
}
