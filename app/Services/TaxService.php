<?php

namespace App\Services;

use App\Models\TaxRate;

class TaxService
{
    public static function rateFor(?string $country): float
    {
        if (!$country) {
            return (float) (\App\Models\Setting::get('shop.tax_percent') ?? config('shop.tax_percent', 10));
        }
        $country = strtoupper($country);
        $rate = cache()->remember("tax:$country", 300, function() use ($country) {
            return optional(TaxRate::where('country',$country)->first())->percent;
        });
        return $rate !== null ? (float)$rate : (float) (\App\Models\Setting::get('shop.tax_percent') ?? config('shop.tax_percent', 10));
    }
}