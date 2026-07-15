<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountGroup extends Model
{
    use BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'value',
        'min_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'     => 'decimal:2',
            'min_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'percentage' => 'Percentage',
            'fixed'      => 'Fixed',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('name', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%");
        });
    }
}
