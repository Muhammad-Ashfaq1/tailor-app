<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Shared repository behaviour: stamp created_by / updated_by from the current
 * authenticated user. Concrete repositories own their search scopes, slug
 * generation and DataTables mapping.
 */
abstract class BaseRepository
{
    /**
     * Merge audit columns into an attribute array for a write.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function withAudit(array $attributes, bool $creating): array
    {
        $userId = auth()->id();

        if ($creating) {
            $attributes['created_by'] = $userId;
        }
        $attributes['updated_by'] = $userId;

        return $attributes;
    }
}
