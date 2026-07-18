import Reveal from './Reveal';

const conversations = [
    [
        { from: 'stu', text: 'Who teaches Web Development?' },
        { from: 'ai', text: 'U Aung Kyaw teaches Web Development.' },
    ],
    [
        { from: 'stu', text: 'How much is registration for First Year IT?' },
        { from: 'ai', text: 'The registration fee is 180,000 MMK.' },
    ],
    [
        { from: 'stu', text: 'Are there any announcements today?' },
        {
            from: 'ai',
            text: 'Yes. Midterm examination registration opens next Monday.',
        },
    ],
];

function Bubble({ from, text }) {
    return (
        <div className={`convo-row ${from === 'stu' ? 'user' : 'ai'}`}>
            <span className={`chat-avatar ${from === 'stu' ? 'stu' : 'ai'}`}>
                <i className={`bi ${from === 'stu' ? 'bi-person-fill' : 'bi-robot'}`}></i>
            </span>
            <div>
                <div className="chat-label">{from === 'stu' ? 'Student' : 'Assistant'}</div>
                <div className="chat-bubble">{text}</div>
            </div>
        </div>
    );
}

export default function ConversationPreview() {
    return (
        <section className="py-5 my-4" id="examples">
            <div className="container">
                <Reveal className="text-center mb-5">
                    <h2 className="section-title mb-2">
                        See It <span className="gradient-text">In Action</span>
                    </h2>
                    <p className="text-muted-soft fs-6 mb-0">
                        Real questions, real answers — the way a conversation should feel.
                    </p>
                </Reveal>

                <div className="row g-4">
                    {conversations.map((convo, i) => (
                        <div className="col-12 col-lg-4" key={i}>
                            <Reveal delay={i * 100}>
                                <div className="convo-card">
                                    {convo.map((msg, j) => (
                                        <Bubble key={j} from={msg.from} text={msg.text} />
                                    ))}
                                </div>
                            </Reveal>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
