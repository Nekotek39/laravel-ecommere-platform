<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,inactive,low_stock,out_of_stock,trashed'],
        ]);

        $products = Product::query()
            ->with(['category', 'mainImage'])
            ->when($request->query('search'), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")))
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->query('status'), fn ($q, $status) => match ($status) {
                'active' => $q->where('is_active', true),
                'inactive' => $q->where('is_active', false),
                'low_stock' => $q->whereBetween('stock', [1, config('shop.low_stock_threshold')]),
                'out_of_stock' => $q->where('stock', 0),
                'trashed' => $q->onlyTrashed(),
            })
            ->latest()
            ->paginate(config('shop.admin_per_page'))
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['is_active' => true, 'stock' => 0]),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::query()->create($request->productData());

        $this->storeImages($product, $request->file('images', []));

        return redirect()->route('admin.products.edit', $product)->with('success', 'The product has been created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->productData());

        $this->storeImages($product, $request->file('images', []));

        return redirect()->route('admin.products.edit', $product)->with('success', 'The product has been updated.');
    }

    /**
     * Soft delete - the product stays linked to historical orders.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'The product has been moved to the trash.');
    }

    public function restore(Product $product): RedirectResponse
    {
        $product->restore();

        return redirect()->route('admin.products.edit', $product)->with('success', 'The product has been restored.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $image->delete();

        return back()->with('success', 'The image has been deleted.');
    }

    /**
     * Reorders images: images[] = list of IDs in the target order.
     */
    public function reorderImages(Request $request, Product $product): RedirectResponse
    {
        $ids = $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['integer'],
        ])['images'];

        foreach (array_values($ids) as $position => $id) {
            $product->images()->whereKey($id)->update(['position' => $position]);
        }

        return back()->with('success', 'The image order has been saved.');
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeImages(Product $product, array $files): void
    {
        $position = (int) $product->images()->max('position');

        foreach ($files as $file) {
            $product->images()->create([
                'path' => $file->store("products/{$product->id}", 'public'),
                'alt' => $product->name,
                'position' => ++$position,
            ]);
        }
    }
}
