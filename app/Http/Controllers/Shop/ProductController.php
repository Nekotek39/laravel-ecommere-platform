<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $products = Product::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('shop.products.index', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function show(Product $product): View
    {
        return view('shop.products.show', [
            'product' => $product,
        ]);
    }
}
