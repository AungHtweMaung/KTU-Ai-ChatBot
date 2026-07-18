/**
 * Floating "Jump to latest" button. Rendered only when the user has scrolled
 * away from the bottom of the conversation.
 */
export default function ScrollToBottomButton({ onClick }) {
    return (
        <button
            type="button"
            className="jump-latest"
            onClick={onClick}
            aria-label="Jump to latest message"
        >
            <i className="bi bi-arrow-down" aria-hidden="true"></i>
            Jump to latest
        </button>
    );
}
