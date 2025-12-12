<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Events\OrderPaymentStatusChanged;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'number',
        'status',
        'subtotal',
        'discount',
        'shipping',
        'tax',
        'total',
        'payment_method',
        'payment_status',
        'transaction_id',
        'billing_address',
        'shipping_address',
        'coupon_code',
        'carrier',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'completed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Alias for items() for backward compatibility
        return $this->items();
    }

    // BelongsToMany Relationships
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot([
                'variant_id',
                'quantity',
                'price',
                'sale_price',
                'total',
                'product_name',
                'product_sku',
                'variant_name',
                'variant_attributes',
                'status',
                'notes',
                'metadata',
            ])
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function activeProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('status', '!=', 'cancelled');
    }

    public function cancelledProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('status', 'cancelled');
    }

    public function shippedProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('status', 'shipped');
    }

    public function deliveredProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('status', 'delivered');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, ?string $startDate = null, ?string $endDate = null)
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query;
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', '!=', 'paid');
    }

    /**
     * Get total item count.
     * 
     * ⚠️ WARNING: This triggers a database query every time it's accessed.
     * Use Order::withSum('items', 'quantity') in your controller instead,
     * then access as $order->items_sum_quantity.
     * 
     * @deprecated Use withSum('items', 'quantity') instead for better performance
     * @return int
     */
    public function getItemCountAttribute(): int
    {
        return $this->products()->sum('quantity');
    }

    /**
     * Get unique product count.
     * 
     * ⚠️ WARNING: This triggers a database query every time it's accessed.
     * Use Order::withCount('products') in your controller instead,
     * then access as $order->products_count.
     * 
     * @deprecated Use withCount('products') instead for better performance
     * @return int
     */
    public function getUniqueProductCountAttribute(): int
    {
        return $this->products()->count();
    }

    public function getActiveItemCountAttribute(): int
    {
        return $this->activeProducts()->sum('quantity');
    }

    public function getCancelledItemCountAttribute(): int
    {
        return $this->cancelledProducts()->sum('quantity');
    }

    public function getShippedItemCountAttribute(): int
    {
        return $this->shippedProducts()->sum('quantity');
    }

    public function getDeliveredItemCountAttribute(): int
    {
        return $this->deliveredProducts()->sum('quantity');
    }

    public function getIsFullyShippedAttribute(): bool
    {
        return $this->getActiveItemCountAttribute() === 0;
    }

    public function getIsFullyDeliveredAttribute(): bool
    {
        return $this->getDeliveredItemCountAttribute() === $this->getItemCountAttribute();
    }

    public function getHasShippedItemsAttribute(): bool
    {
        return $this->getShippedItemCountAttribute() > 0;
    }

    // Product Management Methods
    public function addProduct(Product $product, int $quantity = 1, array $pivotData = []): self
    {
        $this->products()->syncWithoutDetaching([
            $product->id => array_merge([
                'quantity' => $quantity,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'total' => $product->price * $quantity,
                'product_name' => $product->title,
                'product_sku' => $product->sku,
                'status' => 'pending',
            ], $pivotData)
        ]);

        $this->recalcTotals();
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products()->detach($product->id);
        $this->recalcTotals();
        return $this;
    }

    public function updateProductQuantity(Product $product, int $quantity): self
    {
        if ($quantity <= 0) {
            return $this->removeProduct($product);
        }

        $pivot = $this->products()->where('product_id', $product->id)->first();
        if ($pivot) {
            $price = $pivot->pivot->price;
            $this->products()->updateExistingPivot($product->id, [
                'quantity' => $quantity,
                'total' => $price * $quantity,
            ]);
            $this->recalcTotals();
        }

        return $this;
    }

    public function updateProductStatus(Product $product, string $status): self
    {
        $this->products()->updateExistingPivot($product->id, ['status' => $status]);
        return $this;
    }

    public function getProductsByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return $this->products()->wherePivot('status', $status)->get();
    }

    public function hasProduct(int $productId): bool
    {
        return $this->products()->where('product_id', $productId)->exists();
    }

    public function getProductQuantity(int $productId): int
    {
        return $this->products()->where('product_id', $productId)->value('quantity') ?? 0;
    }

    // Order Status Management
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']) && $this->payment_status !== 'paid';
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'pending' && $this->payment_status === 'paid';
    }

    public function canBeShipped(): bool
    {
        return in_array($this->status, ['processing', 'paid']) && $this->getHasShippedItemsAttribute() === false;
    }

    public function canBeCompleted(): bool
    {
        return $this->getIsFullyDeliveredAttribute() && $this->payment_status === 'paid';
    }

    public function markAsProcessing(): self
    {
        $this->status = 'processing';
        $this->save();
        return $this;
    }

    public function markAsShipped(string $carrier = null, string $trackingNumber = null): self
    {
        $this->status = 'shipped';
        $this->carrier = $carrier;
        $this->tracking_number = $trackingNumber;
        $this->shipped_at = now();
        $this->save();
        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
        return $this;
    }

    public function markAsCancelled(string $reason = null): self
    {
        $this->status = 'cancelled';
        $this->save();

        // Cancel all active products
        $this->activeProducts()->each(function ($product) {
            $this->updateProductStatus($product, 'cancelled');
        });

        return $this;
    }

    // Helpers
    public function markPaid(string $transactionId = null): self
    {
        $this->payment_status = 'paid';
        $this->transaction_id = $transactionId;
        $this->paid_at = now();
        $this->status = 'paid';
        $this->save();
        return $this;
    }

    public function recalcTotals(): void
    {
        $subtotal = $this->activeProducts()->sum('total');
        $this->subtotal = $subtotal;
        $this->total = bcadd(bcadd(bcsub((string) $subtotal, (string) $this->discount, 2), (string) $this->shipping, 2), (string) $this->tax, 2);
        $this->save();
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    protected static function booted()
    {
        static::updating(function (Order $order) {
            // Store old status in a static property to avoid database issues
            static::$_oldPaymentStatus = $order->getOriginal('payment_status');
        });

        static::updated(function (Order $order) {
            $old = static::$_oldPaymentStatus ?? null;
            $new = $order->payment_status;

            if ($old && $new && $old !== $new) {
                event(new OrderPaymentStatusChanged($order, (string) $old, (string) $new));
            }
        });
    }

    private static $_oldPaymentStatus;
}
