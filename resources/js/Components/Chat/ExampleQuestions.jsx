const EXAMPLES = [
    { icon: 'bi-calendar-check', text: 'How much is the Registeration Fee?' },
    { icon: 'bi-clock', text: "How many majors are there?" },
    // { icon: 'bi-cash-coin', text: '?' },
    // { icon: 'bi-person-badge', text: 'Who is the Head of Computer Science?' },
];

/**
 * Four example prompt cards. Clicking one places its text into the input
 * (via onPick) — it does NOT auto-send.
 */
export default function ExampleQuestions({ onPick }) {
    return (
        <div className="example-grid">
            {EXAMPLES.map((ex) => (
                <button
                    key={ex.text}
                    type="button"
                    className="example-card"
                    onClick={() => onPick(ex.text)}
                >
                    <i className={`bi ${ex.icon}`} aria-hidden="true"></i>
                    <span>{ex.text}</span>
                </button>
            ))}
        </div>
    );
}
