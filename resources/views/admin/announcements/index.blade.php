@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/30">
                Platform Communication
            </span>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                System Announcements
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Publish, edit, and broadcast operational notices displayed dynamically across user dashboards.
            </p>
        </div>

        <a href="{{ route('admin.announcements.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-600/20 transition-all self-start sm:self-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Create Announcement</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.announcements.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Type Filter -->
            <div class="w-full sm:w-48">
                <select name="type" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2 rounded-xl text-xs font-medium border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Notice Types</option>
                    <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Success</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2 rounded-xl text-xs font-medium border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Visibility States</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive / Hidden</option>
                </select>
            </div>

            @if(request()->hasAny(['type', 'status']))
                <a href="{{ route('admin.announcements.index') }}"
                   class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <!-- Announcements Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                Published Announcements ({{ $announcements->total() }})
            </h2>
            <span class="text-xs text-slate-400">Live on user dashboard</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Title & Notice</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Type</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Live Status</th>
                        <th class="py-3.5 px-4 font-semibold">Created Date</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($announcements as $announcement)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <!-- Title & Notice Content -->
                            <td class="py-4 px-4 max-w-md">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $announcement->title }}
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 mt-0.5 text-xs line-clamp-2 leading-relaxed">
                                    {{ $announcement->content }}
                                </p>
                            </td>

                            <!-- Type Badge -->
                            <td class="py-4 px-4 text-center">
                                @if($announcement->type === 'warning')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                        Warning
                                    </span>
                                @elseif($announcement->type === 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">
                                        Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/30">
                                        Info
                                    </span>
                                @endif
                            </td>

                            <!-- Live Status & Toggle Button -->
                            <td class="py-4 px-4 text-center">
                                <form method="POST" action="{{ route('admin.announcements.status', $announcement) }}" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            title="Click to toggle visibility"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold transition cursor-pointer {{ $announcement->is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $announcement->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $announcement->is_active ? 'Active' : 'Hidden' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Created Date -->
                            <td class="py-4 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs">
                                <div>{{ $announcement->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $announcement->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Action Buttons: Edit & Delete -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 transition">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}"
                                          onsubmit="return confirm('Are you sure you want to permanently delete this announcement?');"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 border border-red-200 dark:border-red-500/30 transition cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="max-w-sm mx-auto">
                                    <svg class="w-10 h-10 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                    </svg>
                                    <p class="font-bold text-slate-700 dark:text-slate-300">No Announcements Found</p>
                                    <p class="text-xs text-slate-400 mt-1">Create an announcement to broadcast operational notices to portal users.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
