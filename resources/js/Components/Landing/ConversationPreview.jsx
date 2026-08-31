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

                {/* <div className="row g-4">
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
                </div> */}

                {/* Two-image showcase row */}
                <div className="row g-4 mt-2 justify-content-center">
                    {['/images/preview-1.png', '/images/preview-2.png'].map((src, i) => (
                        <div className="col-12 col-md-6" key={src}>
                            <Reveal delay={i * 100}>
                                <img
                                    src={src}
                                    alt={`KTU Assistant preview ${i + 1}`}
                                    className="img-fluid w-100"
                                    style={{
                                        borderRadius: '1rem',
                                        height: '400px',
                                        border: '1px solid var(--bs-border-color, rgba(255,255,255,0.1))',
                                    }}
                                />
                            </Reveal>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
