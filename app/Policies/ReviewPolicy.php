<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Only a customer who bought the product and has not reviewed it yet can leave a review.
     */
    public function create(User $user, Product $product): bool
    {
        return $user->hasPurchased($product)
            && ! $user->reviews()->where('product_id', $product->id)->exists();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->user_id === $user->id;
    }
}
