<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTaskRequest;
use App\Models\Task;
use App\Repositories\Interface\TaskRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class TaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {}

    public function index(): View
    {
        return view('tenant.tasks.index', [
            'statuses' => $this->tasks->statusOptions(),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        return response()->json($this->tasks->datatable($request));
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status->value,
            'project_id' => $task->project_id,
            'assigned_to' => $task->assigned_to,
            'due_date' => $task->due_date?->toDateString(),
        ]);
    }

    public function save(SaveTaskRequest $request): JsonResponse
    {
        $task = $this->tasks->save(
            $request->payload(),
            $request->filled('id') ? (int) $request->input('id') : null,
        );

        return response()->json([
            'message' => 'Task saved.',
            'task' => ['id' => $task->id, 'title' => $task->title],
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $this->tasks->delete($task);

        return response()->json(['message' => 'Task deleted.']);
    }

    public function projectOptions(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('tasks.view'), 403);

        return response()->json($this->tasks->projectOptions());
    }

    public function assigneeOptions(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('tasks.view'), 403);

        return response()->json($this->tasks->assigneeOptions());
    }
}
