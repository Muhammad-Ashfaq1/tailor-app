<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * central.user — only users WITHOUT an organization (the super-admin tier) may
 * pass. Guards the /admin/* surface against tenant users.
 */
final readonly class EnsureCentralUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null || $user->organization_id !== null, 403, 'Central access only.');

        return $next($request);
    }
}
