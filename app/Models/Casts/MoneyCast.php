<?php

declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        return (float) $value / 100;
    }

    public function set($model, string $key, $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Value must be numeric');
        }

        return (int) round($value * 100);
    }
}
