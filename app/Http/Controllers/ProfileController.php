<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile and account settings.
     */
    public function index(): View
    {
        $user = auth()->user();
        $recentTransactions = $user->walletTransactions()->latest()->take(5)->get();
        $recentRequests = ServiceRequest::where('user_id', $user->id)
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        return view('profile.index', compact('user', 'recentTransactions', 'recentRequests'));
    }

    /**
     * Update the user's account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password updated successfully.');
    }
}
