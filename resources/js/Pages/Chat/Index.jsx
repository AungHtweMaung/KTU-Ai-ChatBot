import { Head } from '@inertiajs/react';
import { useRef } from 'react';
import '../../../css/chat.css';
import Navbar from '../../Components/Chat/Navbar';
import ChatContainer from '../../Components/Chat/ChatContainer';

export default function Index() {
    // ChatContainer registers its "new chat" handler here so the navbar can call it.
    const newChatRef = useRef(() => {});

    return (
        <>
            <Head title="KTU Assistant — Chat" />

            <div className="chat-app">
                <Navbar onNewChat={() => newChatRef.current()} />
                <ChatContainer registerNewChat={(fn) => (newChatRef.current = fn)} />
            </div>
        </>
    );
}
