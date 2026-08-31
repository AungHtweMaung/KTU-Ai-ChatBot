import { Dialog, DialogPanel } from '@headlessui/react';

/**
 * Reusable modal on the Headless UI **v2** API.
 *
 * Visibility is controlled by `open` on <Dialog> (v2), and `onClose` fires on
 * Escape / outside-click. We intentionally do NOT use Headless UI's
 * `transition` prop here: it keeps the dialog mounted until a CSS
 * transition-end fires, and if the transition utility classes don't resolve
 * the dialog never unmounts (the bug that made "Cancel" appear to do nothing).
 * A short CSS opacity fade on the panel/backdrop keeps it feeling smooth
 * without blocking unmount.
 */
export default function Modal({
    children,
    show = false,
    maxWidth = '2xl',
    closeable = true,
    onClose = () => {},
}) {
    const close = () => {
        if (closeable) {
            onClose();
        }
    };

    const maxWidthClass = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[maxWidth];

    return (
        <Dialog
            open={show}
            onClose={close}
            className="relative z-50 focus:outline-none"
        >
            {/* Backdrop */}
            <div className="fixed inset-0 bg-gray-500/75" aria-hidden="true" />

            {/* Full-screen scroll container that centers the panel */}
            <div className="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0">
                <DialogPanel
                    className={`w-full overflow-hidden rounded-lg bg-white shadow-xl sm:mx-auto ${maxWidthClass}`}
                >
                    {children}
                </DialogPanel>
            </div>
        </Dialog>
    );
}
