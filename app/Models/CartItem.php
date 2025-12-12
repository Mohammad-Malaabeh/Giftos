<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->quantity < 1) {
                throw new \InvalidArgumentException('Quantity must be at least 1');
            }
        });
    }

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helpers
    public function getLineTotalAttribute()
    {
        return bcmul($this->unit_price, $this->quantity, 2);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Models\Variant::class);
    }

    // Scopes
    public function scopeByUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeWithProducts($q)
    {
        return $q->with(['product', 'variant']);
    }

    // Accessor
    public function getSubtotalAttribute(): string
    {
        return bcmul((string) $this->quantity, (string) ($this->unit_price ?? $this->product->effective_price ?? 0), 2);
    }
}
