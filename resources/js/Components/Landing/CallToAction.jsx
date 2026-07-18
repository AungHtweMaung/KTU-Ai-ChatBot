import { Link } from '@inertiajs/react';
import Reveal from './Reveal';

export default function CallToAction() {
    return (
        <section className="py-5 my-4">
            <div className="container">
                <Reveal>
                    <div className="cta-section">
                        <div className="position-relative">
                            <h2
                                className="fw-800 mb-3"
                                style={{ fontSize: 'clamp(1.8rem, 4vw, 2.8rem)', letterSpacing: '-0.8px' }}
                            >
                                Ready to Start?
                            </h2>
                            <p className="fs-5 mb-4 mx-auto" style={{ maxWidth: '32rem', opacity: 0.92 }}>
                                Ask KTU Assistant anything about your university.
                            </p>
                            <Link href={route('chat')} className="btn-ai-white">
                                <i className="bi bi-chat-dots-fill"></i>
                                Start Chatting
                            </Link>
                        </div>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
