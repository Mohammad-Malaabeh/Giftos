<?php

declare(strict_types=1);

namespace App\Models\Attributes;

use Illuminate\Database\Eloquent\Casts\Attributes;

trait ProductAttributes
{
    #[Attributes\Get]
    public function getCurrentPriceAttribute(): ?float
    {
        // Return sale price if available, otherwise regular price
        return $this->sale_price ?? $this->price;
    }

    #[Attributes\Get]
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    #[Attributes\Get]
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    #[Attributes\Get]
    public function getFormattedSalePriceAttribute(): ?string
    {
        return $this->sale_price ? '$' . number_format($this->sale_price, 2) : null;
    }

    #[Attributes\Get]
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->sale_price || $this->sale_price >= $this->price || $this->price <= 0) {
            return null;
        }

        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    #[Attributes\Get]
    public function getStockStatusAttribute(): string
    {
        if ($this->stock > 10) {
            return 'in_stock';
        } elseif ($this->stock > 0) {
            return 'low_stock';
        } elseif ($this->backorder_allowed) {
            return 'backorder';
        } else {
            return 'out_of_stock';
        }
    }

    #[Attributes\Get]
    public function getStockStatusTextAttribute(): string
    {
        return match ($this->stock_status) {
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'backorder' => 'Backorder Available',
            'out_of_stock' => 'Out of Stock',
            default => 'Unknown'
        };
    }

    #[Attributes\Get]
    public function getFormattedViewsAttribute(): string
    {
        return number_format($this->views);
    }

    #[Attributes\Get]
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    #[Attributes\Get]
    public function getHasVariantsAttribute(): bool
    {
        // Check if variants_count is loaded (from withCount)
        if (isset($this->variants_count)) {
            return $this->variants_count > 0;
        }

        // Check if variants relationship is already loaded
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        // Fallback to query (should be avoided in production)
        return $this->variants()->exists();
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    #[Attributes\Get]
    public function getMainImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->image_path;
        }

        if (is_array($this->gallery) && !empty($this->gallery)) {
            return $this->gallery[0];
        }

        return null;
    }

    #[Attributes\Set]
    public function setGalleryAttribute($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        if (!is_array($value)) {
            $value = [];
        }

        return array_filter($value, function ($url) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        });
    }
}
