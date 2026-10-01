<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Display a listing of Frequently Asked Questions.
     */
    public function index(Request $request): View
    {
        $this->ensureAuthorized('settings.manage');

        $query = Faq::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $faqs = $query->orderBy('sort_order')->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = Faq::distinct()->whereNotNull('category')->pluck('category')->sort()->values();

        return view('admin.faqs.index', compact('faqs', 'categories'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create(): View
    {
        $this->ensureAuthorized('settings.manage');

        $categories = Faq::distinct()->whereNotNull('category')->pluck('category')->sort()->values();

        return view('admin.faqs.create', compact('categories'));
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['category'] = ! empty($validated['category']) ? trim($validated['category']) : 'General';
        $validated['sort_order'] = isset($validated['sort_order']) ? (int) $validated['sort_order'] : 0;
        $validated['is_active'] = $request->boolean('is_active');

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')
            ->with('status', 'FAQ created successfully.');
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq): View
    {
        $this->ensureAuthorized('settings.manage');

        $categories = Faq::distinct()->whereNotNull('category')->pluck('category')->sort()->values();

        return view('admin.faqs.edit', compact('faq', 'categories'));
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['category'] = ! empty($validated['category']) ? trim($validated['category']) : 'General';
        $validated['sort_order'] = isset($validated['sort_order']) ? (int) $validated['sort_order'] : 0;
        $validated['is_active'] = $request->boolean('is_active');

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')
            ->with('status', 'FAQ updated successfully.');
    }

    /**
     * Toggle the active status of the specified FAQ.
     */
    public function toggleStatus(Faq $faq): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $faq->update([
            'is_active' => ! $faq->is_active,
        ]);

        $state = $faq->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "FAQ has been {$state}.");
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('status', 'FAQ deleted successfully.');
    }

    /**
     * Guard helper ensuring the acting user has permission or is Super Admin.
     */
    protected function ensureAuthorized(string $permission): void
    {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('super_admin') && ! $user->hasPermission($permission))) {
            abort(403, 'Unauthorized access to FAQ governance.');
        }
    }
}
