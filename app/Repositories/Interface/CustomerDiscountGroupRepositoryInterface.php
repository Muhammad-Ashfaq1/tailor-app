<?php

declare(strict_types=1);

namespace App\Repositories\Interface;

use App\Models\CustomerDiscountGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface CustomerDiscountGroupRepositoryInterface
{
    /** Server-side DataTables payload for the customer discount groups listing. */
    public function datatable(Request $request): array;

    /** Resolve a single org-scoped customer discount group or null. */
    public function find(int $id): ?CustomerDiscountGroup;

    /** Create (id null) or update (id present) a discount group; returns the saved model. */
    public function save(array $data, ?int $id = null): CustomerDiscountGroup;

    /** Soft-delete an org-scoped customer discount group. */
    public function delete(int $id): void;

    /** Retrieve all active discount groups for select dropdowns. */
    public function allActive(): Collection;
}
