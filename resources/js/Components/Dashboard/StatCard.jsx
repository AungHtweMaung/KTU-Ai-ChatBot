import React from 'react';

export default function StatCard({ title, value, icon, change = null }) {
  return (
    <div className="stat-card">
      <div className="stat-icon-container">
        <i className={`bi ${icon}`}></i>
      </div>
      <div className="stat-content">
        <div className="stat-title">{title}</div>
        <div className="stat-value">{value}</div>
        {change && <div className="stat-change">{change}</div>}
      </div>
    </div>
  );
}
