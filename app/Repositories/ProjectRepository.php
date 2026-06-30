<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Repositories\Concerns\HandlesSlugs;
use App\Repositories\Interface\ProjectRepositoryInterface;
use App\Support\DataTables\DataTableBuilder;
use Illuminate\Http\Request;

final class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    use HandlesSlugs;

    public function datatable(Request $request): array
    {
        // Org scope is applied automatically by the global scope.
        $query = Project::query()->withCount('tasks');

        return DataTableBuilder::for($query, $request)
            ->searchable(['name', 'slug'])
            ->orderable(['id', 'name', 'status', 'created_at'])
            ->map(fn (Project $project): array => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'status' => $project->status->value,
                'status_label' => $project->status->label(),
                'status_color' => $project->status->color(),
                'tasks_count' => $project->tasks_count,
                'created_at' => $project->created_at?->toDateString(),
            ])
            ->toArray();
    }

    public function save(array $data, ?int $id = null): Project
    {
        $creating = $id === null;
        $project = $creating ? new Project : $this->find($id);

        if ($project === null) {
            abort(404);
        }

        // Slug regenerated from name (unique per org) only when the name changes.
        if ($creating || ($data['name'] ?? null) !== $project->name) {
            $data['slug'] = $this->generateUniqueSlug(Project::class, $data['name'], $creating ? null : $project->id);
        }

        $project->fill($this->withAudit($data, $creating));
        $project->save();

        return $project->refresh();
    }

    public function find(int $id): ?Project
    {
        return Project::query()->find($id);
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function statusOptions(): array
    {
        return ProjectStatus::options();
    }
}
