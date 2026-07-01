<?php

declare(strict_types=1);

namespace App\Repositories\Interface;

use App\Models\Customer;
use Illuminate\Http\Request;

interface CustomerRepositoryInterface
{
    /** Server-side DataTables payload for the customer listing. */
    public function datatable(Request $request): array;

    /** Resolve a single org-scoped customer or null. */
    public function find(int $id): ?Customer;

    /** Create (id null) or update (id present) a customer; returns the saved model. */
    public function save(array $data, ?int $id = null): Customer;

    /** Soft-delete an org-scoped customer. */
    public function delete(int $id): void;
}
