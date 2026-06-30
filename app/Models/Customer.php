<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Stateless API identity for the /api/v1/* customer surface. Org-scoped exactly
 * like Project/Task: once tenancy is initialised, queries auto-scope to the
 * current organization via the BelongsToOrganization global scope.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string $email
 * @property bool $is_active
 */
class Customer extends Authenticatable
{
    use BelongsToOrganization;

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;
    use HasApiTokens;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'is_active',
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
        ];
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
