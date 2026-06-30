<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Tenancy\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains every query to the current organization.
 * No-op when there is no organization context (central / super-admin).
 */
final class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $orgId = OrganizationContext::id();

        if ($orgId === null) {
            return;
        }

        /** @var \App\Models\Concerns\BelongsToOrganization $model */
        $builder->where(
            $model->getTable().'.'.$model->getOrganizationKeyName(),
            $orgId,
        );
    }
}
