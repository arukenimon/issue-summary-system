import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

export default function Show({ issue, priorities, categories, statuses, editing: initialEditing = false }) {
    const [editing, setEditing] = useState(initialEditing);

    const { data, setData, patch, processing, errors, reset } = useForm({
        title:       issue.title,
        description: issue.description,
        priority:    issue.priority,
        category:    issue.category,
        status:      issue.status,
    });

    function submit(e) {
        e.preventDefault();
        patch(route('issues.update', issue.id), {
            onSuccess: () => setEditing(false),
        });
    }

    function handleDelete() {
        if (!confirm('Are you sure you want to delete this issue?')) return;
        router.delete(route('issues.destroy', issue.id));
    }

    function cancelEdit() {
        reset();
        setEditing(false);
    }

    return (
        <>
            <Head title={`Issue #${issue.id}`} />

            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm">
                    <div className="mx-auto max-w-4xl px-4 py-4 sm:px-6 lg:px-8 flex items-center gap-3">
                        <Link href={route('issues.index')} className="text-gray-400 hover:text-gray-600">
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                        </Link>
                        <div className="flex-1">
                            <div className="flex items-center gap-2">
                                <h1 className="text-lg font-bold text-gray-900">Issue #{issue.id}</h1>
                                {issue.is_escalated && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
                                        <span className="h-1.5 w-1.5 rounded-full bg-red-500" />
                                        Escalated
                                    </span>
                                )}
                            </div>
                            <p className="text-xs text-gray-400 mt-0.5">
                                Created {new Date(issue.created_at).toLocaleString()}
                                {issue.updated_at !== issue.created_at && ` · Updated ${new Date(issue.updated_at).toLocaleString()}`}
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            {!editing && (
                                <>
                                    <button
                                        onClick={() => setEditing(true)}
                                        className="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-600 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={handleDelete}
                                        className="rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 ring-1 ring-red-200 hover:bg-red-100 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </>
                            )}
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8 space-y-5">

                    {editing ? (
                        /* Edit form */
                        <div className="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                            <h2 className="text-base font-semibold text-gray-900 mb-5">Edit Issue</h2>
                            <form onSubmit={submit} className="space-y-5">
                                <div>
                                    <InputLabel htmlFor="title" value="Title" />
                                    <TextInput
                                        id="title"
                                        value={data.title}
                                        onChange={e => setData('title', e.target.value)}
                                        className="mt-1 block w-full"
                                    />
                                    <InputError message={errors.title} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="description" value="Description" />
                                    <textarea
                                        id="description"
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
                                        rows={5}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    />
                                    <InputError message={errors.description} className="mt-1" />
                                </div>

                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <InputLabel htmlFor="priority" value="Priority" />
                                        <select
                                            id="priority"
                                            value={data.priority}
                                            onChange={e => setData('priority', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        >
                                            {priorities.map(p => <option key={p} value={p}>{p}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="category" value="Category" />
                                        <select
                                            id="category"
                                            value={data.category}
                                            onChange={e => setData('category', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        >
                                            {categories.map(c => <option key={c} value={c}>{c}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="status" value="Status" />
                                        <select
                                            id="status"
                                            value={data.status}
                                            onChange={e => setData('status', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        >
                                            {statuses.map(s => <option key={s} value={s}>{s.replace('_', ' ')}</option>)}
                                        </select>
                                    </div>
                                </div>

                                <div className="rounded-lg bg-amber-50 ring-1 ring-amber-100 p-3 text-xs text-amber-700">
                                    Saving will regenerate the AI summary if title, description, priority, or category changed.
                                </div>

                                <div className="flex justify-end gap-3">
                                    <button type="button" onClick={cancelEdit} className="text-sm font-medium text-gray-600 hover:text-gray-800">
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
                                    >
                                        {processing ? 'Saving…' : 'Save Changes'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    ) : (
                        /* View mode */
                        <>
                            {/* Issue details card */}
                            <div className="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 space-y-5">
                                <div>
                                    <h2 className="text-xl font-bold text-gray-900">{issue.title}</h2>
                                    <div className="flex flex-wrap gap-2 mt-3">
                                        <Badge value={issue.priority} />
                                        <Badge value={issue.category} />
                                        <Badge value={issue.status} />
                                    </div>
                                </div>

                                <div>
                                    <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Description</h3>
                                    <p className="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{issue.description}</p>
                                </div>
                            </div>

                            {/* AI summary card */}
                            {(issue.summary || issue.next_action) && (
                                <div className="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl shadow-sm ring-1 ring-indigo-100 p-6 space-y-4">
                                    <div className="flex items-center gap-2">
                                        <svg className="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                        </svg>
                                        <h3 className="text-sm font-semibold text-indigo-800">Smart Summary</h3>
                                    </div>

                                    {issue.summary && (
                                        <div>
                                            <p className="text-xs font-medium text-indigo-500 uppercase tracking-wide mb-1">Summary</p>
                                            <p className="text-sm text-gray-800">{issue.summary}</p>
                                        </div>
                                    )}

                                    {issue.next_action && (
                                        <div className="border-t border-indigo-100 pt-4">
                                            <p className="text-xs font-medium text-indigo-500 uppercase tracking-wide mb-1">Suggested Next Action</p>
                                            <div className="flex items-start gap-2">
                                                <svg className="h-4 w-4 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                </svg>
                                                <p className="text-sm text-gray-800">{issue.next_action}</p>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Escalation notice */}
                            {issue.is_escalated && (
                                <div className="rounded-xl bg-red-50 ring-1 ring-red-200 p-4 flex gap-3">
                                    <svg className="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                    <div>
                                        <p className="text-sm font-semibold text-red-800">This issue is flagged for escalation</p>
                                        <p className="text-xs text-red-600 mt-0.5">
                                            High or critical priority issues that remain open or in-progress are automatically escalated.
                                        </p>
                                    </div>
                                </div>
                            )}
                        </>
                    )}
                </main>
            </div>
        </>
    );
}
