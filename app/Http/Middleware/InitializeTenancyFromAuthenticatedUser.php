<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * org.init — initialise tenancy from the authenticated web user BEFORE any
 * org-scoped query runs. Central / super-admin users (organization_id === null)
 * leave tenancy uninitialised, so the global scope spans all organizations.
 */
final readonly class InitializeTenancyFromAuthenticatedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->organization_id !== null && $user->organization !== null) {
            tenancy()->initialize($user->organization);
        }

        return $next($request);
    }
}
