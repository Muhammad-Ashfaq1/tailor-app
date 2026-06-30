<?php

declare(strict_types=1);

namespace App\Repositories\Interface;

use App\Models\Project;
use Illuminate\Http\Request;

interface ProjectRepositoryInterface
{
    /** Server-side DataTables payload for the projects listing. */
    public function datatable(Request $request): array;

    /** Create (no id) or update (with id) a project; returns the saved model. */
    public function save(array $data, ?int $id = null): Project;

    public function find(int $id): ?Project;

    public function delete(Project $project): void;

    /** @return array<int, array{value:string,label:string}> */
    public function statusOptions(): array;
}
