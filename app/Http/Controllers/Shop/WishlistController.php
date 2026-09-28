<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.wishlist', [
            'products' => $request->user()->wishlist()
                ->with('mainImage')
                ->latest('wishlist_items.created_at')
                ->paginate(config('shop.per_page')),
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $result = $request->user()->wishlist()->toggle($product->id);
        $added = $result['attached'] !== [];

        $message = $added ? 'Added to your wishlist.' : 'Removed from your wishlist.';

        if ($request->wantsJson()) {
            return response()->json(['message' => $message, 'in_wishlist' => $added]);
        }

        return back()->with('success', $message);
    }
}
