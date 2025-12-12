<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category; // Needed to map category names to IDs
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // For mapping column names
use Maatwebsite\Excel\Concerns\WithBatchInserts; // For performance
use Maatwebsite\Excel\Concerns\WithChunkReading; // For performance
use Maatwebsite\Excel\Concerns\WithValidation; // For data validation
use Illuminate\Validation\Rule; // For validation rules
use Illuminate\Support\Collection; // For category caching
use Maatwebsite\Excel\Concerns\ToCollection; // Alternative if ToModel is too restrictive

// Option 1: ToModel for simple row-by-row mapping
class ProductsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    private $categories;

    public function __construct()
    {
        // Cache categories to reduce database queries during import
        $this->categories = Category::all(['id', 'name'])->pluck('id', 'name');
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip rows where 'title' is empty (assuming title is mandatory)
        if (!isset($row['title']) || empty($row['title'])) {
            return null;
        }

        // Find category ID based on name, default to null if not found
        $categoryId = $this->categories[$row['category']] ?? null;

        // You might want to update an existing product if 'id' or 'sku' is provided
        // For simplicity, this example always creates new products.
        // If you want to update, add logic here:
        // $product = Product::find($row['id']) ?? Product::where('sku', $row['sku'])->first();
        // if ($product) { $product->fill([...])->save(); return null; }

        return new Product([
            'category_id'       => $categoryId,
            'title'             => $row['title'],
            'slug'              => \Illuminate\Support\Str::slug($row['title']), // Generate slug from title
            'description'       => $row['description'] ?? null,
            'stock'             => $row['stock'] ?? 0,
            'price'             => $row['price'] ?? 0.00,
            'sale_price'        => $row['sale_price'] ?? null,
            'sku'               => $row['sku'] ?? null,
            // 'image_path'        => $row['image_path'] ?? null, // Handle image path if importing
            // 'gallery'           => json_decode($row['gallery'] ?? '[]', true), // Decode JSON if provided
            'status'            => strtolower($row['status']) === 'active' || $row['status'] === '1',
            'backorder_allowed' => strtolower($row['backorder_allowed']) === 'true' || $row['backorder_allowed'] === '1',
            'backorder_eta'     => isset($row['backorder_eta']) ? \Carbon\Carbon::parse($row['backorder_eta']) : null,
            'meta_title'        => $row['meta_title'] ?? null,
            'meta_description'  => $row['meta_description'] ?? null,
            // 'created_at' => $row['created_at'] // Uncomment if you want to import creation date
            // 'updated_at' => $row['updated_at'] // Uncomment if you want to import update date
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|exists:categories,name', // Validate category name exists
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|lt:price|min:0', // Sale price less than price
            'sku' => 'nullable|string|max:255|unique:products,sku', // SKU must be unique
            'status' => 'required|in:active,inactive,1,0',
            // Add other rules as necessary
        ];
    }

    public function customValidationMessages()
    {
        return [
            'category.exists' => 'The category ":input" does not exist.',
            'sale_price.lt' => 'The sale price must be less than the regular price.',
        ];
    }

    public function batchSize(): int
    {
        return 1000; // Insert 1000 models at once
    }

    public function chunkSize(): int
    {
        return 1000; // Read 1000 rows into memory at once
    }
}
