<?php

declare(strict_types=1);

namespace App\Repositories\Interface;

use App\Models\Task;
use Illuminate\Http\Request;

interface TaskRepositoryInterface
{
    public function datatable(Request $request): array;

    public function save(array $data, ?int $id = null): Task;

    public function find(int $id): ?Task;

    public function delete(Task $task): void;

    public function updateStatus(Task $task, string $status): Task;

    /** @return array<int, array{value:int,label:string}> */
    public function projectOptions(): array;

    /** @return array<int, array{value:int,label:string}> */
    public function assigneeOptions(): array;

    /** @return array<int, array{value:string,label:string}> */
    public function statusOptions(): array;
}
