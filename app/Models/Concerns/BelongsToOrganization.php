<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to every org-scoped domain model. Provides:
 *   1. A global scope WHERE organization_id = <current org> (skipped when null).
 *   2. Auto-fill of organization_id on creating.
 *   3. Lock of organization_id on updating (original silently restored).
 *   4. Org-scoped route-model binding (no cross-tenant id guessing).
 *   5. Escape hatches: withoutOrganizationScope(), forOrganization($id).
 *
 * @property int|null $organization_id
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope);

        static::creating(function ($model): void {
            if ($model->getAttribute($model->getOrganizationKeyName()) === null) {
                $orgId = OrganizationContext::id();
                if ($orgId !== null) {
                    $model->setAttribute($model->getOrganizationKeyName(), $orgId);
                }
            }
        });

        static::updating(function ($model): void {
            // organization_id is immutable. Silently restore the original value.
            $key = $model->getOrganizationKeyName();
            if ($model->isDirty($key)) {
                $model->setAttribute($key, $model->getOriginal($key));
            }
        });
    }

    public function getOrganizationKeyName(): string
    {
        return 'organization_id';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, $this->getOrganizationKeyName());
    }

    /** Bypass the org scope only (SoftDeletes etc. stay intact). */
    public static function withoutOrganizationScope(): Builder
    {
        return static::query()->withoutGlobalScope(OrganizationScope::class);
    }

    /** Query a specific organization regardless of the ambient context. */
    public static function forOrganization(int $organizationId): Builder
    {
        return static::withoutOrganizationScope()
            ->where((new static)->getOrganizationKeyName(), $organizationId);
    }

    /**
     * Org-scope route-model binding so /tenant/{resource}/{model} can never
     * resolve a record belonging to another organization.
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $orgId = OrganizationContext::id();

        if ($orgId !== null) {
            $query->where($this->getOrganizationKeyName(), $orgId);
        }

        return $query->where($field ?? $this->getRouteKeyName(), $value);
    }
}
