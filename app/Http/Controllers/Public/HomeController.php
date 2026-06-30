<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Public marketing landing. Authenticated users are bounced to their
 * role-appropriate dashboard; guests see the marketing page.
 */
final readonly class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route(auth()->user()->defaultDashboardRouteName());
        }

        return view('public.home');
    }
}
