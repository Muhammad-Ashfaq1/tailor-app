<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * org.approved — block any tenant user whose organization is not Approved
 * (pending / suspended / rejected). Central super-admins (no org) pass through.
 */
final readonly class EnsureOrganizationApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->organization_id !== null) {
            $organization = $user->organization;

            if ($organization === null || ! $organization->isApproved()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your organization is not active. Please contact support.',
                ]);
            }
        }

        return $next($request);
    }
}
