<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerCreditType;
use App\Enums\CustomerType;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\FiltersByDateRange;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * A shop customer. Doubles as the stateless identity for the future /api/v1/*
 * customer surface (Sanctum). Org-scoped: once tenancy is initialised, queries
 * auto-scope to the current organization via the BelongsToOrganization scope.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $address
 * @property CustomerType $type
 * @property CustomerCreditType $credit_type
 * @property string $credit_value
 * @property string|null $notes
 * @property string|null $email
 * @property bool $is_active
 */
class Customer extends Authenticatable
{
    use BelongsToOrganization;
    use FiltersByDateRange;

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;
    use HasApiTokens;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'type',
        'credit_type',
        'credit_value',
        'notes',
        'email',
        'password',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'type' => CustomerType::class,
            'credit_type' => CustomerCreditType::class,
            'credit_value' => 'decimal:2',
        ];
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
