<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Stateless customer/member API — /api/v1/*  (Sanctum Bearer tokens)
|--------------------------------------------------------------------------
| Separate guard/provider (customer). Login carries the org slug; authenticated
| requests initialise tenancy from the token holder via customer.org.init.
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // Org-scoped login — body carries { organization, email, password }.
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')->name('login');

    Route::middleware(['auth:sanctum', 'customer.org.init'])->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    });
});
