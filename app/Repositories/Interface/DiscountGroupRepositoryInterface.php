<?php

declare(strict_types=1);

namespace App\Repositories\Interface;

use App\Models\DiscountGroup;
use Illuminate\Http\Request;

interface DiscountGroupRepositoryInterface
{
    /** Server-side DataTables payload for the listing. */
    public function datatable(Request $request): array;

    /** Resolve a single org-scoped discount group or null. */
    public function find(int $id): ?DiscountGroup;

    /** Create (id null) or update (id present); returns the saved model. */
    public function store(array $data, ?DiscountGroup $group = null): DiscountGroup;

    /** Soft-delete a discount group. */
    public function delete(DiscountGroup $group): void;
}
