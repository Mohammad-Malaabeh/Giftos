<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- ADD THIS

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison, ShouldAutoSize, ShouldQueue // <--- ADD ShouldQueue
{
    public function query()
    {
        return Product::query()->with('category');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'SKU',
            'Price',
            'Sale Price',
            'Stock',
            'Status',
            'Category',
            'Created At',
            'Updated At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->title,
            $product->sku,
            number_format($product->price, 2),
            number_format($product->sale_price, 2),
            $product->stock,
            $product->status ? 'Active' : 'Inactive',
            $product->category->name ?? 'N/A',
            $product->created_at->format('Y-m-d H:i:s'),
            $product->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
