import ExampleQuestions from './ExampleQuestions';

const TOPICS = [
    'Admissions',
    'Registration',
    'Timetable',
    'Departments',
    'Teachers',
    'Events',
    'Announcements',
    'Fees',
    'Scholarships',
];

/**
 * Shown when the conversation is empty. Includes the large AI icon, heading,
 * supported topics, and the four example prompt cards.
 */
export default function WelcomeScreen({ onPick }) {
    return (
        <div className="welcome-screen">
            <div className="welcome-icon" aria-hidden="true">
                <i className="bi bi-stars"></i>
            </div>

            <h1>How can I help you today?</h1>

            <p className="text-muted mb-2">Ask anything about:</p>
            <div className="welcome-topics">
                {TOPICS.map((t) => (
                    <span key={t}>{t}</span>
                ))}
            </div>

            <ExampleQuestions onPick={onPick} />
        </div>
    );
}
