<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * impersonating — when an impersonation is active (session('impersonator_id')
 * is set), expose a banner + stop-impersonate affordance to every panel view.
 * The actual stop handler lives in ImpersonationController::stop().
 */
final readonly class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        View::share('isImpersonating', $impersonatorId !== null);
        View::share(
            'impersonator',
            $impersonatorId !== null ? User::withoutGlobalScopes()->find($impersonatorId) : null,
        );

        return $next($request);
    }
}
