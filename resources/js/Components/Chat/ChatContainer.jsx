import { useCallback, useEffect, useRef, useState } from 'react';
import ChatMessage from './ChatMessage';
import TypingIndicator from './TypingIndicator';
import WelcomeScreen from './WelcomeScreen';
import MessageInput from './MessageInput';
import ScrollToBottomButton from './ScrollToBottomButton';
import ConversationSidebar from './ConversationSidebar';
import {
    initialMessages,
    nextId,
    resetConversation,
    streamAssistantReply,
    getCurrentConversationId,
    bindCurrentConversationId,
    fetchConversations,
    loadConversation,
    renameConversation,
    deleteConversation,
} from './mockApi';

const NEAR_BOTTOM_PX = 120;
const MOBILE_BREAKPOINT = 992;

/**
 * Orchestrates:
 *  - the transcript state for the active conversation,
 *  - the sidebar (list of conversations, switching, rename, delete),
 *  - streamed replies, auto-scroll, and inline error + retry.
 *
 * The container also computes whether the viewport is "mobile" (< 992px)
 * because the sidebar's behaviour differs between desktop (always visible,
 * collapsible) and mobile (off-canvas drawer with backdrop).
 */
export default function ChatContainer({ registerNewChat, registerToggleSidebar }) {
    /* ---------- transcript state ---------- */
    const [messages, setMessages] = useState(initialMessages);
    const [draft, setDraft] = useState('');
    const [isResponding, setIsResponding] = useState(false);
    const [error, setError] = useState(null);
    const [showJump, setShowJump] = useState(false);

    /* ---------- sidebar state ---------- */
    const [conversations, setConversations] = useState([]);
    const [activeId, setActiveId] = useState(() => getCurrentConversationId());
    const [loadingList, setLoadingList] = useState(true);
    const [isMobile, setIsMobile] = useState(() =>
        typeof window !== 'undefined' && window.innerWidth < MOBILE_BREAKPOINT,
    );
    const [sidebarOpen, setSidebarOpen] = useState(() =>
        typeof window !== 'undefined' && window.innerWidth >= MOBILE_BREAKPOINT,
    );

    const mainRef = useRef(null);
    const nearBottomRef = useRef(true);
    const lastUserTextRef = useRef('');
    const streamControllerRef = useRef(null);

    /* -----------------------------------------------------------------
     |  Layout: track mobile breakpoint
     |----------------------------------------------------------------- */
    useEffect(() => {
        const onResize = () => {
            const mobile = window.innerWidth < MOBILE_BREAKPOINT;
            setIsMobile((prev) => {
                if (prev !== mobile) {
                    // When flipping between mobile / desktop, restore a sensible
                    // default: closed on mobile, open on desktop.
                    setSidebarOpen(!mobile);
                }
                return mobile;
            });
        };
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    /* -----------------------------------------------------------------
     |  Sidebar: load list on mount + expose refresh
     |----------------------------------------------------------------- */
    const refreshList = useCallback(async () => {
        try {
            const list = await fetchConversations();
            setConversations(list);
        } catch {
            /* silent — the sidebar just stays empty on failure */
        } finally {
            setLoadingList(false);
        }
    }, []);

    useEffect(() => {
        refreshList();
    }, [refreshList]);

    // On initial mount, if we already have a bound conversation id from
    // localStorage, hydrate its messages so refresh doesn't lose context.
    useEffect(() => {
        const bound = getCurrentConversationId();
        if (!bound) return;
        (async () => {
            try {
                const data = await loadConversation(bound);
                setActiveId(data.id);
                setMessages(
                    (data.messages || []).map((m) => ({
                        id: `srv_${m.id}`,
                        role: m.role,
                        content: m.content,
                        createdAt: new Date(m.createdAt),
                    })),
                );
            } catch {
                // 403 / 404 — the conversation no longer belongs to us.
                resetConversation();
                setActiveId(null);
            }
        })();
    }, []);

    /* -----------------------------------------------------------------
     |  Scroll helpers
     |----------------------------------------------------------------- */
    const scrollToBottom = useCallback((behavior = 'smooth') => {
        const el = mainRef.current;
        if (el) el.scrollTo({ top: el.scrollHeight, behavior });
    }, []);

    const handleScroll = useCallback(() => {
        const el = mainRef.current;
        if (!el) return;
        const distance = el.scrollHeight - el.scrollTop - el.clientHeight;
        const near = distance < NEAR_BOTTOM_PX;
        nearBottomRef.current = near;
        setShowJump(!near && (messages.length > 0 || isResponding));
    }, [messages.length, isResponding]);

    useEffect(() => {
        if (nearBottomRef.current) scrollToBottom('smooth');
    }, [messages, isResponding, scrollToBottom]);

    /* -----------------------------------------------------------------
     |  Sending a new message
     |----------------------------------------------------------------- */
    const runAssistant = useCallback(
        (userText) => {
            streamControllerRef.current?.abort();

            setError(null);
            setIsResponding(true);

            const aiId = nextId();
            let started = false;
            const controller = new AbortController();
            streamControllerRef.current = controller;

            streamAssistantReply(
                userText,
                (partial) => {
                    if (!started) {
                        started = true;
                        setMessages((prev) => [
                            ...prev,
                            { id: aiId, role: 'ai', content: partial, createdAt: new Date() },
                        ]);
                    } else {
                        setMessages((prev) =>
                            prev.map((m) =>
                                m.id === aiId ? { ...m, content: partial } : m,
                            ),
                        );
                    }
                },
                { signal: controller.signal },
            )
                .then(() => {
                    if (streamControllerRef.current === controller) {
                        streamControllerRef.current = null;
                    }
                    setIsResponding(false);
                    // Backend may have assigned a fresh conversation id — sync it.
                    const bound = getCurrentConversationId();
                    if (bound && bound !== activeId) setActiveId(bound);
                    // Refresh the sidebar list so a new thread appears + titles update.
                    refreshList();
                })
                .catch((err) => {
                    if (err?.name === 'AbortError') return;
                    if (streamControllerRef.current === controller) {
                        streamControllerRef.current = null;
                    }
                    setIsResponding(false);
                    setError(err?.message || 'Unable to connect. Please try again.');
                });
        },
        [activeId, refreshList],
    );

    const sendMessage = useCallback(
        (text) => {
            const content = text.trim();
            if (!content || isResponding) return;

            lastUserTextRef.current = content;
            nearBottomRef.current = true;

            setMessages((prev) => [
                ...prev,
                { id: nextId(), role: 'user', content, createdAt: new Date() },
            ]);
            setDraft('');
            runAssistant(content);
        },
        [isResponding, runAssistant],
    );

    const retry = useCallback(() => {
        if (lastUserTextRef.current) runAssistant(lastUserTextRef.current);
    }, [runAssistant]);

    /* -----------------------------------------------------------------
     |  Sidebar actions
     |----------------------------------------------------------------- */
    const switchConversation = useCallback(async (id) => {
        streamControllerRef.current?.abort();
        streamControllerRef.current = null;
        setError(null);
        setIsResponding(false);
        setActiveId(id);
        bindCurrentConversationId(id);

        try {
            const data = await loadConversation(id);
            setMessages(
                (data.messages || []).map((m) => ({
                    id: `srv_${m.id}`,
                    role: m.role,
                    content: m.content,
                    createdAt: new Date(m.createdAt),
                })),
            );
            nearBottomRef.current = true;
        } catch {
            setMessages([]);
            setError('Unable to load this conversation.');
        }
    }, []);

    const handleRename = useCallback(async (id, title) => {
        // Optimistic update: reflect immediately, sync in the background.
        setConversations((prev) =>
            prev.map((c) => (c.id === id ? { ...c, title } : c)),
        );
        try {
            await renameConversation(id, title);
        } catch {
            refreshList();
        }
    }, [refreshList]);

    const handleDelete = useCallback(async (id) => {
        setConversations((prev) => prev.filter((c) => c.id !== id));
        try {
            await deleteConversation(id);
        } catch {
            refreshList();
        }
        if (id === activeId) {
            resetConversation();
            setActiveId(null);
            setMessages([]);
        }
    }, [activeId, refreshList]);

    /* -----------------------------------------------------------------
     |  New Chat + navbar wiring
     |----------------------------------------------------------------- */
    const newChat = useCallback(() => {
        streamControllerRef.current?.abort();
        streamControllerRef.current = null;
        resetConversation();
        setActiveId(null);
        setMessages([]);
        setDraft('');
        setError(null);
        setIsResponding(false);
        setShowJump(false);
        nearBottomRef.current = true;
    }, []);

    useEffect(() => {
        registerNewChat?.(newChat);
    }, [registerNewChat, newChat]);

    const toggleSidebar = useCallback(() => setSidebarOpen((v) => !v), []);
    useEffect(() => {
        registerToggleSidebar?.(toggleSidebar);
    }, [registerToggleSidebar, toggleSidebar]);

    useEffect(() => {
        return () => streamControllerRef.current?.abort();
    }, []);

    const isEmpty = messages.length === 0 && !isResponding && !error;

    return (
        <>
            <ConversationSidebar
                conversations={conversations}
                activeId={activeId}
                isOpen={sidebarOpen}
                isMobile={isMobile}
                loading={loadingList}
                onSelect={switchConversation}
                onNewChat={newChat}
                onRename={handleRename}
                onDelete={handleDelete}
                onCloseMobile={() => setSidebarOpen(false)}
            />

            <main
                className="chat-main"
                ref={mainRef}
                onScroll={handleScroll}
                aria-label="Conversation"
            >
                <div className="chat-inner">
                    {isEmpty ? (
                        <WelcomeScreen onPick={(q) => setDraft(q)} />
                    ) : (
                        <>
                            {messages.map((m) => (
                                <ChatMessage key={m.id} message={m} />
                            ))}

                            {isResponding &&
                                messages[messages.length - 1]?.role !== 'ai' && (
                                    <TypingIndicator />
                                )}

                            {error && (
                                <div className="chat-error" role="alert">
                                    <i className="bi bi-exclamation-triangle-fill"></i>
                                    <span>{error}</span>
                                    <button
                                        type="button"
                                        className="retry-btn"
                                        onClick={retry}
                                    >
                                        <i className="bi bi-arrow-clockwise me-1"></i>
                                        Retry
                                    </button>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </main>

            {showJump && (
                <ScrollToBottomButton
                    onClick={() => {
                        nearBottomRef.current = true;
                        setShowJump(false);
                        scrollToBottom('smooth');
                    }}
                />
            )}

            <MessageInput
                value={draft}
                onChange={setDraft}
                onSend={sendMessage}
                disabled={isResponding}
            />
        </>
    );
}
