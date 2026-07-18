import { useMemo } from 'react';
import ConversationItem from './ConversationItem';

/**
 * Left-hand list of past conversation threads.
 *
 * Groups conversations by relative age (Today / Yesterday / Previous 7 days
 * / Older) similar to ChatGPT & Claude. Desktop = always visible next to
 * the chat; mobile = off-canvas drawer toggled from the navbar hamburger.
 */
export default function ConversationSidebar({
    conversations,
    activeId,
    isOpen,
    isMobile,
    loading,
    onSelect,
    onNewChat,
    onRename,
    onDelete,
    onCloseMobile,
}) {
    const groups = useMemo(() => groupByDate(conversations), [conversations]);

    // Desktop: `is-open` false means the user collapsed it → slide-left animation.
    // Mobile: `is-open` true means the drawer is out.
    const cls = [
        'chat-sidebar',
        isMobile ? (isOpen ? 'is-open' : '') : (isOpen ? '' : 'is-closed'),
    ]
        .filter(Boolean)
        .join(' ');

    // Mobile transform is applied inline (avoids CSS cascade quirks with
    // identically-scoped media-query rules). Desktop transform lives in CSS.
    const inlineStyle = isMobile
        ? { transform: isOpen ? 'translateX(0)' : 'translateX(calc(-1 * var(--c-sidebar-w)))' }
        : undefined;

    return (
        <>
            <aside className={cls} style={inlineStyle} aria-label="Chat history">
                <div className="chat-sidebar-header">
                    <button
                        type="button"
                        className="chat-btn"
                        onClick={() => {
                            onNewChat();
                            if (isMobile) onCloseMobile();
                        }}
                    >
                        <i className="bi bi-plus-lg" aria-hidden="true"></i>
                        New Chat
                    </button>
                </div>

                <div className="chat-sidebar-list">
                    {loading && conversations.length === 0 ? (
                        <div className="sidebar-empty">Loading conversations…</div>
                    ) : conversations.length === 0 ? (
                        <div className="sidebar-empty">
                            No conversations yet.
                            <br />
                            Start chatting to save your first thread.
                        </div>
                    ) : (
                        groups.map((g) => (
                            <div key={g.label}>
                                <div className="sidebar-group-label">{g.label}</div>
                                {g.items.map((c) => (
                                    <ConversationItem
                                        key={c.id}
                                        conversation={c}
                                        active={c.id === activeId}
                                        onSelect={(id) => {
                                            onSelect(id);
                                            if (isMobile) onCloseMobile();
                                        }}
                                        onRename={onRename}
                                        onDelete={onDelete}
                                    />
                                ))}
                            </div>
                        ))
                    )}
                </div>
            </aside>

            {isMobile && isOpen && (
                <div
                    className="sidebar-backdrop"
                    onClick={onCloseMobile}
                    aria-hidden="true"
                ></div>
            )}
        </>
    );
}

/* ---------- helpers ---------- */

const DAY_MS = 24 * 60 * 60 * 1000;

function groupByDate(conversations) {
    const now = new Date();
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
    const buckets = {
        Today: [],
        Yesterday: [],
        'Previous 7 days': [],
        Older: [],
    };

    for (const c of conversations) {
        const t = new Date(c.updated_at).getTime();
        if (t >= startOfToday) buckets['Today'].push(c);
        else if (t >= startOfToday - DAY_MS) buckets['Yesterday'].push(c);
        else if (t >= startOfToday - 7 * DAY_MS) buckets['Previous 7 days'].push(c);
        else buckets['Older'].push(c);
    }

    return Object.entries(buckets)
        .filter(([, items]) => items.length > 0)
        .map(([label, items]) => ({ label, items }));
}
