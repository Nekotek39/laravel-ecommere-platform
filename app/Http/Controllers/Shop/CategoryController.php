<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ProductFilterRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(ProductFilterRequest $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        $filters = [...$request->filters(), 'category' => $category];

        return view('shop.categories.show', [
            'category' => $category->load(['children' => fn ($q) => $q->active()]),
            'breadcrumbs' => $category->ancestorsAndSelf(),
            'products' => Product::query()->catalog($filters)->paginate(config('shop.per_page'))->withQueryString(),
            'filters' => $request->filters(),
            'sorts' => ProductFilterRequest::SORTS,
        ]);
    }
}
