<?php

declare(strict_types=1);

use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\TaskController;
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

        // The member's focused task surface.
        Route::controller(TaskController::class)->prefix('tasks')->name('tasks.')->group(function (): void {
            Route::get('/', 'index')->middleware('permission:tasks.view')->name('index');
            Route::get('/listing', 'listing')->middleware('permission:tasks.view')->name('listing');
            Route::post('/{task}/status', 'updateStatus')->middleware('permission:tasks.update')->name('status');
        });

        // Member-surface reports.
        Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/{report}', 'show')->name('show');
            Route::get('/{report}/listing', 'listing')->name('listing');
            Route::get('/{report}/export', 'export')->name('export');
        });
    });
