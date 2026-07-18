/**
 * Message avatar. `role` is either "ai" or "user".
 */
export default function Avatar({ role }) {
    const isAi = role === 'ai';
    return (
        <span
            className={`msg-avatar ${isAi ? 'ai' : 'user'}`}
            aria-hidden="true"
        >
            <i className={`bi ${isAi ? 'bi-robot' : 'bi-person-fill'}`}></i>
        </span>
    );
}
