import { useCallback, useEffect, useRef, useState } from 'react';
import ChatMessage from './ChatMessage';
import TypingIndicator from './TypingIndicator';
import WelcomeScreen from './WelcomeScreen';
import MessageInput from './MessageInput';
import ScrollToBottomButton from './ScrollToBottomButton';
import { initialMessages, nextId, streamAssistantReply } from './mockApi';

const NEAR_BOTTOM_PX = 120;

/**
 * Orchestrates conversation state, the mock streaming reply, near-bottom
 * auto-scroll, and inline error + retry.
 */
export default function ChatContainer({ registerNewChat }) {
    const [messages, setMessages] = useState(initialMessages);
    const [draft, setDraft] = useState('');
    const [isResponding, setIsResponding] = useState(false);
    const [error, setError] = useState(null);
    const [showJump, setShowJump] = useState(false);

    const mainRef = useRef(null);
    const nearBottomRef = useRef(true);
    const lastUserTextRef = useRef('');
    const streamControllerRef = useRef(null);

    /* ---------- scroll helpers ---------- */
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

    // Auto-scroll on new content only if the user is already near the bottom.
    useEffect(() => {
        if (nearBottomRef.current) scrollToBottom('smooth');
    }, [messages, isResponding, scrollToBottom]);

    /* ---------- sending ---------- */
    const runAssistant = useCallback(
        (userText, { forceError = false } = {}) => {
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
                    // On the first chunk, replace the typing indicator with a
                    // real (growing) message bubble.
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
                { forceError, signal: controller.signal },
            )
                .then(() => {
                    if (streamControllerRef.current === controller) {
                        streamControllerRef.current = null;
                    }
                    setIsResponding(false);
                })
                .catch((err) => {
                    if (err?.name === 'AbortError') return;
                    if (streamControllerRef.current === controller) {
                        streamControllerRef.current = null;
                    }
                    setIsResponding(false);
                    setError('Unable to connect. Please try again.');
                });
        },
        [],
    );

    const sendMessage = useCallback(
        (text) => {
            const content = text.trim();
            if (!content || isResponding) return;

            lastUserTextRef.current = content;
            nearBottomRef.current = true; // sending always jumps us to the latest

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

    /* ---------- new chat ---------- */
    const newChat = useCallback(() => {
        streamControllerRef.current?.abort();
        streamControllerRef.current = null;
        setMessages([]);
        setDraft('');
        setError(null);
        setIsResponding(false);
        setShowJump(false);
        nearBottomRef.current = true;
    }, []);

    // Expose "New Chat" to the parent (so the navbar button can trigger it).
    useEffect(() => {
        registerNewChat?.(newChat);
    }, [registerNewChat, newChat]);

    useEffect(() => {
        return () => streamControllerRef.current?.abort();
    }, []);

    const isEmpty = messages.length === 0 && !isResponding && !error;

    return (
        <>
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

                            {/* Typing indicator only before the first streamed chunk */}
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
