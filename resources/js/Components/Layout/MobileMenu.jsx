import React from 'react';
import Sidebar from './Sidebar';

export default function MobileMenu({ show, onClose, active = 'dashboard' }) {
  if (!show) return null;

  return (
    <>
      <div
        className="position-fixed top-0 start-0 vw-100 vh-100"
        style={{background: 'rgba(0,0,0,0.5)', zIndex: 1040}}
        onClick={onClose}
      />
      <div
        className="position-fixed top-0 start-0 vh-100"
        style={{width: 260, zIndex: 1050, background: 'var(--bs-sidebar-bg)', animation: 'slideInLeft 0.3s ease', overflowY: 'auto'}}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="p-2 border-bottom d-flex justify-content-between align-items-center">
          <strong style={{color: 'var(--bs-body-color)'}}>Menu</strong>
          <button className="btn btn-sm btn-outline-secondary" onClick={onClose} style={{fontSize: '0.875rem', padding: '0.25rem 0.5rem'}}>
            <i className="bi bi-x"></i>
          </button>
        </div>
        <Sidebar active={active} isMobileMenu={true} />
      </div>
    </>
  );
}
