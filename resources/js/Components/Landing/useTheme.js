import { useEffect, useState } from 'react';

const THEME_KEY = 'admin_theme';
const DEFAULT_THEME = 'light';

function getSavedTheme() {
    try {
        const stored = localStorage.getItem(THEME_KEY);
        return stored === 'dark' || stored === 'light' ? stored : DEFAULT_THEME;
    } catch {
        return DEFAULT_THEME;
    }
}

function applyTheme(theme) {
    if (typeof document !== 'undefined' && document.documentElement) {
        document.documentElement.setAttribute('data-bs-theme', theme);
    }
}

/**
 * Shared theme hook — reuses the same `admin_theme` localStorage key and
 * `data-bs-theme` attribute as the admin panel, so the preference carries
 * across the whole app.
 */
export default function useTheme() {
    const [theme, setTheme] = useState(DEFAULT_THEME);

    useEffect(() => {
        const saved = getSavedTheme();
        setTheme(saved);
        applyTheme(saved);
    }, []);

    const toggle = () => {
        const next = theme === 'dark' ? 'light' : 'dark';
        setTheme(next);
        try {
            localStorage.setItem(THEME_KEY, next);
        } catch {
            // ignore restricted storage contexts
        }
        applyTheme(next);
    };

    return { theme, toggle };
}
