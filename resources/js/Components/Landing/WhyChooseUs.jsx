import Reveal from './Reveal';

const features = [
    {
        icon: 'bi-lightning-charge-fill',
        title: 'Instant Answers',
        desc: 'Get responses in seconds — no queues, no waiting for office hours.',
    },
    {
        icon: 'bi-cpu-fill',
        title: 'AI Powered',
        desc: 'Understands natural language and answers the way you actually ask.',
    },
    {
        icon: 'bi-clock-history',
        title: 'Available Anytime',
        desc: 'Open 24/7 from any device, whenever you need information.',
    },
    {
        icon: 'bi-bullseye',
        title: 'Accurate Information',
        desc: 'Answers pulled straight from official university data.',
    },
];

export default function WhyChooseUs() {
    return (
        <section className="py-5 my-4" id="why">
            <div className="container">
                <Reveal className="text-center mb-5">
                    <h2 className="section-title mb-2">
                        Why Choose <span className="gradient-text">KTU Assistant</span>
                    </h2>
                    <p className="text-muted-soft fs-6 mb-0">
                        Built to make university information effortless.
                    </p>
                </Reveal>

                <div className="row g-4">
                    {features.map((f, i) => (
                        <div className="col-12 col-sm-6 col-lg-3" key={f.title}>
                            <Reveal delay={i * 90}>
                                <div className="ai-card feature-card text-center">
                                    <div className="ai-card-icon mx-auto">
                                        <i className={`bi ${f.icon}`}></i>
                                    </div>
                                    <h3>{f.title}</h3>
                                    <p>{f.desc}</p>
                                </div>
                            </Reveal>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
