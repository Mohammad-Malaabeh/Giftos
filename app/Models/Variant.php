<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock',
        'backorder_allowed',
        'backorder_eta',
        'options',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'options' => 'array',
        'backorder_allowed' => 'boolean',
        'backorder_eta' => 'date',
        'status' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionItems()
    {
        return $this->hasMany(VariantOption::class);
    }

    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price ?? $this->product?->effective_price;
    }
}
