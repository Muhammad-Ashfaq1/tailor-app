<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('tasks.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('tasks.update');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('tasks.delete');
    }
}
