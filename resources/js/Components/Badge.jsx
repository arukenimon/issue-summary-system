export default function Badge({ value, type = 'default' }) {
    const styles = {
        // Priority
        critical: 'bg-red-100 text-red-800 ring-red-600/20',
        high:     'bg-orange-100 text-orange-800 ring-orange-600/20',
        medium:   'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
        low:      'bg-green-100 text-green-800 ring-green-600/20',
        // Status
        open:        'bg-blue-100 text-blue-800 ring-blue-600/20',
        in_progress: 'bg-purple-100 text-purple-800 ring-purple-600/20',
        resolved:    'bg-green-100 text-green-800 ring-green-600/20',
        closed:      'bg-gray-100 text-gray-600 ring-gray-500/20',
        // Category
        bug:            'bg-red-50 text-red-700 ring-red-600/10',
        feature:        'bg-indigo-50 text-indigo-700 ring-indigo-600/10',
        infrastructure: 'bg-teal-50 text-teal-700 ring-teal-600/10',
        security:       'bg-pink-50 text-pink-700 ring-pink-600/10',
        other:          'bg-gray-50 text-gray-600 ring-gray-500/10',
        // Default
        default: 'bg-gray-100 text-gray-700 ring-gray-500/20',
    };

    const label = value?.replace('_', ' ');
    const cls = styles[value] ?? styles.default;

    return (
        <span className={`inline-flex items-center whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ${cls}`}>
            {label}
        </span>
    );
}
