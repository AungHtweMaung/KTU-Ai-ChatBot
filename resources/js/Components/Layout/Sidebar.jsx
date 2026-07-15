import React from 'react';
import { Link } from '@inertiajs/react';

const items = [
  { key: 'dashboard', label: 'Dashboard', icon: 'bi-grid' , href: '/admin/dashboard'},
  { key: 'teachers', label: 'Teacher Management', icon: 'bi-person-badge', children: [
    { key: 'teachers.list', label: 'Teachers List', href: '/admin/teachers' },
    { key: 'teachers.add', label: 'Add Teacher', href: '/admin/teachers/create' },
  ]},
  { key: 'departments', label: 'Department Management', icon: 'bi-building', href: '/admin/departments' },
  { key: 'majors', label: 'Major Management', icon: 'bi-book', href: '/admin/majors' },
  { key: 'subjects', label: 'Subject Management', icon: 'bi-journal-text', href: '/admin/subjects' },
  { key: 'timetable', label: 'Timetable Management', icon: 'bi-calendar-event', href: '/admin/timetable' },
  { key: 'announcements', label: 'Announcement Management', icon: 'bi-megaphone', href: '/admin/announcements' },
  { key: 'events', label: 'Event Management', icon: 'bi-calendar2-day', href: '/admin/events' },
  { key: 'faq', label: 'FAQ Management', icon: 'bi-question-circle', href: '/admin/faqs' },
  { key: 'fees', label: 'Registration Fee Management', icon: 'bi-cash-stack', href: '/admin/fees' },
  { key: 'analytics', label: 'AI Chatbot Analytics', icon: 'bi-bar-chart', href: '/admin/analytics' },
  { key: 'users', label: 'Users', icon: 'bi-people', href: '/admin/users' },
  { key: 'settings', label: 'Settings', icon: 'bi-gear', href: '/admin/settings' },
];

export default function Sidebar({ active = 'dashboard', collapsed, isMobileMenu = false }) {
  const [expandedGroups, setExpandedGroups] = React.useState({});

  const toggleGroup = (key) => {
    setExpandedGroups(prev => ({ ...prev, [key]: !prev[key] }));
  };

  const sidebarClass = isMobileMenu ? 'sidebar-mobile-menu' : `sidebar-modern h-100 ${collapsed ? 'd-none d-md-block' : ''}`;

  return (
    <aside className={sidebarClass} style={{width: 260}}>
      <div className="sidebar-header">
        <div className="logo-container">
          <div className="logo-emoji">🎓</div>
          <div className="logo-text">
            <div className="logo-title">University AI</div>
            <div className="logo-subtitle">Admin Panel</div>
          </div>
        </div>
      </div>
      <nav className="sidebar-nav">
        {items.map((it) => (
          <div key={it.key} className="nav-section">
            {it.children ? (
              <>
                <button
                  className={`nav-item-group ${expandedGroups[it.key] ? 'expanded' : ''}`}
                  onClick={() => toggleGroup(it.key)}
                >
                  <i className={`bi ${it.icon}`}></i>
                  <span className="nav-label">{it.label}</span>
                  <i className="bi bi-chevron-right nav-chevron"></i>
                </button>
                {expandedGroups[it.key] && (
                  <div className="nav-children">
                    {it.children.map((c) => (
                      <Link key={c.key} href={c.href} className={`nav-child-item ${c.key===active ? 'active' : ''}`}>
                        {c.label}
                      </Link>
                    ))}
                  </div>
                )}
              </>
            ) : (
              <Link href={it.href} className={`nav-item ${it.key===active ? 'active' : ''}`}>
                <i className={`bi ${it.icon}`}></i>
                <span className="nav-label">{it.label}</span>
              </Link>
            )}
          </div>
        ))}
      </nav>
    </aside>
  );
}
