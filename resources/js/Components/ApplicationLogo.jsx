/**
 * The application logo — now the Kyaukse Technological University crest.
 *
 * Renders `public/images/ktu-logo.png` directly. Callers already pass
 * Tailwind size classes (e.g. `h-20 w-20`, `h-9 w-auto`) which apply to
 * the img element and control the display size.
 */
export default function ApplicationLogo({ className = '', style, ...rest }) {
    return (
        <img
            src="/images/ktu-logo.png"
            alt="KTU"
            className={className}
            style={{ objectFit: 'contain', ...style }}
            draggable="false"
            {...rest}
        />
    );
}
