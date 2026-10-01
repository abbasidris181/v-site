<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of registered portal users with search & filters.
     */
    public function index(Request $request): View
    {
        $this->ensureAuthorized('users.view');

        $query = User::with(['roles', 'wallet'])->latest();

        // Search by keyword (Name, Email, Phone, Business)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('business', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($roleSlug = $request->input('role')) {
            if ($roleSlug === 'admin' || $roleSlug === 'administrators') {
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('slug', ['admin', 'super_admin']);
                });
            } else {
                $query->whereHas('roles', function ($q) use ($roleSlug) {
                    $q->where('slug', $roleSlug);
                });
            }
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        // Aggregation metrics for dashboard cards
        $totalUsers = User::count();
        $activeCount = User::where('is_active', true)->count();
        $suspendedCount = User::where('is_active', false)->count();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'totalUsers',
            'activeCount',
            'suspendedCount'
        ));
    }

    /**
     * Display a detailed profile for the given user.
     */
    public function show(User $user): View
    {
        $this->ensureAuthorized('users.view');

        $user->load(['roles', 'wallet']);
        $recentTransactions = $user->walletTransactions()->latest()->take(8)->get();
        $recentRequests = ServiceRequest::where('user_id', $user->id)
            ->with('service')
            ->latest()
            ->take(8)
            ->get();
        $roles = Role::all();

        return view('admin.users.show', compact('user', 'recentTransactions', 'recentRequests', 'roles'));
    }

    /**
     * Toggle the active status of a user (Suspend / Activate).
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->ensureAuthorized('users.view');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own administrative account.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $actionText = $user->is_active ? 'activated' : 'suspended';

        return back()->with('status', "User account for {$user->full_name} has been {$actionText} successfully.");
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(User $user, Request $request): RedirectResponse
    {
        $this->ensureAuthorized('users.reset_password');

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            $newPassword = $request->input('password');
        } else {
            // Generate a secure temporary password if none supplied
            $newPassword = 'Temp_' . Str::random(8) . '!';
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('status', "Password for {$user->full_name} has been reset successfully. New Temporary Password: {$newPassword}");
    }

    /**
     * Reassign a user's platform role (Super Admin exclusive).
     */
    public function changeRole(User $user, Request $request): RedirectResponse
    {
        if (! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Only Super Administrators can modify platform user roles.');
        }

        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($request->input('role_id'));
        $user->roles()->sync([$role->id]);

        return back()->with('status', "User role for {$user->full_name} has been updated to {$role->name}.");
    }

    /**
     * Guard helper ensuring the acting user has permission or is Super Admin.
     */
    protected function ensureAuthorized(string $permission): void
    {
        if (! auth()->user()->hasRole('super_admin') && ! auth()->user()->hasPermission($permission)) {
            abort(403, 'Unauthorized access to user governance.');
        }
    }
}
