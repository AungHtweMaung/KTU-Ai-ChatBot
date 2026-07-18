import Avatar from './Avatar';

/**
 * Shown while the assistant is "thinking", before the streamed response
 * replaces it.
 */
export default function TypingIndicator() {
    return (
        <div className="chat-message ai" aria-live="polite">
            <Avatar role="ai" />
            <div className="msg-col">
                <div className="typing-label">KTU Assistant is typing…</div>
                <div className="msg-bubble">
                    <span className="typing-dots" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </div>
            </div>
        </div>
    );
}
