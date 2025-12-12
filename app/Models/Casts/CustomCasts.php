<?php

declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
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

class GalleryCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        $decoded = Json::decode($value);
        
        if (!is_array($decoded)) {
            return [];
        }

        return array_filter($decoded, function ($url) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        });
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (!is_array($value)) {
            $value = [];
        }

        $filtered = array_filter($value, function ($url) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        });

        return Json::encode(array_values($filtered));
    }
}

class MetaDataCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        $decoded = Json::decode($value);
        
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (!is_array($value)) {
            $value = [];
        }

        return Json::encode($value);
    }
}

class StatusCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): bool
    {
        return (bool) $value;
    }

    public function set($model, string $key, $value, array $attributes): int
    {
        return $value ? 1 : 0;
    }
}

class RatingCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        $rating = (float) $value;
        
        // Round to 1 decimal place
        return round($rating, 1);
    }

    public function set($model, string $key, $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        $rating = (float) $value;
        
        // Validate rating range
        if ($rating < 0 || $rating > 5) {
            throw new InvalidArgumentException('Rating must be between 0 and 5');
        }

        // Round to 1 decimal place
        return round($rating, 1);
    }
}
