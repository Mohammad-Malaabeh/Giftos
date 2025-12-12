<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'on_sale' => ['nullable', 'boolean'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:price_asc,price_desc,name_asc,name_desc,created_at,created_at_desc,popularity'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Search term is required.',
            'q.min' => 'Search term must be at least 2 characters.',
            'q.max' => 'Search term cannot exceed 100 characters.',
            'price_max.gt' => 'Maximum price must be greater than minimum price.',
            'sort.in' => 'Invalid sort option.',
            'per_page.max' => 'Cannot display more than 50 items per page.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('price_min') && $this->has('price_max')) {
                if ($this->float('price_min') >= $this->float('price_max')) {
                    $validator->errors()->add('price_max', 'Maximum price must be greater than minimum price.');
                }
            }
        });
    }
}
