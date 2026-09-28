<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_at_price',
        'stock',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true)->latest();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    public function scopeWithRating(Builder $query): void
    {
        $query
            ->withAvg(['reviews as rating_avg' => fn (Builder $q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as rating_count' => fn (Builder $q) => $q->where('is_approved', true)]);
    }

    /**
     * Catalog filtering and sorting.
     *
     * Supported keys: search, category (model or slug), min_price, max_price
     * (in major currency units), in_stock, sort (newest, price_asc, price_desc, name, popular).
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function (Builder $q, Category|string $category) {
                if (is_string($category)) {
                    $category = Category::query()->where('slug', $category)->first();
                }

                $category
                    ? $q->whereIn('category_id', $category->descendantAndSelfIds())
                    : $q->whereRaw('1 = 0');
            })
            ->when(isset($filters['min_price']) && $filters['min_price'] !== '', fn (Builder $q) => $q->where('price', '>=', Money::toCents($filters['min_price'])))
            ->when(isset($filters['max_price']) && $filters['max_price'] !== '', fn (Builder $q) => $q->where('price', '<=', Money::toCents($filters['max_price'])))
            ->when($filters['in_stock'] ?? false, fn (Builder $q) => $q->inStock());

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            'popular' => $query->withSum('orderItems as sold_quantity', 'quantity')->orderByDesc('sold_quantity'),
            default => $query->latest(),
        };
    }

    /**
     * Active products with the relations needed on listings, filtered.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeCatalog(Builder $query, array $filters = []): void
    {
        $query->active()->with(['mainImage', 'category'])->withRating()->filter($filters);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function isPurchasable(): bool
    {
        return $this->is_active && ! $this->trashed() && $this->isInStock();
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    /**
     * Discount percentage relative to the compare-at price.
     */
    public function discountPercent(): ?int
    {
        if (! $this->isOnSale()) {
            return null;
        }

        return (int) round(100 - ($this->price / $this->compare_at_price * 100));
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => Money::format($this->price));
    }

    protected function formattedCompareAtPrice(): Attribute
    {
        return Attribute::get(fn () => $this->compare_at_price ? Money::format($this->compare_at_price) : null);
    }
}
