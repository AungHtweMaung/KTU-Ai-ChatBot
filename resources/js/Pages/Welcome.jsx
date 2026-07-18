import { Head } from '@inertiajs/react';
import '../../css/landing.css';
import Navbar from '../Components/Landing/Navbar';
import HeroSection from '../Components/Landing/HeroSection';
import SupportedTopics from '../Components/Landing/SupportedTopics';
import WhyChooseUs from '../Components/Landing/WhyChooseUs';
import ConversationPreview from '../Components/Landing/ConversationPreview';
import CallToAction from '../Components/Landing/CallToAction';
import Footer from '../Components/Landing/Footer';

export default function Welcome() {
    return (
        <>
            <Head title="KTU Assistant — Your Intelligent University Assistant" />

            <div className="landing-page">
                <Navbar />
                <main>
                    <HeroSection />
                    <SupportedTopics />
                    <WhyChooseUs />
                    <ConversationPreview />
                    <CallToAction />
                </main>
                <Footer />
            </div>
        </>
    );
}
