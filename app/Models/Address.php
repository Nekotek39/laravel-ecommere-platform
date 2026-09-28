<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    /**
     * Address fields copied into the order.
     */
    public const SNAPSHOT_FIELDS = [
        'first_name',
        'last_name',
        'company',
        'tax_id',
        'street',
        'city',
        'postal_code',
        'country',
        'phone',
    ];

    protected $fillable = [
        'user_id',
        'label',
        'first_name',
        'last_name',
        'company',
        'tax_id',
        'street',
        'city',
        'postal_code',
        'country',
        'phone',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marks the address as default and unmarks the user's other addresses.
     */
    public function makeDefault(): void
    {
        static::query()
            ->where('user_id', $this->user_id)
            ->whereKeyNot($this->getKey())
            ->update(['is_default' => false]);

        $this->forceFill(['is_default' => true])->save();
    }

    /**
     * @return array<string, string|null>
     */
    public function toSnapshot(): array
    {
        return $this->only(self::SNAPSHOT_FIELDS);
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->first_name} {$this->last_name}"));
    }
}
