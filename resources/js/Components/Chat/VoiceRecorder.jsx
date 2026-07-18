import { useEffect, useRef, useState } from 'react';

/**
 * Browser voice-to-text using the Web Speech API (SpeechRecognition).
 *
 * Props:
 *  - onTranscript(text): called continuously with the recognized text so the
 *    parent can fill the textarea. Voice never auto-sends.
 *  - onRecordingChange(bool): notifies parent so it can show "Listening…".
 *  - disabled: external disable (e.g. while the AI is responding).
 *  - lang: recognition language ('en-US' or 'my-MM'). Defaults to en-US.
 *
 * If the API is unsupported the button is disabled with an informative tooltip.
 */
export default function VoiceRecorder({
    onTranscript,
    onRecordingChange,
    getBaseText,
    disabled = false,
    lang = 'en-US',
}) {
    const [supported, setSupported] = useState(true);
    const [recording, setRecording] = useState(false);
    const recognitionRef = useRef(null);
    const baseTextRef = useRef('');

    // Detect support once.
    useEffect(() => {
        const SpeechRecognition =
            window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            setSupported(false);
            return;
        }

        const recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = lang;

        recognition.onresult = (event) => {
            let transcript = '';
            for (let i = 0; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }
            // Append recognized speech to whatever was already in the box.
            const prefix = baseTextRef.current
                ? baseTextRef.current.replace(/\s*$/, '') + ' '
                : '';
            onTranscript(prefix + transcript);
        };

        recognition.onend = () => {
            setRecording(false);
            onRecordingChange?.(false);
        };

        recognition.onerror = () => {
            setRecording(false);
            onRecordingChange?.(false);
        };

        recognitionRef.current = recognition;

        return () => {
            try {
                recognition.abort();
            } catch {
                /* noop */
            }
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Keep language in sync if the parent changes it.
    useEffect(() => {
        if (recognitionRef.current) recognitionRef.current.lang = lang;
    }, [lang]);

    const toggle = () => {
        const recognition = recognitionRef.current;
        if (!recognition) return;

        if (recording) {
            recognition.stop();
            return;
        }

        // Remember current textarea text so speech appends rather than replaces.
        baseTextRef.current = typeof getBaseText === 'function' ? getBaseText() : '';
        try {
            recognition.start();
            setRecording(true);
            onRecordingChange?.(true);
        } catch {
            /* start() throws if already started — ignore */
        }
    };

    if (!supported) {
        return (
            <button
                type="button"
                className="input-icon-btn"
                disabled
                title="Voice input is not supported in this browser"
                aria-label="Voice input not supported"
            >
                <i className="bi bi-mic-mute"></i>
            </button>
        );
    }

    return (
        <button
            type="button"
            className={`input-icon-btn ${recording ? 'recording' : ''}`}
            onClick={toggle}
            disabled={disabled}
            title={recording ? 'Stop recording' : 'Start voice input'}
            aria-label={recording ? 'Stop voice input' : 'Start voice input'}
            aria-pressed={recording}
        >
            <i className={`bi ${recording ? 'bi-mic-fill' : 'bi-mic'}`}></i>
        </button>
    );
}
