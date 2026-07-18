import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

/**
 * Renders a single message's content.
 * - User messages are plain text (preserving line breaks).
 * - Assistant messages render Markdown (headings, tables, lists, bold,
 *   italic, links, fenced code blocks) via react-markdown + remark-gfm.
 */
export default function MessageBubble({ role, content }) {
    const isAi = role === 'ai';

    return (
        <div className="msg-bubble">
            {isAi ? (
                <div className="md-content">
                    <ReactMarkdown
                        remarkPlugins={[remarkGfm]}
                        components={{
                            // Open links in a new tab safely.
                            a: ({ node, ...props }) => (
                                <a target="_blank" rel="noopener noreferrer" {...props} />
                            ),
                            // Wrap tables so they can scroll horizontally on mobile.
                            table: ({ node, ...props }) => (
                                <div className="table-wrap">
                                    <table {...props} />
                                </div>
                            ),
                        }}
                    >
                        {content}
                    </ReactMarkdown>
                </div>
            ) : (
                <span style={{ whiteSpace: 'pre-wrap' }}>{content}</span>
            )}
        </div>
    );
}
