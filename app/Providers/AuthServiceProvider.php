<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private array $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Super-admin override: short-circuit every gate/permission check.
        Gate::before(static function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
