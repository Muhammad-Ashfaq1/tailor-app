<?php

declare(strict_types=1);

namespace App\Repositories\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Repository-side slug generation: derive a URL-safe slug from a source field
 * and guarantee it is unique WITHIN the current organization (the model's
 * global org scope keeps the uniqueness query tenant-local automatically).
 */
trait HandlesSlugs
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function generateUniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 1;

        while ($this->slugExists($modelClass, $slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function slugExists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        return $modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }
}
