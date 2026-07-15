import { useEffect, useRef, useState } from 'react';

export default function SearchInput({ value = '', onChange, placeholder = 'Search...', delay = 400 }) {
    const [term, setTerm] = useState(value);
    const isFirstRun = useRef(true);

    useEffect(() => {
        setTerm(value);
    }, [value]);

    useEffect(() => {
        if (isFirstRun.current) {
            isFirstRun.current = false;
            return;
        }

        const timeout = setTimeout(() => onChange(term), delay);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [term]);

    return (
        <div className="input-group" style={{ maxWidth: '320px' }}>
            <span
                className="input-group-text"
                style={{
                    backgroundColor: 'var(--bs-body-bg)',
                    color: 'var(--bs-body-color)',
                    borderColor: 'var(--bs-border-color)',
                }}
            >
                <i className="bi bi-search"></i>
            </span>
            <input
                type="text"
                className="form-control"
                style={{
                    backgroundColor: 'var(--bs-body-bg)',
                    color: 'var(--bs-body-color)',
                    borderColor: 'var(--bs-border-color)',
                }}
                placeholder={placeholder}
                value={term}
                onChange={(e) => setTerm(e.target.value)}
            />
            {term && (
                <button
                    type="button"
                    className="btn btn-outline-secondary"
                    style={{ borderColor: 'var(--bs-border-color)' }}
                    onClick={() => setTerm('')}
                    aria-label="Clear search"
                >
                    <i className="bi bi-x-lg"></i>
                </button>
            )}
        </div>
    );
}
