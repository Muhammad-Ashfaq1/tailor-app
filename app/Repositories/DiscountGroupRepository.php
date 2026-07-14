<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\DiscountGroup;
use App\Repositories\Concerns\HandlesSlugs;
use App\Repositories\Interface\DiscountGroupRepositoryInterface;
use App\Support\DataTables\DataTableBuilder;
use Illuminate\Http\Request;

final class DiscountGroupRepository extends BaseRepository implements DiscountGroupRepositoryInterface
{
    use HandlesSlugs;

    public function datatable(Request $request): array
    {
        $query = DiscountGroup::query();

        return DataTableBuilder::for($query, $request)
            ->searchable(['name', 'slug'])
            ->orderable(['id', 'name', 'type', 'value', 'created_at'])
            ->map(fn (DiscountGroup $group): array => $this->transform($group))
            ->toArray();
    }

    public function find(int $id): ?DiscountGroup
    {
        return DiscountGroup::query()->find($id);
    }

    public function store(array $data, ?DiscountGroup $group = null): DiscountGroup
    {
        $creating = $group === null;

        if ($creating) {
            $group = new DiscountGroup;
        }

        $slug = $this->generateUniqueSlug(
            DiscountGroup::class,
            $data['name'],
            $creating ? null : $group->id,
        );

        $group->fill([
            'name'      => trim($data['name']),
            'slug'      => $slug,
            'type'      => $data['type'],
            'value'     => $data['value'],
            'min_limit' => ($data['min_limit'] ?? null) !== null && $data['min_limit'] !== ''
                ? $data['min_limit']
                : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $group->save();

        return $group->refresh();
    }

    public function delete(DiscountGroup $group): void
    {
        $group->delete();
    }

    private function transform(DiscountGroup $group): array
    {
        return [
            'id'         => $group->id,
            'name'       => $group->name,
            'slug'       => $group->slug,
            'type'       => $group->type,
            'type_label' => ucfirst($group->type),
            'value'      => (string) $group->value,
            'value_label' => $group->type === 'percentage'
                ? rtrim(rtrim(number_format((float) $group->value, 2, '.', ''), '0'), '.') . '%'
                : '$' . number_format((float) $group->value, 2),
            'min_limit'       => $group->min_limit !== null ? (string) $group->min_limit : null,
            'min_limit_label' => $group->type === 'fixed' && $group->min_limit !== null
                ? '$' . number_format((float) $group->min_limit, 2)
                : '—',
            'is_active'         => $group->is_active,
            'status_label'      => $group->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $group->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $group->created_at?->format('d M Y'),
        ];
    }
}
