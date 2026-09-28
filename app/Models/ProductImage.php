<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'alt',
        'position',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $image) {
            Storage::disk('public')->delete($image->path);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(function () {
            if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
                return $this->path;
            }

            return Storage::disk('public')->url($this->path);
        });
    }
}
