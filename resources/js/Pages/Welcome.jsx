import { Head } from '@inertiajs/react';

export default function Welcome({ appName }) {
    return (
        <>
            <Head title="Welcome" />

            <div className="flex min-h-screen flex-col items-center justify-center bg-gray-50 text-gray-900">
                <div className="text-center">
                    <h1 className="text-4xl font-bold tracking-tight">
                        {appName}
                    </h1>
                    <p className="mt-3 text-lg text-gray-600">
                        Laravel + React + Inertia is up and running 🎉
                    </p>
                </div>
            </div>
        </>
    );
}
