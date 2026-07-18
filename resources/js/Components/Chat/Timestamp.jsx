/**
 * Formats an ISO/Date value to a short local time, e.g. "2:30 PM".
 */
export default function Timestamp({ value }) {
    if (!value) return null;

    const date = value instanceof Date ? value : new Date(value);
    const label = date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    });

    return (
        <time className="msg-timestamp" dateTime={date.toISOString()}>
            {label}
        </time>
    );
}
