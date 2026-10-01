<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of system announcements.
     */
    public function index(Request $request): View
    {
        $this->ensureAuthorized('announcements.manage');

        $query = Announcement::latest();

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $announcements = $query->paginate(10)->withQueryString();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        $this->ensureAuthorized('announcements.manage');

        return view('admin.announcements.create');
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAuthorized('announcements.manage');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'type' => ['required', 'string', 'in:info,warning,success'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Announcement published successfully.');
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(Announcement $announcement): View
    {
        $this->ensureAuthorized('announcements.manage');

        return view('admin.announcements.edit', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureAuthorized('announcements.manage');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'type' => ['required', 'string', 'in:info,warning,success'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Announcement updated successfully.');
    }

    /**
     * Toggle the active visibility status of an announcement.
     */
    public function toggleStatus(Announcement $announcement): RedirectResponse
    {
        $this->ensureAuthorized('announcements.manage');

        $announcement->update([
            'is_active' => ! $announcement->is_active,
        ]);

        $state = $announcement->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Announcement has been {$state}.");
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->ensureAuthorized('announcements.manage');

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Announcement deleted successfully.');
    }

    /**
     * Guard helper ensuring the acting user has permission or is Super Admin.
     */
    protected function ensureAuthorized(string $permission): void
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin') && ! $user->hasPermission($permission)) {
            abort(403, 'Unauthorized access to announcement governance.');
        }
    }
}
