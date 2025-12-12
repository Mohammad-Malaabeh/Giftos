<?php

namespace App\Services;

use App\Models\ShippingZone;

class ShippingService
{
    public static function rateFor(string $country, float $subtotal): float
    {
        $country = strtoupper($country);
        $zones = cache()->remember('shipping_zones', 300, fn() => ShippingZone::all());

        foreach ($zones as $z) {
            $countries = array_map('strtoupper', (array)$z->countries);
            if (in_array($country, $countries, true)) {
                if ($z->free_min !== null && $subtotal >= (float)$z->free_min) {
                    return 0.0;
                }
                return (float)$z->flat_rate;
            }
        }
        // fallback to settings
        $freeMin = (float) (\App\Models\Setting::get('shop.free_shipping_min') ?? config('shop.free_shipping_min', 100));
        $flatShip = (float) (\App\Models\Setting::get('shop.flat_shipping') ?? config('shop.flat_shipping', 9.99));
        return $subtotal >= $freeMin ? 0.0 : $flatShip;
    }
}
