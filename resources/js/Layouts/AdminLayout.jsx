import React, { useState, useEffect } from 'react';
import Sidebar from '../Components/Layout/Sidebar';
import Header from '../Components/Layout/Header';
import MobileMenu from '../Components/Layout/MobileMenu';

export default function AdminLayout({ children, active = 'dashboard' }) {
  const [collapsed, setCollapsed] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [isMobile, setIsMobile] = useState(false);

  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth < 768);
    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  useEffect(() => {
    if (!isMobile) setMobileOpen(false);
  }, [isMobile]);

  return (
    <div className="d-flex vh-100">
      {!isMobile && <Sidebar active={active} collapsed={collapsed} />}
      <div className="flex-grow-1 d-flex flex-column admin-main-content">
        <Header onToggle={() => setCollapsed(!collapsed)} onMobileToggle={() => setMobileOpen(true)} />
        <main className="p-3 p-md-4 overflow-auto flex-grow-1" style={{background: 'var(--bs-body-bg)'}}>
          {children}
        </main>
      </div>
      {isMobile && <MobileMenu show={mobileOpen} onClose={() => setMobileOpen(false)} active={active} />}
    </div>
  );
}
