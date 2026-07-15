import React from 'react';
import { Head, Link } from '@inertiajs/react';

const messages = {
  403: {
    title: 'Access denied',
    description: 'You do not have permission to open this page.',
  },
  404: {
    title: 'Page not found',
    description: 'The page you are looking for does not exist or has been moved.',
  },
  500: {
    title: 'Server error',
    description: 'Something went wrong while loading this page.',
  },
  503: {
    title: 'Service unavailable',
    description: 'The application is temporarily unavailable.',
  },
};

export default function Error({ status = 500 }) {
  const error = messages[status] || messages[500];

  return (
    <>
      <Head title={`${status} ${error.title}`} />
      <main
        className="min-vh-100 d-flex align-items-center justify-content-center p-4"
        style={{ background: 'var(--bs-body-bg)', color: 'var(--bs-body-color)' }}
      >
        <section className="error-panel text-center">
          <div className="error-status">{status}</div>
          <h1 className="error-title">{error.title}</h1>
          <p className="error-description">{error.description}</p>
          <Link href="/admin/dashboard" className="btn btn-primary">
            Back to dashboard
          </Link>
        </section>
      </main>
    </>
  );
}
