<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ProductFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(ProductFilterRequest $request): View
    {
        $filters = $request->filters();

        return view('shop.products.index', [
            'products' => Product::query()->catalog($filters)->paginate(config('shop.per_page'))->withQueryString(),
            'filters' => $filters,
            'sorts' => ProductFilterRequest::SORTS,
            'categories' => Category::query()->active()->roots()->ordered()->with('children')->get(),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category'])->loadAvg(
            ['reviews as rating_avg' => fn ($q) => $q->where('is_approved', true)],
            'rating',
        );

        $user = $request->user();

        return view('shop.products.show', [
            'product' => $product,
            'breadcrumbs' => $product->category?->ancestorsAndSelf() ?? [],
            'reviews' => $product->approvedReviews()->with('user:id,name')->paginate(10),
            'relatedProducts' => Product::query()
                ->active()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->with('mainImage')
                ->inRandomOrder()
                ->limit(4)
                ->get(),
            'canReview' => $user?->can('create', [Review::class, $product]) ?? false,
            'inWishlist' => $user?->wishlist()->whereKey($product->id)->exists() ?? false,
        ]);
    }
}
