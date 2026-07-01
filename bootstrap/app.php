<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureCentralUser;
use App\Http\Middleware\EnsureMemberPanelAccess;
use App\Http\Middleware\EnsureOrganizationApproved;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleImpersonation;
use App\Http\Middleware\InitializeTenancyFromAuthenticatedUser;
use App\Http\Middleware\InitializeTenancyFromCustomer;
use App\Http\Middleware\SetOrganizationLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Each file declares its OWN middleware group at the top.
            Route::group([], base_path('routes/auth.php'));
            Route::group([], base_path('routes/admin.php'));
            Route::group([], base_path('routes/tenant.php'));
            Route::group([], base_path('routes/member.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tenancy MUST be initialised before route-model binding runs, since
        // binding is an org-scoped query. Place org.init / customer.org.init
        // immediately after session auth and before SubstituteBindings.
        $middleware->appendToPriorityList(
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            InitializeTenancyFromAuthenticatedUser::class,
        );
        $middleware->appendToPriorityList(
            InitializeTenancyFromAuthenticatedUser::class,
            InitializeTenancyFromCustomer::class,
        );

        $middleware->alias([
            // Custom tenancy / panel guards.
            'active.user' => EnsureUserIsActive::class,
            'central.user' => EnsureCentralUser::class,
            'member.panel' => EnsureMemberPanelAccess::class,
            'impersonating' => HandleImpersonation::class,
            'org.init' => InitializeTenancyFromAuthenticatedUser::class,
            'set.organization.locale' => SetOrganizationLocale::class,
            'org.approved' => EnsureOrganizationApproved::class,
            'super_admin' => EnsureSuperAdmin::class,
            'customer.org.init' => InitializeTenancyFromCustomer::class,

            // spatie/laravel-permission.
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JSON error responses (incl. 422 validation) for the API and for any
        // AJAX request that expects JSON — the latter powers every tenant
        // axios form (inline .invalid-feedback / notyf). Plain browser form
        // posts still fall through to the standard redirect-with-errors flow.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
