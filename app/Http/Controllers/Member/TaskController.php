<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Repositories\Interface\TaskRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The member's focused task surface. Listing is forced to "mine" so an
 * employee only ever sees the tasks assigned to them.
 */
final readonly class TaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {}

    public function index(): View
    {
        return view('member.tasks.index', [
            'statuses' => $this->tasks->statusOptions(),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        // Force the "mine" scope regardless of client input.
        $request->merge(['mine' => true]);

        return response()->json($this->tasks->datatable($request));
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        abort_unless($task->assigned_to === $request->user()?->id, 403, 'Not your task.');

        $task = $this->tasks->updateStatus($task, $request->string('status')->toString());

        return response()->json([
            'message' => 'Status updated.',
            'status' => $task->status->value,
            'status_label' => $task->status->label(),
        ]);
    }
}
