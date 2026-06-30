<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Model-level authorization for projects. The org scope already guarantees
 * $project belongs to the acting user's organization, so these checks are
 * purely about the ability. Super admins are short-circuited by Gate::before.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('projects.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('projects.update');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('projects.delete');
    }
}
