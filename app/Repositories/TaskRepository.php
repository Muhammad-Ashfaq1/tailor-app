<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interface\TaskRepositoryInterface;
use App\Support\DataTables\DataTableBuilder;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\Request;

final class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function datatable(Request $request): array
    {
        $query = Task::query()->with(['project:id,name', 'assignee:id,name']);

        // Member surface: only the current user's own tasks.
        if ($request->boolean('mine')) {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', (int) $request->input('project_id'));
        }

        return DataTableBuilder::for($query, $request)
            ->searchable(['title'])
            ->orderable(['id', 'title', 'status', 'due_date', 'created_at'])
            ->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->project?->name,
                'project_id' => $task->project_id,
                'assignee' => $task->assignee?->name,
                'assigned_to' => $task->assigned_to,
                'status' => $task->status->value,
                'status_label' => $task->status->label(),
                'status_color' => $task->status->color(),
                'due_date' => $task->due_date?->toDateString(),
            ])
            ->toArray();
    }

    public function save(array $data, ?int $id = null): Task
    {
        $creating = $id === null;
        $task = $creating ? new Task : $this->find($id);

        if ($task === null) {
            abort(404);
        }

        $task->fill($this->withAudit($data, $creating));
        $task->save();

        return $task->refresh();
    }

    public function find(int $id): ?Task
    {
        return Task::query()->find($id);
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $task->fill($this->withAudit(['status' => $status], false));
        $task->save();

        return $task->refresh();
    }

    public function projectOptions(): array
    {
        return Project::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Project $p): array => ['value' => $p->id, 'label' => $p->name])
            ->all();
    }

    public function assigneeOptions(): array
    {
        $orgId = OrganizationContext::id();

        return User::query()
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u): array => ['value' => $u->id, 'label' => $u->name])
            ->all();
    }

    public function statusOptions(): array
    {
        return TaskStatus::options();
    }
}
