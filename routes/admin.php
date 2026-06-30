<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super-admin routes — /admin/* (organization_id = null)
|--------------------------------------------------------------------------
| Group middleware: session auth + active + central(super) + impersonation banner.
*/

Route::middleware(['web', 'auth', 'active.user', 'central.user', 'super_admin', 'impersonating'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Organization onboarding & oversight.
        Route::controller(OrganizationController::class)->prefix('organizations')->name('organizations.')->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/listing', 'listing')->name('listing');         // DataTable JSON
            Route::get('/{organization}', 'show')->name('show');
            Route::post('/save', 'save')->name('save');                 // create + update
            Route::post('/{organization}/status', 'updateStatus')->name('status');
            Route::post('/{organization}/impersonate', 'impersonate')->name('impersonate');
        });

        // Lead triage.
        Route::controller(LeadController::class)->prefix('leads')->name('leads.')->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/listing', 'listing')->name('listing');
            Route::post('/{lead}/status', 'updateStatus')->name('status');
        });

        // Platform reports (surface = admin, derived from the route-name prefix).
        Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/{report}', 'show')->name('show');
            Route::get('/{report}/listing', 'listing')->name('listing');
            Route::get('/{report}/export', 'export')->name('export');
        });
    });
