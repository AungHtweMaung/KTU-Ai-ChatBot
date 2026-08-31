import { Link } from '@inertiajs/react';

const topics = [
    { icon: 'bi-cash-coin', label: 'Registration Fees' },
    { icon: 'bi-building', label: 'Departments' },
    { icon: 'bi-mortarboard', label: 'Majors' },
    { icon: 'bi-journal-text', label: 'Subjects' },
    { icon: 'bi-person-badge', label: 'Teachers' },
    { icon: 'bi-calendar2-event', label: 'Events' },
    { icon: 'bi-megaphone', label: 'Announcements' },
    { icon: 'bi-question-circle', label: 'FAQs' },
];

export default function HeroSection() {
    return (
        <section className="hero-section">
            <span className="hero-glow hero-glow-1"></span>
            <span className="hero-glow hero-glow-2"></span>

            <div className="container position-relative">
                <div className="row align-items-center g-5">
                    {/* Left: copy */}
                    <div className="col-lg-6">
                        <span className="hero-badge mb-3">
                            <i className="bi bi-stars"></i>
                            AI-Powered University Assistant
                        </span>

                        <h1 className="display-hero mb-3">
                            Your Intelligent <span className="gradient-text">University Assistant</span>
                        </h1>

                        <p className="fs-5 text-muted-soft mb-4" style={{ maxWidth: '34rem' }}>
                            Ask questions naturally and get instant answers about your university —
                            no forms, no waiting.
                        </p>

                        <ul className="hero-topics mb-4">
                            {topics.map((t) => (
                                <li key={t.label}>
                                    <i className={`bi ${t.icon}`}></i>
                                    {t.label}
                                </li>
                            ))}
                        </ul>

                        <div className="d-flex flex-wrap gap-3">
                            <Link href={route('chat')} className="btn-ai-primary">
                                <i className="bi bi-chat-dots-fill"></i>
                                Start Chatting
                            </Link>
                            <Link href={route('login')} className="btn-ai-ghost">
                                <i className="bi bi-box-arrow-in-right"></i>
                                Login
                            </Link>
                        </div>
                    </div>

                    {/* Right: hero image */}
                    <div className="col-lg-6 text-center">
                        <img
                            src="/images/hero.png"
                            alt="KTU Assistant"
                            className="hero-image img-fluid"
                            style={{
                                maxWidth: '100%',
                                height: 'auto',
                                borderRadius: '1rem',
                            }}
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}
