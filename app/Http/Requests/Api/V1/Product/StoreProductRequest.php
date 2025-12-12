<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['required', 'integer', 'min:0'],
            'sku' => ['required', 'string', 'max:100', 'unique:products'],
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
            'title.required' => 'Product title is required.',
            'slug.required' => 'Product slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'price.required' => 'Product price is required.',
            'price.regex' => 'Price must have maximum 2 decimal places.',
            'sale_price.lt' => 'Sale price must be less than regular price.',
            'sku.required' => 'Product SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'category_id.exists' => 'Selected category does not exist.',
            'gallery.max' => 'Maximum 10 images allowed in gallery.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('active'),
        ]);
    }
}
