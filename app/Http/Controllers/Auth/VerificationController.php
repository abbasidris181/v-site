<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function showEmailNotice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isEmailVerified()) {
            return $user->isPhoneVerified()
                ? redirect()->route('dashboard')
                : redirect()->route('verification.phone.notice');
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email as verified.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isEmailVerified()) {
            return redirect()->route('verification.phone.notice');
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return redirect()->route('verification.phone.notice')
            ->with('status', 'Email verified successfully! Now complete your phone number verification.');
    }

    /**
     * Display the phone verification prompt.
     */
    public function showPhoneNotice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Must complete email verification first
        if (! $user->isEmailVerified()) {
            return redirect()->route('verification.email.notice');
        }

        if ($user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-phone');
    }

    /**
     * Mark the authenticated user's phone number as verified.
     */
    public function verifyPhone(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isEmailVerified()) {
            return redirect()->route('verification.email.notice');
        }

        if ($user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        $user->forceFill([
            'phone_verified_at' => now(),
        ])->save();

        return redirect()->route('dashboard')
            ->with('status', 'Phone number verified successfully! Your account is now fully active.');
    }
}
