<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A customer discount group (e.g. Silver, Gold, VIP). Scoped per organization.
 * Customers can be associated with a discount group to get automatic discounts.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property float $discount_percentage
 * @property string|null $description
 * @property bool $is_active
 */
class CustomerDiscountGroup extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'discount_percentage',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'discount_group_id');
    }
}
