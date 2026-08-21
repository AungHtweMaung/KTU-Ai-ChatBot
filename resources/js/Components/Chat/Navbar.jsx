import { Link, useForm } from '@inertiajs/react';

/**
 * Fixed top navigation for the chat page.
 *  Left:  university logo + "KTU Assistant"
 *  Right: New Chat button + user avatar dropdown (Profile / Logout)
 */
export default function Navbar({ onNewChat, onToggleSidebar }) {
    const { post } = useForm();

    const logout = () => post(route('logout'));

    return (
        <header className="chat-navbar">
            <div className="container-fluid px-3 d-flex align-items-center justify-content-between">
                {/* Left: sidebar toggle + brand */}
                <div className="d-flex align-items-center gap-2">
                    <button
                        type="button"
                        className="chat-icon-btn"
                        onClick={onToggleSidebar}
                        aria-label="Toggle conversation history"
                        title="Conversations"
                    >
                        <i className="bi bi-layout-sidebar" aria-hidden="true"></i>
                    </button>

                    <Link href="/" className="d-flex align-items-center gap-2">
                        <span className="brand-logo" aria-hidden="true">
                            <i className="bi bi-stars"></i>
                        </span>
                        <span className="brand-name d-none d-sm-inline">KTU Assistant</span>
                    </Link>
                </div>

                {/* Right: actions */}
                <div className="d-flex align-items-center gap-2">
                    <button type="button" className="chat-btn" onClick={onNewChat}>
                        <i className="bi bi-plus-lg" aria-hidden="true"></i>
                        <span className="d-none d-sm-inline">New Chat</span>
                    </button>

                    <div className="dropdown">
                        <button
                            type="button"
                            className="chat-avatar-btn"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="Account menu"
                        >
                            <i className="bi bi-person-fill"></i>
                        </button>
                        <ul className="dropdown-menu dropdown-menu-end chat-dropdown-menu mt-2">
                            <li>
                                <Link className="dropdown-item" href="/profile">
                                    <i className="bi bi-person" aria-hidden="true"></i>
                                    Profile
                                </Link>
                            </li>
                            <li>
                                <hr className="dropdown-divider" />
                            </li>
                            <li>
                                <button type="button" className="dropdown-item" onClick={logout}>
                                    <i className="bi bi-box-arrow-right" aria-hidden="true"></i>
                                    Logout
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
    );
}
