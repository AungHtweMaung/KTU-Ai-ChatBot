import { useEffect, useRef, useState } from 'react';

/**
 * One row in the sidebar list. Handles hover-reveal menu (rename, delete),
 * inline-edit for renaming, and delegates to the parent for actions.
 */
export default function ConversationItem({ conversation, active, onSelect, onRename, onDelete }) {
    const [renaming, setRenaming] = useState(false);
    const [draft, setDraft] = useState(conversation.title || '');
    const [menuOpen, setMenuOpen] = useState(false);
    const inputRef = useRef(null);
    const menuRef = useRef(null);

    useEffect(() => {
        if (renaming) inputRef.current?.select();
    }, [renaming]);

    // Close the popover on outside click.
    useEffect(() => {
        if (!menuOpen) return;
        const close = (e) => {
            if (!menuRef.current?.contains(e.target)) setMenuOpen(false);
        };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, [menuOpen]);

    const commitRename = () => {
        const next = draft.trim();
        if (next && next !== conversation.title) {
            onRename(conversation.id, next);
        }
        setRenaming(false);
    };

    const handleKey = (e) => {
        if (e.key === 'Enter') commitRename();
        if (e.key === 'Escape') {
            setDraft(conversation.title || '');
            setRenaming(false);
        }
    };

    return (
        <div className={`convo-item ${active ? 'active' : ''}`} role="button" tabIndex={0}
             onClick={() => !renaming && onSelect(conversation.id)}
             onKeyDown={(e) => {
                 if (renaming) return;
                 if (e.key === 'Enter' || e.key === ' ') {
                     e.preventDefault();
                     onSelect(conversation.id);
                 }
             }}>
            <i className="bi bi-chat-left-text" aria-hidden="true"></i>

            {renaming ? (
                <input
                    ref={inputRef}
                    className="convo-title-input"
                    value={draft}
                    onChange={(e) => setDraft(e.target.value)}
                    onKeyDown={handleKey}
                    onBlur={commitRename}
                    onClick={(e) => e.stopPropagation()}
                    aria-label="Conversation title"
                />
            ) : (
                <span className="convo-title" title={conversation.title}>
                    {conversation.title || 'New chat'}
                </span>
            )}

            {!renaming && (
                <div ref={menuRef} className="dropdown">
                    <button
                        type="button"
                        className="convo-item-menu-btn"
                        aria-label="Conversation actions"
                        aria-expanded={menuOpen}
                        onClick={(e) => {
                            e.stopPropagation();
                            setMenuOpen((v) => !v);
                        }}
                    >
                        <i className="bi bi-three-dots"></i>
                    </button>
                    {menuOpen && (
                        <ul className="dropdown-menu chat-dropdown-menu show" style={{ position: 'absolute', right: 0, top: '100%', marginTop: 4 }}>
                            <li>
                                <button
                                    type="button"
                                    className="dropdown-item"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        setMenuOpen(false);
                                        setDraft(conversation.title || '');
                                        setRenaming(true);
                                    }}
                                >
                                    <i className="bi bi-pencil"></i>
                                    Rename
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    className="dropdown-item text-danger"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        setMenuOpen(false);
                                        if (confirm('Delete this conversation? This cannot be undone.')) {
                                            onDelete(conversation.id);
                                        }
                                    }}
                                >
                                    <i className="bi bi-trash"></i>
                                    Delete
                                </button>
                            </li>
                        </ul>
                    )}
                </div>
            )}
        </div>
    );
}
