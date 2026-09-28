<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'shipping_method',
        'email',
        'phone',
        'shipping_address',
        'billing_address',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'coupon_code',
        'notes',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $attributes = [
        'status' => 'pending',
        'payment_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'shipping_method' => ShippingMethod::class,
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->number ??= static::generateNumber();
        });
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::query()->where('number', $number)->exists());

        return $number;
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeStatus(Builder $query, OrderStatus|string|null $status): void
    {
        $query->when($status, fn (Builder $q) => $q->where('status', $status));
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('payment_status', PaymentStatus::Paid);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status->isCancellableByCustomer() && ! $this->isPaid();
    }

    public function itemsCount(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
