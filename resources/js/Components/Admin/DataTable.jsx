export default function DataTable({ columns = [], data = [], actions }) {
    return (
        <div className="table-responsive">
            <table className="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        {columns.map((col) => (
                            <th
                                key={col.key}
                                style={{ color: 'var(--bs-body-color)', borderColor: 'var(--bs-border-color)' }}
                            >
                                {col.title}
                            </th>
                        ))}
                        {actions && (
                            <th style={{ borderColor: 'var(--bs-border-color)' }}>Actions</th>
                        )}
                    </tr>
                </thead>
                <tbody>
                    {data.length === 0 ? (
                        <tr>
                            <td
                                colSpan={columns.length + (actions ? 1 : 0)}
                                className="text-center text-muted py-4"
                            >
                                No records found.
                            </td>
                        </tr>
                    ) : (
                        data.map((row, index) => (
                            <tr key={row.id}>
                                {columns.map((col) => (
                                    <td key={col.key} style={{ borderColor: 'var(--bs-border-color)' }}>
                                        {col.render ? col.render(row, index) : row[col.key]}
                                    </td>
                                ))}
                                {actions && (
                                    <td style={{ borderColor: 'var(--bs-border-color)' }}>{actions(row)}</td>
                                )}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
