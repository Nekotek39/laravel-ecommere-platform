<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Generates a unique slug from the "name" field when none was given.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function (self $model) {
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug($model->name);
            }
        });
    }

    public function generateUniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while ($this->slugExists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $query->withTrashed();
        }

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
