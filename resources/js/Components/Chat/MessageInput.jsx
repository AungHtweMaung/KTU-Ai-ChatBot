import { useCallback, useEffect, useRef, useState } from 'react';
import VoiceRecorder from './VoiceRecorder';

const MAX_LINES = 6;

// Matches the layout breakpoint ChatContainer uses for its mobile behaviour.
const MOBILE_BREAKPOINT = 992;

/**
 * Fixed bottom input.
 *  - Auto-growing textarea (up to 6 lines).
 *  - Enter sends, Shift+Enter inserts a newline.
 *  - Attachment (placeholder), voice, and send controls.
 *  - Disabled while the AI is responding.
 *  - Keeps itself focused so the user can always just start typing.
 *
 * Controlled by the parent via `value` / `onChange` so example cards and
 * voice input can populate it. `focusKey` is any value that changes when a
 * different conversation is opened, which re-focuses the textarea.
 */
export default function MessageInput({ value, onChange, onSend, disabled, focusKey }) {
    const textareaRef = useRef(null);
    const [listening, setListening] = useState(false);
    const [lang, setLang] = useState('en-US');

    // Auto-grow: reset height then grow to content, capped at MAX_LINES.
    useEffect(() => {
        const el = textareaRef.current;
        if (!el) return;
        el.style.height = 'auto';
        const lineHeight = parseFloat(getComputedStyle(el).lineHeight) || 24;
        const maxHeight = lineHeight * MAX_LINES;
        el.style.height = Math.min(el.scrollHeight, maxHeight) + 'px';
    }, [value]);

    // No-ops while the textarea is disabled, so callers don't have to check.
    const focusTextarea = useCallback(() => {
        const el = textareaRef.current;
        if (el && !el.disabled) el.focus();
    }, []);

    /* Ready to type on arrival, and again whenever another conversation is
       opened. Skipped on narrow layouts, where focusing would raise the
       virtual keyboard over the transcript before the user asked to type. */
    useEffect(() => {
        if (window.innerWidth < MOBILE_BREAKPOINT) return;
        focusTextarea();
    }, [focusKey, focusTextarea]);

    /* Hand focus back when a reply finishes and the textarea is re-enabled, so
       the next question needs no click. This runs on every layout: the user was
       already typing here, so the keyboard is welcome back. */
    const wasDisabledRef = useRef(disabled);
    useEffect(() => {
        if (wasDisabledRef.current && !disabled) focusTextarea();
        wasDisabledRef.current = disabled;
    }, [disabled, focusTextarea]);

    const submit = () => {
        const trimmed = value.trim();
        if (!trimmed || disabled) return;
        onSend(trimmed);
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            submit();
        }
    };

    const canSend = value.trim().length > 0 && !disabled;

    return (
        <div className="chat-input-region">
            <div className="chat-input-wrap">
                <div className="chat-input-box">
                    <textarea
                        ref={textareaRef}
                        rows={1}
                        className="chat-textarea"
                        placeholder="Ask anything about KTU..."
                        value={value}
                        disabled={disabled}
                        onChange={(e) => onChange(e.target.value)}
                        onKeyDown={handleKeyDown}
                        aria-label="Message input"
                    />

                    {/* Voice language toggle (English / Myanmar) */}
                    <button
                        type="button"
                        className="input-icon-btn"
                        title={`Voice language: ${lang === 'en-US' ? 'English' : 'Myanmar'} (click to switch)`}
                        aria-label={`Voice recognition language, currently ${lang === 'en-US' ? 'English' : 'Myanmar'}`}
                        onClick={() => setLang((l) => (l === 'en-US' ? 'my-MM' : 'en-US'))}
                        disabled={disabled}
                        style={{ fontSize: '0.72rem', fontWeight: 700 }}
                    >
                        {lang === 'en-US' ? 'EN' : 'MY'}
                    </button>

                    <VoiceRecorder
                        onTranscript={onChange}
                        onRecordingChange={setListening}
                        getBaseText={() => value}
                        disabled={disabled}
                        lang={lang}
                    />

                    <button
                        type="button"
                        className="send-btn"
                        onClick={submit}
                        disabled={!canSend}
                        title="Send message"
                        aria-label="Send message"
                    >
                        <i className="bi bi-send-fill"></i>
                    </button>
                </div>

                {listening ? (
                    <div className="listening-hint" aria-live="polite">
                        <i className="bi bi-mic-fill me-1"></i>
                        Listening…
                    </div>
                ) : (
                    <div className="input-hint">
                        Press <strong>Enter</strong> to send, <strong>Shift + Enter</strong> for a new line
                    </div>
                )}
            </div>
        </div>
    );
}
