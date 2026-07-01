<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Support\DataTables\DataTableBuilder;
use Illuminate\Http\Request;

final class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function datatable(Request $request): array
    {
        // Org scope is automatic via BelongsToOrganization.
        $query = Customer::query();

        return DataTableBuilder::for($query, $request)
            ->searchable(['name', 'phone', 'email'])
            ->orderable(['id', 'name', 'phone', 'type', 'created_at'])
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'type' => $customer->type->value,
                'type_label' => $customer->type->label(),
                'type_color' => $customer->type->color(),
                'credit_type' => $customer->credit_type->value,
                'credit_type_label' => $customer->credit_type->label(),
                'credit_value' => $customer->credit_value,
                'is_active' => $customer->is_active,
                'created_at' => $customer->created_at?->toDateString(),
            ])
            ->toArray();
    }

    public function find(int $id): ?Customer
    {
        return Customer::query()->find($id);
    }

    public function save(array $data, ?int $id = null): Customer
    {
        $creating = $id === null;

        $customer = $creating ? new Customer : $this->find($id);
        if ($customer === null) {
            abort(404);
        }

        // A blank password on update leaves the existing one untouched.
        if (($data['password'] ?? null) === null || $data['password'] === '') {
            unset($data['password']);
        }

        $customer->fill($this->withAudit($data, $creating))->save();

        return $customer->refresh();
    }

    public function delete(int $id): void
    {
        $customer = $this->find($id);
        if ($customer === null) {
            abort(404);
        }

        $customer->delete();
    }
}
