import Reveal from './Reveal';

const topics = [
    {
        icon: 'bi-cash-coin',
        title: 'Registration Fees',
        desc: 'Check tuition and registration fees for any major and year.',
    },
    {
        icon: 'bi-building',
        title: 'Departments',
        desc: 'Explore academic departments and what they offer.',
    },
    {
        icon: 'bi-journal-text',
        title: 'Subjects',
        desc: 'Find subject details, credits, and descriptions.',
    },
    {
        icon: 'bi-person-badge',
        title: 'Teachers',
        desc: 'Discover who teaches a subject and their expertise.',
    },
    {
        icon: 'bi-megaphone',
        title: 'Announcements',
        desc: 'Stay updated with the latest university announcements.',
    },
    {
        icon: 'bi-calendar2-event',
        title: 'Events',
        desc: 'Never miss seminars, holidays, and campus events.',
    },
    {
        icon: 'bi-question-circle',
        title: 'Frequently Asked Questions',
        desc: 'Get quick answers to the most common questions.',
    },
];

export default function SupportedTopics() {
    return (
        <section className="py-5 my-4" id="topics">
            <div className="container">
                <Reveal className="text-center mb-5">
                    <h2 className="section-title mb-2">
                        What Can <span className="gradient-text">KTU Assistant</span> Help You With?
                    </h2>
                    <p className="text-muted-soft fs-6 mb-0">
                        One assistant for everything you need to know about your university.
                    </p>
                </Reveal>

                <div className="row g-4">
                    {topics.map((t, i) => (
                        <div className="col-12 col-sm-6 col-lg-4 col-xl-3" key={t.title}>
                            <Reveal delay={(i % 4) * 80}>
                                <div className="ai-card">
                                    <div className="ai-card-icon">
                                        <i className={`bi ${t.icon}`}></i>
                                    </div>
                                    <h3>{t.title}</h3>
                                    <p>{t.desc}</p>
                                </div>
                            </Reveal>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
