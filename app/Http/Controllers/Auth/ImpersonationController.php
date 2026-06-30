<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Impersonation\Impersonator;
use Illuminate\Http\RedirectResponse;

/**
 * The single shared "stop impersonating" handler (both super->tenant and
 * tenant->member flows return here). Start lives in the respective admin /
 * tenant controllers.
 */
final readonly class ImpersonationController extends Controller
{
    public function __construct(
        private Impersonator $impersonator,
    ) {}

    public function stop(): RedirectResponse
    {
        $restored = $this->impersonator->stop();

        if (! $restored) {
            return redirect()->route('login');
        }

        return redirect()
            ->route(auth()->user()->defaultDashboardRouteName())
            ->with('status', 'Stopped impersonating.');
    }
}
