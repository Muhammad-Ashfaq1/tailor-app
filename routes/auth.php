<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ImpersonationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication & onboarding routes
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function (): void {

    // ---- Guests --------------------------------------------------------
    Route::middleware('guest')->group(function (): void {
        Route::get('/register', [RegisterController::class, 'show'])->name('register');
        Route::post('/register', [RegisterController::class, 'store'])
            ->middleware('throttle:5,1')->name('register.store');

        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')->name('login.store');

        // Password reset.
        Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class, 'email'])
            ->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
        Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    });

    // ---- Authenticated -------------------------------------------------
    Route::middleware('auth')->group(function (): void {
        // Email verification.
        Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware('signed')->name('verification.verify');
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1')->name('verification.send');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Stop impersonating — single shared handler (super->tenant, tenant->member).
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])
            ->middleware('impersonating')->name('impersonate.stop');
    });
});
