<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Category tree: root category => subcategories.
     *
     * @var array<string, list<string>>
     */
    private const TREE = [
        'Electronics' => ['Smartphones', 'Laptops', 'Headphones', 'Accessories'],
        'Home & Garden' => ['Furniture', 'Lighting', 'Tools'],
        'Fashion' => ['Women', 'Men', 'Shoes'],
        'Sports' => ['Fitness', 'Bikes', 'Outdoor'],
        'Books' => [],
    ];

    public function run(): void
    {
        $position = 0;

        foreach (self::TREE as $rootName => $children) {
            $root = Category::query()->create([
                'name' => $rootName,
                'position' => $position++,
                'is_active' => true,
            ]);

            $leaves = [];

            foreach ($children as $i => $childName) {
                $leaves[] = Category::query()->create([
                    'parent_id' => $root->id,
                    'name' => $childName,
                    'position' => $i,
                    'is_active' => true,
                ]);
            }

            foreach ($leaves ?: [$root] as $category) {
                Product::factory()->count(6)->for($category)->create();
                Product::factory()->count(2)->for($category)->onSale()->create();
                Product::factory()->for($category)->featured()->create();
                Product::factory()->for($category)->outOfStock()->create();
            }
        }
    }
}
