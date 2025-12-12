<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products')->ignore($productId)],
            'featured' => ['boolean'],
            'active' => ['boolean'],
            'category_id' => ['exists:categories,id'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['url', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'This slug is already in use.',
            'price.regex' => 'Price must have maximum 2 decimal places.',
            'sale_price.lt' => 'Sale price must be less than regular price.',
            'sku.unique' => 'This SKU is already in use.',
            'category_id.exists' => 'Selected category does not exist.',
            'gallery.max' => 'Maximum 10 images allowed in gallery.',
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('active')) {
            $this->merge([
                'status' => $this->boolean('active'),
            ]);
        }
    }
}
