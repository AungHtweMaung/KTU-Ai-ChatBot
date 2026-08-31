import React from 'react';
import AdminLayout from '../../Layouts/AdminLayout';
import StatCard from '../../Components/Dashboard/StatCard';
import ChartCard from '../../Components/Dashboard/ChartCard';

export default function Dashboard(props) {
  const { statistics = {}, announcements = [], events = [], recentQuestions = [] } = props;

  const chartData = {
    labels: statistics.daily?.labels || [],
    datasets: [
      { label: 'Daily Questions', data: statistics.daily?.data || [], borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', tension: 0.4, fill: true }
    ]
  };

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        display: true,
        labels: { color: 'var(--bs-body-color)' }
      }
    },
    scales: {
      y: {
        grid: { color: 'var(--bs-border-color)' },
        ticks: { color: 'var(--bs-text-muted)' }
      },
      x: {
        grid: { display: false },
        ticks: { color: 'var(--bs-text-muted)' }
      }
    }
  };

  return (
    <AdminLayout active="dashboard">
      <div style={{maxWidth: '1400px', margin: '0 auto'}}>
        <div className="mb-4">
          <h2 className="fw-700" style={{fontSize: '1.75rem', letterSpacing: '-0.5px'}}>Dashboard</h2>
          <p className="text-muted mb-0">Welcome back! Here's what's happening with your university.</p>
        </div>

        <div className="row g-3 mb-4">
          <div className="col-6 col-md-3"><StatCard title="Total Teachers" value={statistics.teachers || 0} icon="bi-person-badge" /></div>
          <div className="col-6 col-md-3"><StatCard title="Total Subjects" value={statistics.subjects || 0} icon="bi-journal-text" /></div>
          <div className="col-6 col-md-3"><StatCard title="Departments" value={statistics.departments || 0} icon="bi-building" /></div>
          <div className="col-6 col-md-3"><StatCard title="Majors" value={statistics.majors || 0} icon="bi-mortarboard" /></div>
        </div>

        <div className="row g-3">
          <div className="col-12 col-lg-8">
            <ChartCard title="Daily Questions" data={chartData} options={chartOptions} />
            <div className="card mt-3">
              <div className="card-header">
                <h6 className="card-title mb-0">Recent Announcements</h6>
              </div>
              <div className="card-body p-0">
                <div className="list-group list-group-flush">
                  {announcements.slice(0,5).map(a=> (
                    <div key={a.id} className="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <div className="fw-600" style={{fontSize: '0.9375rem'}}>{a.title}</div>
                        <div className="small text-muted">{a.published_at}</div>
                      </div>
                    </div>
                  ))}
                  {announcements.length === 0 && (
                    <div className="p-3 text-center text-muted">No announcements</div>
                  )}
                </div>
              </div>
            </div>
          </div>
          <div className="col-12 col-lg-4">
            <div className="card">
              <div className="card-header">
                <h6 className="card-title mb-0">Recent Events</h6>
              </div>
              <div className="card-body p-0">
                <div className="list-group list-group-flush">
                  {events.slice(0,5).map(e => (
                    <div key={e.id} className="list-group-item p-3">
                      <div className="fw-600" style={{fontSize: '0.9375rem'}}>{e.title}</div>
                      <div className="small text-muted">{e.start_date}</div>
                    </div>
                  ))}
                  {events.length === 0 && (
                    <div className="p-3 text-center text-muted">No events</div>
                  )}
                </div>
              </div>
            </div>

            <div className="card mt-3">
              <div className="card-header">
                <h6 className="card-title mb-0">Recent Questions</h6>
              </div>
              <div className="card-body p-0">
                <div className="list-group list-group-flush">
                  {recentQuestions.slice(0,6).map(q => (
                    <div key={q.id} className="list-group-item p-3">
                      <div className="small">{q.question}</div>
                    </div>
                  ))}
                  {recentQuestions.length === 0 && (
                    <div className="p-3 text-center text-muted">No questions</div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
