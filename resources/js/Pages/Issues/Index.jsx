import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';

export default function Index({ issues, filters, priorities, categories, statuses }) {
    const [form, setForm] = useState({
        status:   filters.status   ?? '',
        category: filters.category ?? '',
        priority: filters.priority ?? '',
    });

    function applyFilters(e) {
        e.preventDefault();
        router.get(route('issues.index'), form, { preserveState: true, replace: true });
    }

    function clearFilters() {
        const empty = { status: '', category: '', priority: '' };
        setForm(empty);
        router.get(route('issues.index'), {}, { preserveState: true, replace: true });
    }

    function handleDelete(id) {
        if (!confirm('Delete this issue?')) return;
        router.delete(route('issues.destroy', id));
    }

    const hasFilters = form.status || form.category || form.priority;

    return (
        <>
            <Head title="Issues" />

            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm">
                    <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Issue Tracker</h1>
                            <p className="text-sm text-gray-500 mt-0.5">Smart intake &amp; summary system</p>
                        </div>
                        <Link
                            href={route('issues.create')}
                            className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                        >
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Issue
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">

                    {/* Filters */}
                    <form onSubmit={applyFilters} className="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-4">
                        <div className="flex flex-wrap gap-3 items-end">
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</label>
                                <select
                                    value={form.status}
                                    onChange={e => setForm(f => ({ ...f, status: e.target.value }))}
                                    className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All statuses</option>
                                    {statuses.map(s => <option key={s} value={s}>{s.replace('_', ' ')}</option>)}
                                </select>
                            </div>
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                                <select
                                    value={form.category}
                                    onChange={e => setForm(f => ({ ...f, category: e.target.value }))}
                                    className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All categories</option>
                                    {categories.map(c => <option key={c} value={c}>{c}</option>)}
                                </select>
                            </div>
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">Priority</label>
                                <select
                                    value={form.priority}
                                    onChange={e => setForm(f => ({ ...f, priority: e.target.value }))}
                                    className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All priorities</option>
                                    {priorities.map(p => <option key={p} value={p}>{p}</option>)}
                                </select>
                            </div>
                            <button type="submit" className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition-colors">
                                Filter
                            </button>
                            {hasFilters && (
                                <button type="button" onClick={clearFilters} className="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-600 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                                    Clear
                                </button>
                            )}
                        </div>
                    </form>

                    {/* Stats bar */}
                    <div className="text-sm text-gray-500">
                        Showing <span className="font-semibold text-gray-800">{issues.total}</span> issue{issues.total !== 1 ? 's' : ''}
                        {hasFilters && ' (filtered)'}
                    </div>

                    {/* Table */}
                    <div className="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                        {issues.data.length === 0 ? (
                            <div className="py-16 text-center text-gray-400">
                                <svg className="mx-auto h-10 w-10 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                                </svg>
                                <p className="font-medium">No issues found</p>
                                <p className="text-xs mt-1">Try adjusting your filters or create a new issue.</p>
                            </div>
                        ) : (
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Issue</th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Priority</th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Summary</th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Created</th>
                                        <th className="relative px-4 py-3"><span className="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {issues.data.map(issue => (
                                        <tr key={issue.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-4 py-3 max-w-xs">
                                                <div className="flex items-start gap-2">
                                                    {issue.is_escalated && (
                                                        <span title="Escalated" className="mt-0.5 flex-shrink-0 inline-flex h-2 w-2 rounded-full bg-red-500 ring-2 ring-red-200" />
                                                    )}
                                                    <div>
                                                        <Link
                                                            href={route('issues.show', issue.id)}
                                                            className="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1"
                                                        >
                                                            {issue.title}
                                                        </Link>
                                                        <p className="text-xs text-gray-400 mt-0.5 line-clamp-1">{issue.description}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3"><Badge value={issue.priority} /></td>
                                            <td className="px-4 py-3"><Badge value={issue.category} /></td>
                                            <td className="px-4 py-3"><Badge value={issue.status} /></td>
                                            <td className="px-4 py-3 max-w-sm">
                                                {issue.summary
                                                    ? <p className="text-xs text-gray-500 line-clamp-2">{issue.summary}</p>
                                                    : <span className="text-xs text-gray-300 italic">—</span>
                                                }
                                            </td>
                                            <td className="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                                                {new Date(issue.created_at).toLocaleDateString()}
                                            </td>
                                            <td className="px-4 py-3 text-right whitespace-nowrap">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        href={route('issues.show', issue.id)}
                                                        className="text-indigo-600 hover:text-indigo-800 text-xs font-medium"
                                                    >
                                                        View
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(issue.id)}
                                                        className="text-red-500 hover:text-red-700 text-xs font-medium"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {/* Pagination */}
                    {issues.last_page > 1 && (
                        <div className="flex items-center justify-between">
                            <p className="text-sm text-gray-500">
                                Page {issues.current_page} of {issues.last_page}
                            </p>
                            <div className="flex gap-2">
                                {issues.prev_page_url && (
                                    <Link
                                        href={issues.prev_page_url}
                                        className="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-600 ring-1 ring-gray-300 hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {issues.next_page_url && (
                                    <Link
                                        href={issues.next_page_url}
                                        className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
                                    >
                                        Next
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
                </main>
            </div>
        </>
    );
}
