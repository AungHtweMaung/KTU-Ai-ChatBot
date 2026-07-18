export default function Footer() {
    return (
        <footer className="landing-footer">
            <div className="container">
                <div className="row g-4 align-items-center">
                    <div className="col-md-6">
                        <div className="d-flex align-items-center gap-2 mb-2">
                            <span className="brand-logo" style={{ width: 36, height: 36, fontSize: '1.1rem' }}>
                                <i className="bi bi-stars"></i>
                            </span>
                            <span className="brand-name fs-6">KTU Assistant</span>
                        </div>
                        <p className="text-muted-soft mb-0" style={{ fontSize: '0.9rem' }}>
                            AI-powered University Chatbot
                        </p>
                    </div>

                    <div className="col-md-6">
                        <div className="d-flex flex-wrap gap-4 justify-content-md-end">
                            <a href="#" style={{ fontSize: '0.9rem' }}>
                                Privacy Policy
                            </a>
                            <a href="#" style={{ fontSize: '0.9rem' }}>
                                Terms of Service
                            </a>
                        </div>
                    </div>
                </div>

                <hr style={{ borderColor: 'var(--ai-border)', opacity: 1 }} className="my-4" />

                <p className="text-center text-muted-soft mb-0" style={{ fontSize: '0.85rem' }}>
                    © 2026 KTU Assistant. All rights reserved.
                </p>
            </div>
        </footer>
    );
}
