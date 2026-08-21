/**
 * Chat API + client-side transcript animation.
 *
 * The public shape (`initialMessages`, `nextId`, `streamAssistantReply`) is
 * unchanged from the earlier mock implementation, so ChatContainer needs no
 * modification. Two things are different under the hood:
 *
 * 1. `streamAssistantReply` now POSTs to `/chat/send` and gets a real answer
 *    from Laravel → ChatService → OpenAI/Gemini.
 * 2. Once the answer arrives, we still animate it word-by-word into the
 *    UI so it feels like streaming. Genuine SSE streaming can be added
 *    later without touching the components.
 */

import axios from 'axios';

let idCounter = 0;
export const nextId = () => `m_${Date.now()}_${idCounter++}`;

export const initialMessages = [];

/* ---------------- conversation identity ----------------
 *
 * `conversationId` is remembered client-side once the backend assigns one,
 * so subsequent messages stay in the same thread. A stable `guestUuid` in
 * localStorage lets an anonymous browser hold a persistent conversation
 * across refreshes. When the visitor is authenticated, the backend links
 * the conversation to their user account instead.
 */

const GUEST_KEY = 'ktu_chat_guest_uuid';
const CONVO_KEY = 'ktu_chat_conversation_id';

function ensureGuestUuid() {
    try {
        let uuid = localStorage.getItem(GUEST_KEY);
        if (!uuid) {
            uuid = window.crypto?.randomUUID?.() ?? fallbackUuid();
            localStorage.setItem(GUEST_KEY, uuid);
        }
        return uuid;
    } catch {
        return null;
    }
}

function fallbackUuid() {
    // RFC4122-style v4 uuid — used only when crypto.randomUUID is unavailable.
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

function getConversationId() {
    try {
        const v = localStorage.getItem(CONVO_KEY);
        return v ? Number(v) : null;
    } catch {
        return null;
    }
}

function setConversationId(id) {
    try {
        localStorage.setItem(CONVO_KEY, String(id));
    } catch {
        /* ignore */
    }
}

/** Wipe the current conversation binding (called by "New Chat"). */
export function resetConversation() {
    try {
        localStorage.removeItem(CONVO_KEY);
    } catch {
        /* ignore */
    }
}

/** Public accessors used by the sidebar. */
export function getCurrentConversationId() {
    return getConversationId();
}

export function bindCurrentConversationId(id) {
    setConversationId(id);
}

/* ---------------- conversation list / CRUD ---------------- */

/** GET /chat/conversations — list threads for this browser/user. */
export function fetchConversations() {
    return axios
        .get('/chat/conversations', {
            params: { guest_uuid: ensureGuestUuid() },
            headers: { Accept: 'application/json' },
        })
        .then((r) => r.data?.data ?? []);
}

/** GET /chat/conversations/{id} — full transcript for one thread. */
export function loadConversation(id) {
    return axios
        .get(`/chat/conversations/${id}`, {
            params: { guest_uuid: ensureGuestUuid() },
            headers: { Accept: 'application/json' },
        })
        .then((r) => r.data ?? { id, title: null, messages: [] });
}

/** PATCH — rename. */
export function renameConversation(id, title) {
    return axios
        .patch(
            `/chat/conversations/${id}`,
            { title, guest_uuid: ensureGuestUuid() },
            { headers: { Accept: 'application/json' } },
        )
        .then((r) => r.data);
}

/** DELETE — remove thread (and its messages via cascade). */
export function deleteConversation(id) {
    return axios.delete(`/chat/conversations/${id}`, {
        params: { guest_uuid: ensureGuestUuid() },
        headers: { Accept: 'application/json' },
    });
}

/* ---------------- streaming ---------------- */

// Word-by-word animation cadence (ms). Cheap way to make a non-streamed
// response feel alive.
const STREAM_TICK_MS = 22;

/**
 * Send the user's message to the backend and animate the reply.
 *
 * @param {string} userText
 * @param {(cumulative:string)=>void} onChunk
 * @param {object} [opts]
 * @param {AbortSignal} [opts.signal]
 * @returns {Promise<string>}
 */
export function streamAssistantReply(userText, onChunk, opts = {}) {
    const { signal } = opts;

    return new Promise((resolve, reject) => {
        const controller = new AbortController();
        if (signal) {
            if (signal.aborted) {
                reject(new DOMException('Aborted', 'AbortError'));
                return;
            }
            signal.addEventListener('abort', () => controller.abort());
        }

        const payload = {
            message: userText,
            conversation_id: getConversationId(),
            guest_uuid: ensureGuestUuid(),
        };

        axios
            .post('/chat/send', payload, {
                signal: controller.signal,
                headers: { Accept: 'application/json' },
            })
            .then((response) => {
                const data = response.data ?? {};

                if (data.conversation_id) {
                    setConversationId(data.conversation_id);
                }

                const fullText = String(data.assistant_message?.content ?? '');
                if (fullText === '') {
                    // Should not happen — the backend guarantees a response —
                    // but guard so the UI doesn't get stuck.
                    onChunk('');
                    resolve('');
                    return;
                }

                animateText(fullText, onChunk, controller.signal)
                    .then(() => resolve(fullText))
                    .catch(reject);
            })
            .catch((err) => {
                if (axios.isCancel?.(err) || err?.code === 'ERR_CANCELED') {
                    reject(new DOMException('Aborted', 'AbortError'));
                    return;
                }
                // Surface the friendly message from Laravel when available.
                const msg = err?.response?.data?.message;
                reject(new Error(msg || 'Network error'));
            });
    });
}

/**
 * Feed `full` into `onChunk` word-by-word so the UI renders a smooth
 * streaming-like transcript. Aborts cleanly if the signal fires.
 */
function animateText(full, onChunk, signal) {
    return new Promise((resolve, reject) => {
        const tokens = full.split(/(\s+)/); // keep whitespace tokens
        let i = 0;
        let acc = '';

        const timer = setInterval(() => {
            if (signal?.aborted) {
                clearInterval(timer);
                reject(new DOMException('Aborted', 'AbortError'));
                return;
            }
            acc += tokens[i++];
            onChunk(acc);
            if (i >= tokens.length) {
                clearInterval(timer);
                resolve();
            }
        }, STREAM_TICK_MS);
    });
}
