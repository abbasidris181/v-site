@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header & Back Link -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.announcements.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Announcements</span>
        </a>

        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}"
              onsubmit="return confirm('Are you sure you want to permanently delete this announcement?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline cursor-pointer">
                Delete Announcement
            </button>
        </form>
    </div>

    <!-- Edit Card Form -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Edit Announcement
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Update announcement copy, alert severity level, or visibility state.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Announcement Title <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="title" name="title" required maxlength="150"
                       value="{{ old('title', $announcement->title) }}"
                       placeholder="e.g. Scheduled NIMC Gateway Maintenance"
                       class="block w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                @error('title')
                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Notice Type <span class="text-rose-500">*</span>
                </label>
                <select id="type" name="type" required
                        class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                    <option value="info" {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>Info (Informational & System Updates)</option>
                    <option value="warning" {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>Warning (Channel Downtime & Alerts)</option>
                    <option value="success" {{ old('type', $announcement->type) === 'success' ? 'selected' : '' }}>Success (Platform Enhancements & Upgrades)</option>
                </select>
                @error('type')
                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Announcement Body / Message <span class="text-rose-500">*</span>
                </label>
                <textarea id="content" name="content" rows="4" required
                          placeholder="Provide the details of the notice displayed to users on their dashboard..."
                          class="block w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 leading-relaxed">{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status Toggle -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Active & Visible Immediately</span>
                    <span class="text-xs text-slate-400">If enabled, this notice is displayed across all user dashboards.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-["'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.announcements.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
