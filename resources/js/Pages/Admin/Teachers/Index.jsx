import React from 'react';
import AdminLayout from '../../../Layouts/AdminLayout';
import DataTable from '../../../Components/Common/DataTable';
import { Link } from '@inertiajs/react';

export default function Index(props) {
  const { teachers = [], pagination = null } = props;

  const columns = [
    { key: 'avatar', title: 'Image', render: (r) => (<img src={r.avatar || '/images/avatar-placeholder.png'} alt="avatar" width={40} height={40} className="rounded" style={{objectFit: 'cover'}} />) },
    { key: 'name', title: 'Name' },
    { key: 'position', title: 'Position' },
    { key: 'department', title: 'Department' },
    { key: 'major', title: 'Major' },
    { key: 'email', title: 'Email' },
    { key: 'phone', title: 'Phone' },
  ];

  return (
    <AdminLayout active="teachers">
      <div style={{maxWidth: '1400px', margin: '0 auto'}}>
        <div className="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 className="fw-700 mb-1" style={{fontSize: '1.75rem', letterSpacing: '-0.5px'}}>Teachers</h2>
            <p className="text-muted mb-0">Manage all teachers and their information.</p>
          </div>
          <Link href="/admin/teachers/create" className="btn btn-primary d-flex align-items-center gap-2">
            <i className="bi bi-plus"></i>
            Add Teacher
          </Link>
        </div>

        <div className="card">
          <div className="table-responsive">
            <DataTable columns={columns} data={teachers}>
              {(t) => (
                <div className="btn-group btn-group-sm">
                  <Link href={`/admin/teachers/${t.id}`} className="btn btn-outline-secondary" title="View">
                    <i className="bi bi-eye"></i>
                  </Link>
                  <Link href={`/admin/teachers/${t.id}/edit`} className="btn btn-outline-secondary" title="Edit">
                    <i className="bi bi-pencil"></i>
                  </Link>
                  <form method="post" action={`/admin/teachers/${t.id}`} onSubmit={(e)=>{ if(!confirm('Delete this teacher?')) e.preventDefault(); }} style={{display: 'inline'}}>
                    <input type="hidden" name="_method" value="DELETE" />
                    <button className="btn btn-outline-danger" title="Delete">
                      <i className="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              )}
            </DataTable>
          </div>

          {pagination && (
            <nav className="p-3 border-top" aria-label="Page navigation">
              <ul className="pagination mb-0">{/* render pagination links from server */}</ul>
            </nav>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
