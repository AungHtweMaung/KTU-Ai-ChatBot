import React, { useEffect, useState } from 'react';

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

function setSavedTheme(theme) {
  try {
    localStorage.setItem(THEME_KEY, theme);
  } catch {
    // Ignore localStorage errors in restricted browser contexts
  }
}

function applyTheme(theme) {
  if (typeof document !== 'undefined' && document.documentElement) {
    document.documentElement.setAttribute('data-bs-theme', theme);
  }
}

export default function ThemeToggle() {
  const [theme, setTheme] = useState(DEFAULT_THEME);

  useEffect(() => {
    const saved = getSavedTheme();
    setTheme(saved);
    applyTheme(saved);
  }, []);

  function toggle() {
    const next = theme === 'dark' ? 'light' : 'dark';
    setTheme(next);
    setSavedTheme(next);
    applyTheme(next);
  }

  return (
    <button className="icon-btn" onClick={toggle} aria-label="Toggle theme" title={theme === 'dark' ? 'Light mode' : 'Dark mode'}>
      {theme === 'dark' ? <i className="bi bi-sun"/> : <i className="bi bi-moon"/>}
    </button>
  );
}
