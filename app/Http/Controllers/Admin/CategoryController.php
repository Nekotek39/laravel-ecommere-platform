<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->with('parent')
                ->withCount('products')
                ->when($request->query('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
                ->ordered()
                ->paginate(config('shop.admin_per_page'))
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true]),
            'parents' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->categoryData());

        return redirect()->route('admin.categories.index')->with('success', 'The category has been created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => Category::query()
                ->whereNotIn('id', $category->descendantAndSelfIds())
                ->ordered()
                ->get(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->categoryData());

        return redirect()->route('admin.categories.index')->with('success', 'The category has been updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'A category with assigned products cannot be deleted.');
        }

        // Subcategories are moved to the deleted category's parent.
        $category->children()->update(['parent_id' => $category->parent_id]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'The category has been deleted.');
    }
}
