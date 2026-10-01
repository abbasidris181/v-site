<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact support.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Display the registration view.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request with all 8 specified input fields:
     * 1- firstname, 2- surname, 3- middlename(optional), 4- email,
     * 5- phonenumber, 6- password, 7- confirm password, 8- business.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:191', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'business' => ['required', 'string', 'max:191'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $createdUser = User::create([
                'first_name' => $validated['first_name'],
                'surname' => $validated['surname'],
                'middle_name' => $validated['middle_name'] ?? null,
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'business' => $validated['business'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // Assign default End User role
            $endUserRole = Role::firstOrCreate(
                ['slug' => 'end_user'],
                ['name' => 'End User', 'description' => 'Standard customer with access to services and personal wallet']
            );

            $createdUser->roles()->syncWithoutDetaching([$endUserRole->id]);

            // Initialize User's Wallet
            $createdUser->wallet()->create([
                'balance' => 0.00,
                'currency' => 'NGN',
            ]);

            return $createdUser;
        });

        Auth::login($user);

        return redirect()->route('verification.email.notice')
            ->with('status', 'Registration successful! Please verify your email address to get started.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
