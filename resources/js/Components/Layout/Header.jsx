import React from 'react';
import ThemeToggle from './ThemeToggle';
import MobileMenu from './MobileMenu';
import { Link } from '@inertiajs/react';

export default function Header({ onToggle, onMobileToggle }) {
  return (
    <header className="header-modern">
      <div className="header-left">
        <button className="header-toggle d-md-none" onClick={onMobileToggle} aria-label="Toggle mobile menu" title="Menu">
          <i className="bi bi-list"></i>
        </button>
        <button className="header-toggle d-none d-md-flex" onClick={onToggle} aria-label="Toggle sidebar" title="Toggle sidebar">
          <i className="bi bi-list"></i>
        </button>
      </div>
      <div className="header-right">
        <div className="search-container">
          <i className="bi bi-search"></i>
          <input type="text" className="search-input" placeholder="Search..." />
        </div>

        <div className="header-icons">
          <div className="dropdown">
            <button className="icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
              <i className="bi bi-bell"></i>
              <span className="badge">0</span>
            </button>
            <ul className="dropdown-menu dropdown-menu-end header-dropdown">
              <li className="dropdown-header">Notifications</li>
              <li className="dropdown-item-text">No new notifications</li>
            </ul>
          </div>

          <div className="dropdown">
            <button className="user-btn" data-bs-toggle="dropdown" aria-expanded="false">
              <div className="user-avatar">A</div>
              <span className="d-none d-md-inline">Admin</span>
            </button>
            <ul className="dropdown-menu dropdown-menu-end header-dropdown">
              <li className="dropdown-header">Account</li>
              <li><Link className="dropdown-item" href="/profile">Profile</Link></li>
              <li><hr className="dropdown-divider" /></li>
              <li><Link className="dropdown-item" href="/logout">Logout</Link></li>
            </ul>
          </div>

          <ThemeToggle />
        </div>
      </div>
    </header>
  );
}
