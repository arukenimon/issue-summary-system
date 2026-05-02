import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

export default function Create({ priorities, categories, statuses }) {
    const { data, setData, post, processing, errors } = useForm({
        title:       '',
        description: '',
        priority:    'medium',
        category:    'bug',
        status:      'open',
    });

    function submit(e) {
        e.preventDefault();
        post(route('issues.store'));
    }

    return (
        <>
            <Head title="New Issue" />

            <div className="min-h-screen bg-gray-50">
                <header className="bg-white shadow-sm">
                    <div className="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-8 flex items-center gap-3">
                        <Link href={route('issues.index')} className="text-gray-400 hover:text-gray-600">
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                        </Link>
                        <div>
                            <h1 className="text-xl font-bold text-gray-900">Report New Issue</h1>
                            <p className="text-xs text-gray-500 mt-0.5">A summary and next action will be generated automatically.</p>
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 space-y-6">
                        <form onSubmit={submit} className="space-y-6">

                            {/* Title */}
                            <div>
                                <InputLabel htmlFor="title" value="Title *" />
                                <TextInput
                                    id="title"
                                    value={data.title}
                                    onChange={e => setData('title', e.target.value)}
                                    className="mt-1 block w-full"
                                    placeholder="Short, descriptive title for the issue"
                                    required
                                />
                                <InputError message={errors.title} className="mt-1" />
                            </div>

                            {/* Description */}
                            <div>
                                <InputLabel htmlFor="description" value="Description *" />
                                <textarea
                                    id="description"
                                    value={data.description}
                                    onChange={e => setData('description', e.target.value)}
                                    rows={5}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="Describe the issue in detail — what happened, what was expected, steps to reproduce, impact..."
                                    required
                                />
                                <InputError message={errors.description} className="mt-1" />
                            </div>

                            {/* Row: Priority + Category + Status */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <InputLabel htmlFor="priority" value="Priority *" />
                                    <select
                                        id="priority"
                                        value={data.priority}
                                        onChange={e => setData('priority', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    >
                                        {priorities.map(p => (
                                            <option key={p} value={p}>{p.charAt(0).toUpperCase() + p.slice(1)}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.priority} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="category" value="Category *" />
                                    <select
                                        id="category"
                                        value={data.category}
                                        onChange={e => setData('category', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    >
                                        {categories.map(c => (
                                            <option key={c} value={c}>{c.charAt(0).toUpperCase() + c.slice(1)}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.category} className="mt-1" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="status" value="Status" />
                                    <select
                                        id="status"
                                        value={data.status}
                                        onChange={e => setData('status', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    >
                                        {statuses.map(s => (
                                            <option key={s} value={s}>{s.replace('_', ' ')}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} className="mt-1" />
                                </div>
                            </div>

                            {/* AI notice */}
                            <div className="rounded-lg bg-indigo-50 ring-1 ring-indigo-100 p-4 flex gap-3">
                                <svg className="h-5 w-5 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                </svg>
                                <p className="text-sm text-indigo-700">
                                    A <strong>smart summary</strong> and <strong>suggested next action</strong> will be generated automatically after submission.
                                </p>
                            </div>

                            {/* Actions */}
                            <div className="flex items-center justify-end gap-3 pt-2">
                                <Link href={route('issues.index')} className="text-sm font-medium text-gray-600 hover:text-gray-800">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    {processing && (
                                        <svg className="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                                        </svg>
                                    )}
                                    {processing ? 'Generating summary…' : 'Submit Issue'}
                                </button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </>
    );
}
