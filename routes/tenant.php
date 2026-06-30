<?php

declare(strict_types=1);

use App\Http\Controllers\ReportController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\MemberController;
use App\Http\Controllers\Tenant\RoleController;
use App\Http\Controllers\Tenant\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant admin / manager routes — /tenant/*
|--------------------------------------------------------------------------
| Group middleware: auth + active + org.init (tenancy) + org.approved + banner.
| Per-route permission middleware is added on top (OR-form where relevant).
*/

Route::middleware(['web', 'auth', 'active.user', 'org.init', 'org.approved', 'impersonating'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function (): void {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Members (tenant users) management + impersonation.
        Route::controller(MemberController::class)->prefix('members')->name('members.')->group(function (): void {
            Route::get('/', 'index')->middleware('permission:members.view')->name('index');
            Route::get('/listing', 'listing')->middleware('permission:members.view')->name('listing');
            Route::post('/save', 'save')->middleware('permission:members.create|members.update')->name('save');
            Route::post('/{user}/impersonate', 'impersonate')->middleware('permission:members.impersonate')->name('impersonate');
        });

        // Roles & permissions UI.
        Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function (): void {
            Route::get('/', 'index')->middleware('permission:roles.view')->name('index');
            Route::get('/listing', 'listing')->middleware('permission:roles.view')->name('listing');
            Route::post('/save', 'save')->middleware('permission:roles.manage')->name('save');
            Route::delete('/{role}', 'destroy')->middleware('permission:roles.manage')->name('destroy');
        });

        // Settings (sections gated by settings.manage).
        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->middleware('permission:settings.manage')->group(function (): void {
            Route::get('/{section?}', 'index')->name('index');
            Route::post('/{section}', 'save')->name('save');
        });

        // Tenant analytics reports.
        Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/{report}', 'show')->name('show');
            Route::get('/{report}/listing', 'listing')->name('listing');
            Route::get('/{report}/export', 'export')->name('export');
        });
    });
