import { Link } from '@inertiajs/react';
import KtuLogo from '../KtuLogo';
import useTheme from './useTheme';

export default function Navbar() {
    const { theme, toggle } = useTheme();

    return (
        <nav className="landing-navbar">
            <div className="container py-2">
                <div className="d-flex align-items-center justify-content-between">
                    <Link href="/" className="d-flex align-items-center gap-2">
                        <KtuLogo size={44} />
                        <span className="brand-name fs-5">KTU Assistant</span>
                    </Link>

                    <div className="d-flex align-items-center gap-2 gap-sm-3">
                        <button
                            type="button"
                            className="theme-toggle-btn"
                            onClick={toggle}
                            aria-label="Toggle dark mode"
                            title={theme === 'dark' ? 'Light mode' : 'Dark mode'}
                        >
                            <i className={`bi ${theme === 'dark' ? 'bi-sun' : 'bi-moon-stars'}`}></i>
                        </button>

                        <Link href={route('login')} className="btn-ai-ghost">
                            <i className="bi bi-box-arrow-in-right"></i>
                            <span className="d-none d-sm-inline">Login</span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>
    );
}
