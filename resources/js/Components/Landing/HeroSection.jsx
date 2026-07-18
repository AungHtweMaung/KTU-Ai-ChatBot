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

const conversation = [
    { from: 'stu', label: 'Student', text: 'Who teaches Database Systems?' },
    { from: 'ai', label: 'KTU Assistant', text: 'Dr. Aung Kyaw teaches Database Systems.' },
    {
        from: 'stu',
        label: 'Student',
        text: 'How much is First Year Computer Science registration?',
    },
    { from: 'ai', label: 'KTU Assistant', text: 'The registration fee is 150,000 MMK.' },
    { from: 'stu', label: 'Student', text: 'When is registration?' },
    { from: 'ai', label: 'KTU Assistant', text: 'Registration starts on August 10.' },
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

                    {/* Right: chat mockup */}
                    <div className="col-lg-6">
                        <div className="chat-mock-wrap">
                            <div className="float-badge float-badge-1">
                                <i className="bi bi-lightning-charge-fill text-warning"></i>
                                Instant
                            </div>
                            <div className="float-badge float-badge-2">
                                <i className="bi bi-cpu-fill text-primary"></i>
                                AI Powered
                            </div>
                            <div className="float-badge float-badge-3">
                                <i className="bi bi-check-circle-fill text-success"></i>
                                Accurate
                            </div>

                            <div className="chat-mock">
                                <div className="chat-mock-header">
                                    <span className="chat-mock-avatar">
                                        <i className="bi bi-robot"></i>
                                    </span>
                                    <div className="flex-grow-1">
                                        <div className="fw-bold" style={{ lineHeight: 1.1 }}>
                                            KTU Assistant
                                        </div>
                                        <small className="text-muted-soft">Online now</small>
                                    </div>
                                    <span className="chat-mock-dot"></span>
                                </div>

                                <div className="chat-mock-body">
                                    {conversation.map((msg, i) => (
                                        <div
                                            key={i}
                                            className={`chat-row ${msg.from === 'stu' ? 'user' : 'ai'}`}
                                            style={{ animationDelay: `${i * 0.35 + 0.2}s` }}
                                        >
                                            <span
                                                className={`chat-avatar ${msg.from === 'stu' ? 'stu' : 'ai'}`}
                                            >
                                                <i
                                                    className={`bi ${msg.from === 'stu' ? 'bi-person-fill' : 'bi-robot'}`}
                                                ></i>
                                            </span>
                                            <div>
                                                <div className="chat-label">{msg.label}</div>
                                                <div className="chat-bubble">{msg.text}</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div className="chat-input-bar">
                                    <div className="chat-input-fake">Ask anything about KTU…</div>
                                    <button className="chat-send" type="button" aria-label="Send">
                                        <i className="bi bi-send-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
