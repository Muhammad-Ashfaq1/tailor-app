<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * super_admin — hard gate for /admin/*. The authenticated user must be a
 * super admin (no organization). Complements central.user with a role check.
 */
final readonly class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null || ! $user->isSuperAdmin(), 403, 'Super-admin access only.');

        return $next($request);
    }
}
