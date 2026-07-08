<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CustomerDiscountGroup;
use App\Repositories\Interface\CustomerDiscountGroupRepositoryInterface;
use App\Support\DataTables\DataTableBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

final class CustomerDiscountGroupRepository extends BaseRepository implements CustomerDiscountGroupRepositoryInterface
{
    public function datatable(Request $request): array
    {
        $query = CustomerDiscountGroup::query();

        return DataTableBuilder::for($query, $request)
            ->searchable(['name', 'description'])
            ->orderable(['id', 'name', 'discount_percentage', 'created_at'])
            ->map(fn (CustomerDiscountGroup $group): array => [
                'id' => $group->id,
                'name' => $group->name,
                'discount_percentage' => $group->discount_percentage,
                'description' => $group->description,
                'is_active' => $group->is_active,
                'created_at' => $group->created_at?->toDateString(),
            ])
            ->toArray();
    }

    public function find(int $id): ?CustomerDiscountGroup
    {
        return CustomerDiscountGroup::query()->find($id);
    }

    public function save(array $data, ?int $id = null): CustomerDiscountGroup
    {
        $creating = $id === null;

        $group = $creating ? new CustomerDiscountGroup : $this->find($id);
        if ($group === null) {
            abort(404);
        }

        $group->fill($this->withAudit($data, $creating))->save();

        return $group->refresh();
    }

    public function delete(int $id): void
    {
        $group = $this->find($id);
        if ($group === null) {
            abort(404);
        }

        $group->delete();
    }

    public function allActive(): Collection
    {
        return CustomerDiscountGroup::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
