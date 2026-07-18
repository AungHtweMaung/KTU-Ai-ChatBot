/**
 * Mock API layer for the chat UI.
 *
 * These are placeholder functions that emulate a backend so the frontend can
 * be built and demoed without APIs. When the Laravel backend is ready, replace
 * the bodies of `streamAssistantReply` (and optionally `initialMessages`) with
 * real HTTP / SSE calls — the component contract stays the same, so no
 * refactoring of the UI components is required.
 */

let idCounter = 0;
export const nextId = () => `m_${Date.now()}_${idCounter++}`;

/**
 * Optional seed conversation. Return [] to start on the welcome screen.
 */
export const initialMessages = [];

/**
 * Canned Markdown replies keyed by simple keyword matching, so the demo feels
 * realistic. A real backend would replace this entirely.
 */
const CANNED = [
    {
        match: /regist/i,
        reply: `Registration for the **2026–2027** academic year runs:

| Stage | Dates |
| --- | --- |
| Early registration | **Aug 10 – Aug 20** |
| Regular registration | Aug 21 – Sep 5 |
| Late registration | Sep 6 – Sep 12 *(late fee applies)* |

You can register online through the [student portal](https://portal.ktu.edu).`,
    },
    {
        match: /timetable|schedule|class/i,
        reply: `Here is a sample of **today's timetable** for *First Year Computer Science*:

- **09:00 – 10:30** — Database Systems (Room B-201)
- **10:45 – 12:15** — Web Development (Lab 3)
- **13:30 – 15:00** — Discrete Mathematics (Room A-105)

Would you like the full week's schedule?`,
    },
    {
        match: /fee|tuition|cost|scholarship/i,
        reply: `### Tuition & Fees (per year)

| Program | Tuition | Registration |
| --- | ---: | ---: |
| Computer Science | 1,200,000 MMK | 150,000 MMK |
| Information Technology | 1,100,000 MMK | 180,000 MMK |
| Civil Engineering | 1,000,000 MMK | 150,000 MMK |

> Scholarships covering up to **50%** of tuition are available for students with a GPA above 3.5.`,
    },
    {
        match: /head|teacher|who teaches|professor|dr\.?/i,
        reply: `The **Head of the Computer Science Department** is **Dr. Aung Kyaw**.

He also teaches:

1. Database Systems
2. Advanced Algorithms

You can reach the department office at \`cs.office@ktu.edu\`.`,
    },
    {
        match: /department|major/i,
        reply: `KTU currently offers these departments:

- 🖥️ **Computer Science**
- 🌐 **Information Technology**
- 🏗️ **Civil Engineering**
- ⚡ **Electrical Engineering**

Ask me about any of them for majors, subjects, or teachers.`,
    },
    {
        match: /announce|event|news/i,
        reply: `**Latest announcements:**

- 📢 Midterm examination registration opens **next Monday**.
- 🎉 The Annual Tech Fair is scheduled for **September 15** in the Main Auditorium.

Want details on any of these?`,
    },
];

const DEFAULT_REPLY = `I'm the **KTU Assistant** 🤖 — a demo running on mock data.

I can help with:

- Admissions & Registration
- Timetables & Departments
- Teachers, Events & Announcements
- Fees & Scholarships

Try one of the example questions, or ask me anything about KTU.`;

function pickReply(userText) {
    const hit = CANNED.find((c) => c.match.test(userText));
    return hit ? hit.reply : DEFAULT_REPLY;
}

/**
 * Emulates a streaming assistant reply.
 *
 * @param {string} userText          The user's message.
 * @param {(chunk:string)=>void} onChunk  Called with the cumulative text so far.
 * @param {object} [opts]
 * @param {AbortSignal} [opts.signal] Optional abort signal.
 * @param {boolean} [opts.forceError] Force a failure (used by the demo).
 * @returns {Promise<string>} Resolves with the full text once complete.
 *
 * TODO(backend): replace with a fetch to POST /api/chat that streams tokens
 * (SSE or chunked). Keep the (userText, onChunk) contract.
 */
export function streamAssistantReply(userText, onChunk, opts = {}) {
    const { signal, forceError = false } = opts;

    return new Promise((resolve, reject) => {
        // Simulate initial network / "thinking" latency.
        const thinkDelay = 500 + Math.random() * 500;

        const startTimer = setTimeout(() => {
            if (forceError) {
                reject(new Error('Network error'));
                return;
            }

            const full = pickReply(userText);
            // Stream word-by-word for a natural typing effect.
            const tokens = full.split(/(\s+)/);
            let i = 0;
            let acc = '';

            const tick = () => {
                if (signal?.aborted) {
                    clearInterval(streamTimer);
                    reject(new DOMException('Aborted', 'AbortError'));
                    return;
                }
                acc += tokens[i];
                onChunk(acc);
                i++;
                if (i >= tokens.length) {
                    clearInterval(streamTimer);
                    resolve(full);
                }
            };

            const streamTimer = setInterval(tick, 28);
        }, thinkDelay);

        // Allow cancellation during the think phase too.
        signal?.addEventListener('abort', () => {
            clearTimeout(startTimer);
            reject(new DOMException('Aborted', 'AbortError'));
        });
    });
}
