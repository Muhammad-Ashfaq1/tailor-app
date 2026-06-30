<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * The member's personal dashboard landing.
 */
final readonly class DashboardController extends Controller
{
    public function index(): View
    {
        return view('member.dashboard', [
            'user' => auth()->user(),
        ]);
    }
}
