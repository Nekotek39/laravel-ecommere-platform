<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'coupon_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Abandoned guest carts removed by `php artisan model:prune`.
     */
    public function prunable(): Builder
    {
        return static::query()
            ->whereNull('user_id')
            ->where('updated_at', '<', now()->subDays((int) config('shop.guest_cart_lifetime_days', 30)));
    }
}
