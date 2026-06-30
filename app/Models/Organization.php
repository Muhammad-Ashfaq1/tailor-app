<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationStatus;
use App\Support\Settings\OrganizationSettings;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Database\Concerns\HasInternalKeys;
use Stancl\Tenancy\Database\Concerns\TenantRun;

/**
 * The tenant model. Implements the stancl/tenancy Tenant contract so it can be
 * passed to tenancy()->initialize(); used for IDENTIFICATION ONLY (single DB).
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property OrganizationStatus $status
 * @property array<string, mixed>|null $settings
 */
class Organization extends Model implements Tenant
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;
    use HasInternalKeys; // getInternal()/setInternal()
    use SoftDeletes;
    use TenantRun;       // run(callable)

    protected $fillable = [
        'name',
        'slug',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'settings' => 'array',
        ];
    }

    /* ----------------------------------------------------------------- */
    /* Stancl\Tenancy\Contracts\Tenant                                    */
    /* ----------------------------------------------------------------- */

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): int|string
    {
        return $this->getKey();
    }

    /* ----------------------------------------------------------------- */
    /* Relations                                                          */
    /* ----------------------------------------------------------------- */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /* ----------------------------------------------------------------- */
    /* Status helpers                                                     */
    /* ----------------------------------------------------------------- */

    public function isApproved(): bool
    {
        return $this->status === OrganizationStatus::Approved;
    }

    /* ----------------------------------------------------------------- */
    /* Settings (see App\Support\Settings\OrganizationSettings)           */
    /* ----------------------------------------------------------------- */

    /** Defaults deep-merged over the stored settings JSON. */
    public function mergedSettings(): array
    {
        return OrganizationSettings::merged($this->settings ?? []);
    }

    /** Dot-path read with default: $org->setting('regional.currency', 'USD'). */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->mergedSettings(), $key, $default);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
