<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('shop.home', [
            'featuredProducts' => Product::query()->catalog()->featured()->limit(8)->get(),
            'newProducts' => Product::query()->catalog()->limit(8)->get(),
            'saleProducts' => Product::query()->catalog()->whereColumn('compare_at_price', '>', 'price')->limit(8)->get(),
            'categories' => Category::query()->active()->roots()->ordered()->withCount('products')->get(),
        ]);
    }
}
