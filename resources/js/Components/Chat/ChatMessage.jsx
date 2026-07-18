import Avatar from './Avatar';
import MessageBubble from './MessageBubble';
import Timestamp from './Timestamp';

/**
 * A single turn in the conversation. Handles alignment (user right / AI left),
 * avatar, bubble, and timestamp.
 *
 * message = { id, role: 'user' | 'ai', content, createdAt }
 */
export default function ChatMessage({ message }) {
    const isUser = message.role === 'user';

    return (
        <div className={`chat-message ${isUser ? 'user' : 'ai'}`}>
            <Avatar role={message.role} />
            <div className="msg-col">
                <MessageBubble role={message.role} content={message.content} />
                <Timestamp value={message.createdAt} />
            </div>
        </div>
    );
}
