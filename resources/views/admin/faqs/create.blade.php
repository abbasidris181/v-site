@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">
            <a href="{{ route('admin.faqs.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                Support FAQs
            </a>
            <span>/</span>
            <span class="text-slate-900 dark:text-white">Create New FAQ</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Add Frequently Asked Question
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Publish a new question and answer to the user dashboard support helpdesk.
        </p>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-6">
            @csrf

            <!-- Question Input -->
            <div>
                <label for="question" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Question <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       id="question"
                       name="question"
                       value="{{ old('question') }}"
                       placeholder="e.g., How long does manual IPE clearing take?"
                       required
                       maxlength="255"
                       class="w-full px-4 py-3 rounded-xl text-sm font-medium border @error('question') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 dark:border-slate-700 @enderror bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                @error('question')
                    <p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Category Input with Datalist -->
                <div>
                    <label for="category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Category
                    </label>
                    <input type="text"
                           id="category"
                           name="category"
                           value="{{ old('category', 'General') }}"
                           list="category-suggestions"
                           placeholder="e.g., Verification Services"
                           maxlength="100"
                           class="w-full px-4 py-3 rounded-xl text-sm font-medium border @error('category') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 dark:border-slate-700 @enderror bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <datalist id="category-suggestions">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                        <option value="General"></option>
                        <option value="Verification Services"></option>
                        <option value="Wallet & Billing"></option>
                        <option value="Slips & Results"></option>
                        <option value="Technical Support"></option>
                    </datalist>
                    @error('category')
                        <p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Display Order / Priority
                    </label>
                    <input type="number"
                           id="sort_order"
                           name="sort_order"
                           value="{{ old('sort_order', 0) }}"
                           min="0"
                           class="w-full px-4 py-3 rounded-xl text-sm font-medium border @error('sort_order') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 dark:border-slate-700 @enderror bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <p class="mt-1 text-[11px] text-slate-400">Lower numbers appear first (e.g., 0, 1, 2...).</p>
                    @error('sort_order')
                        <p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Answer Textarea -->
            <div>
                <label for="answer" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Detailed Answer <span class="text-rose-500">*</span>
                </label>
                <textarea id="answer"
                          name="answer"
                          rows="6"
                          required
                          placeholder="Provide a clear, helpful, and concise answer to this question..."
                          class="w-full px-4 py-3 rounded-xl text-sm font-medium border @error('answer') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 dark:border-slate-700 @enderror bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 leading-relaxed">{{ old('answer') }}</textarea>
                @error('answer')
                    <p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Visibility Status Toggle -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <span class="block text-sm font-bold text-slate-900 dark:text-white">Active &amp; Visible to Users</span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">When disabled, this FAQ will be hidden from the customer support dashboard.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.faqs.index') }}"
                   class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-600/20 transition cursor-pointer">
                    Publish FAQ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
