import React from 'react';

export default function DataTable({ columns = [], data = [], children }) {
  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead>
          <tr>
            {columns.map((c) => (<th key={c.key}>{c.title}</th>))}
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {data.map((row, idx) => (
            <tr key={row.id || idx}>
              {columns.map((c) => (<td key={c.key}>{c.render ? c.render(row) : row[c.key]}</td>))}
              <td>{children ? children(row) : null}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
