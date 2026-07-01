<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Session auth for the web panels: show the login form, log in (with the
 * layered credential/status gates) and log out. Password reset lives in
 * PasswordResetController; registration/verification/impersonation each keep
 * their own controller.
 */
final readonly class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Layered login: credentials -> email verified -> org status -> active flag.
     * Each gate has its own message via resolveLoginBlockMessage(). Throttle is
     * applied at the route (5,1). On success the user lands on the dashboard for
     * their role tier (super-admin / tenant / member).
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        // 1. Credentials.
        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // 2-4. Email verified -> org status -> active flag.
        $message = $this->resolveLoginBlockMessage($user);
        if ($message !== null) {
            throw ValidationException::withMessages(['email' => $message]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route($user->defaultDashboardRouteName()))
            ->with('status', "Welcome back, {$user->name}!");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** Returns the first applicable block reason, or null if login may proceed. */
    private function resolveLoginBlockMessage(User $user): ?string
    {
        if ($user->getAttribute('email_verified_at') === null) {
            return 'Please verify your email address before signing in.';
        }

        $organization = $user->organization;
        if ($organization !== null && ! $organization->isApproved()) {
            return match ($organization->status->value) {
                'pending' => 'Your organization is awaiting approval.',
                'suspended' => 'Your organization has been suspended.',
                'rejected' => 'Your organization registration was not approved.',
                default => 'Your organization is not active.',
            };
        }

        if ($user->is_active === false) {
            return 'Your account has been deactivated.';
        }

        return null;
    }
}
