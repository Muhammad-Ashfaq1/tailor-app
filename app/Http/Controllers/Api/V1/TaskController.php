<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Org-scoped task reads/writes for the /api/v1/* customer surface. Tenancy is
 * initialised by customer.org.init, so queries and creates auto-scope to the org.
 */
final readonly class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tasks = Task::query()
            ->when(
                $request->filled('project_id'),
                fn ($query) => $query->where('project_id', $request->integer('project_id')),
            )
            ->latest()
            ->paginate(15);

        return TaskResource::collection($tasks);
    }

    public function store(Request $request): TaskResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_id' => [
                'required',
                // exists is scoped to the current org via the global scope.
                Rule::exists((new Project)->getTable(), 'id')
                    ->where('organization_id', $request->user()->organization_id),
            ],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
        ]);

        // organization_id auto-fills via BelongsToOrganization on creating.
        $task = Task::create([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'status' => $validated['status'] ?? TaskStatus::Todo->value,
        ]);

        return new TaskResource($task);
    }

    public function updateStatus(Request $request, Task $task): TaskResource
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ]);

        $task->update(['status' => $validated['status']]);

        return new TaskResource($task);
    }
}
