<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Category::class)->ignore($category)],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists(Category::class, 'id'),
                function (string $attribute, mixed $value, Closure $fail) use ($category) {
                    if ($category && in_array((int) $value, $category->descendantAndSelfIds(), true)) {
                        $fail('The parent category cannot be the category itself or one of its subcategories.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryData(): array
    {
        return [
            ...$this->safe()->only(['name', 'slug', 'parent_id', 'description', 'is_active']),
            'position' => $this->integer('position'),
        ];
    }
}
