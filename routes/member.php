<?php

declare(strict_types=1);

use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member / employee routes — /member/*
|--------------------------------------------------------------------------
| Group middleware: auth + active + org.init + org.approved + member.panel + banner.
*/

Route::middleware(['web', 'auth', 'active.user', 'org.init', 'org.approved', 'member.panel', 'impersonating'])
    ->prefix('member')
    ->name('member.')
    ->group(function (): void {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Member-surface reports.
        Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/{report}', 'show')->name('show');
            Route::get('/{report}/listing', 'listing')->name('listing');
            Route::get('/{report}/export', 'export')->name('export');
        });
    });
