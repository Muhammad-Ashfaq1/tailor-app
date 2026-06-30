<?php

declare(strict_types=1);

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / central web routes (web middleware group)
|--------------------------------------------------------------------------
| The marketing landing page and the central, NON-org-scoped lead capture.
*/

Route::middleware('web')->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Request-a-Demo modal posts here. Throttled, writes to the central leads table.
    Route::post('/leads', [LeadController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('leads.store');
});
