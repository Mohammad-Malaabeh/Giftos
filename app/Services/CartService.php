<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Variant;
use Illuminate\Auth\Access\AuthorizationException;

class CartService
{
    public function __construct(
        protected ?Authenticatable $user,
        protected ?string $sessionId
    ) {}

    public static function fromRequest(): self
    {
        return new self(auth()->user(), session('cart_session_id'));
    }

    public function items(): Collection
    {
        return CartItem::with('product')
            ->where(function ($q) {
                if ($this->user) {
                    $q->where('user_id', $this->user->id);
                } else {
                    $q->whereNull('user_id')->where('session_id', $this->sessionId);
                }
            })
            ->get();
    }

    public function add(Product $product, int $qty = 1): CartItem
    {
        $qty = max(1, $qty);
        $builder = CartItem::query()
            ->where('product_id', $product->id)
            ->where(function ($q) {
                if ($this->user) {
                    $q->where('user_id', $this->user->id);
                } else {
                    $q->whereNull('user_id')->where('session_id', $this->sessionId);
                }
            });

        $existing = $builder->first();

        if ($existing) {
            $existing->quantity += $qty;
            $existing->unit_price = $existing->unit_price; // keep snapshot
            $existing->save();
            return $existing;
        }

        return CartItem::create([
            'user_id' => $this->user?->id,
            'session_id' => $this->user ? null : $this->sessionId,
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_price' => $product->effective_price,
        ]);
    }

    public function updateQuantity(CartItem $item, int $qty): void
    {
        $qty = max(1, $qty);
        $this->authorizeItem($item);
        $item->quantity = $qty;
        $item->save();
    }

    public function remove(CartItem $item): void
    {
        $this->authorizeItem($item);
        $item->delete();
    }

    public function clear(): void
    {
        CartItem::where(function ($q) {
            if ($this->user) {
                $q->where('user_id', $this->user->id);
            } else {
                $q->whereNull('user_id')->where('session_id', $this->sessionId);
            }
        })->delete();
    }

    public function totals(?string $couponCode = null): array
    {
        $items = $this->items();
        $subtotal = 0.0;
        foreach ($items as $i) {
            $subtotal += (float) $i->unit_price * $i->quantity;
        }
        $subtotal = round($subtotal, 2);

        // Config-driven demo rates
        $freeMin = (float) (Setting::get('shop.free_shipping_min') ?? config('shop.free_shipping_min', 100));
        $flatShip = (float) (Setting::get('shop.flat_shipping') ?? config('shop.flat_shipping', 9.99));
        $taxPercent = (float) (Setting::get('shop.tax_percent') ?? config('shop.tax_percent', 10));

        $country = strtoupper((string) session('checkout_country'));
        $taxPercent = TaxService::rateFor($country ?: null);
        $shipping = $country ? ShippingService::rateFor($country, $subtotal) : ($subtotal >= $freeMin ? 0.0 : $flatShip);
        $tax = round($subtotal * ($taxPercent / 100), 2);
        $discount = 0.0;

        $code = $couponCode ?? session('coupon');
        if ($code) {
            $coupon = \App\Models\Coupon::where('code', $code)->first();
            if ($coupon && $coupon->isValidNow()) {
                $after = $coupon->applyToAmount($subtotal);
                $discount = round(max(0, $subtotal - $after), 2);
            }
        }

        $country = session('checkout_country');
        $shipping = $country ? \App\Services\ShippingService::rateFor($country, $subtotal)
            : ($subtotal >= $freeMin ? 0.0 : $flatShip);

        $total = round($subtotal - $discount + $tax + $shipping, 2);

        return compact('subtotal', 'discount', 'tax', 'shipping', 'total');
    }

    public function mergeGuestIntoUser(int $userId, string $guestSessionId): void
    {
        DB::transaction(function () use ($userId, $guestSessionId) {
            $guestItems = CartItem::whereNull('user_id')
                ->where('session_id', $guestSessionId)
                ->get();

            foreach ($guestItems as $gi) {
                $existing = CartItem::where('user_id', $userId)
                    ->where('product_id', $gi->product_id)
                    ->first();

                if ($existing) {
                    $existing->quantity += $gi->quantity;
                    $existing->save();
                    $gi->delete();
                } else {
                    $gi->user_id = $userId;
                    $gi->session_id = null;
                    $gi->save();
                }
            }
        });
    }

    protected function authorizeItem(CartItem $item): void
    {
        if ($this->user) {
            if ($item->user_id !== $this->user->id) {
                throw new AuthorizationException();
            }
        } else {
            if (!is_null($item->user_id) || $item->session_id !== $this->sessionId) {
                throw new AuthorizationException();
            }
        }
    }

    public function addVariant(Product $product, Variant $variant, int $qty = 1): CartItem
    {
        $qty = max(1, $qty);

        $builder = CartItem::query()
            ->where('product_id', $product->id)
            ->where('variant_id', $variant->id)
            ->where(function ($q) {
                if ($this->user) {
                    $q->where('user_id', $this->user->id);
                } else {
                    $q->whereNull('user_id')->where('session_id', $this->sessionId);
                }
            });

        $existing = $builder->first();

        if ($existing) {
            $existing->quantity += $qty;
            $existing->save();
            return $existing;
        }

        return CartItem::create([
            'user_id' => $this->user?->id,
            'session_id' => $this->user ? null : $this->sessionId,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => $qty,
            'unit_price' => $variant->effective_price,
        ]);
    }
}
