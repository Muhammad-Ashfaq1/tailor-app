<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * member.panel — restricts /member/* to member-tier roles. Tenant admins and
 * managers use /tenant/*; the member panel is the focused operational surface.
 */
final readonly class EnsureMemberPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null || ! $user->isMemberTier(), 403, 'Member panel access only.');

        return $next($request);
    }
}
