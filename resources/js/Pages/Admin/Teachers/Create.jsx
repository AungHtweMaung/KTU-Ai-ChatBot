import React from 'react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';

export default function Create(props) {
  const { departments = [], majors = [] } = props;
  const form = useForm({ name: '', email: '', phone: '', position: '', department_id: '', major_id: '', biography: '', avatar: null });

  function submit(e) {
    e.preventDefault();
    form.post('/admin/teachers');
  }

  return (
    <AdminLayout active="teachers">
      <div style={{maxWidth: '800px', margin: '0 auto'}}>
        <div className="mb-4">
          <h2 className="fw-700 mb-1" style={{fontSize: '1.75rem', letterSpacing: '-0.5px'}}>Add Teacher</h2>
          <p className="text-muted">Create a new teacher profile</p>
        </div>

        <div className="card">
          <div className="card-body">
            <form onSubmit={submit} encType="multipart/form-data">
              <div className="row g-3">
                <div className="col-12">
                  <label className="form-label">Profile Image</label>
                  <input className="form-control" type="file" accept="image/*" onChange={(e)=>form.setData('avatar', e.target.files[0])} />
                </div>
                <div className="col-12">
                  <label className="form-label">Full Name *</label>
                  <input className="form-control" type="text" value={form.data.name} onChange={e=>form.setData('name', e.target.value)} required />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Email *</label>
                  <input className="form-control" type="email" value={form.data.email} onChange={e=>form.setData('email', e.target.value)} required />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Phone</label>
                  <input className="form-control" type="tel" value={form.data.phone} onChange={e=>form.setData('phone', e.target.value)} />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Position *</label>
                  <input className="form-control" type="text" value={form.data.position} onChange={e=>form.setData('position', e.target.value)} required />
                </div>
                <div className="col-md-3">
                  <label className="form-label">Department</label>
                  <select className="form-select" value={form.data.department_id} onChange={e=>form.setData('department_id', e.target.value)}>
                    <option value="">Select Department</option>
                    {departments.map(d=> <option key={d.id} value={d.id}>{d.name}</option>)}
                  </select>
                </div>
                <div className="col-md-3">
                  <label className="form-label">Major</label>
                  <select className="form-select" value={form.data.major_id} onChange={e=>form.setData('major_id', e.target.value)}>
                    <option value="">Select Major</option>
                    {majors.map(m=> <option key={m.id} value={m.id}>{m.name}</option>)}
                  </select>
                </div>
                <div className="col-12">
                  <label className="form-label">Biography</label>
                  <textarea className="form-control" rows="4" value={form.data.biography} onChange={e=>form.setData('biography', e.target.value)}></textarea>
                </div>
                <div className="col-12 d-flex gap-2 pt-2">
                  <button className="btn btn-primary" type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : 'Save Teacher'}
                  </button>
                  <a href="/admin/teachers" className="btn btn-outline-secondary">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
