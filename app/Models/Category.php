<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'is_active',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('name');
    }

    /**
     * IDs of this category and all of its subcategories.
     *
     * @return list<int>
     */
    public function descendantAndSelfIds(): array
    {
        $ids = [$this->id];
        $parentIds = [$this->id];

        while ($parentIds !== []) {
            $parentIds = self::query()->whereIn('parent_id', $parentIds)->pluck('id')->all();
            $parentIds = array_values(array_diff($parentIds, $ids));
            $ids = array_merge($ids, $parentIds);
        }

        return $ids;
    }

    /**
     * Path from the root category to this one (for breadcrumbs).
     *
     * @return list<self>
     */
    public function ancestorsAndSelf(): array
    {
        $path = [$this];
        $current = $this;

        while ($current->parent_id && ($current = $current->parent)) {
            array_unshift($path, $current);
        }

        return $path;
    }
}
