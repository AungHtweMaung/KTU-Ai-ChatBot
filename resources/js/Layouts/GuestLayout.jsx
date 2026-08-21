import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
            <Link href="/" className="flex flex-col items-center">
                <ApplicationLogo className="h-24 w-24" />
                <div className="mt-3 text-center">
                    <div className="text-lg font-bold tracking-tight text-gray-900">
                        KTU Assistant
                    </div>
                    <div className="text-xs text-gray-500">
                        AI-powered University Chatbot
                    </div>
                </div>
            </Link>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                {children}
            </div>
        </div>
    );
}
