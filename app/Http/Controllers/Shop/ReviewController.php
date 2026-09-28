<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $product->reviews()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'is_approved' => false,
        ]);

        return back()->with('success', 'Thank you! Your review will be published after moderation.');
    }
}
