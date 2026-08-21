import { Head } from '@inertiajs/react';
import { useRef } from 'react';
import '../../../css/chat.css';
import Navbar from '../../Components/Chat/Navbar';
import ChatContainer from '../../Components/Chat/ChatContainer';

export default function Index() {
    // ChatContainer registers its "new chat" and "toggle sidebar" handlers
    // here so the navbar buttons can trigger them without prop drilling.
    const newChatRef = useRef(() => {});
    const toggleSidebarRef = useRef(() => {});

    return (
        <>
            <Head title="KTU Assistant — Chat" />

            <div className="chat-app">
                <Navbar
                    onNewChat={() => newChatRef.current()}
                    onToggleSidebar={() => toggleSidebarRef.current()}
                />
                <ChatContainer
                    registerNewChat={(fn) => (newChatRef.current = fn)}
                    registerToggleSidebar={(fn) => (toggleSidebarRef.current = fn)}
                />
            </div>
        </>
    );
}
