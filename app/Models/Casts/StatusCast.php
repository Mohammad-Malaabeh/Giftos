<?php

declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StatusCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): bool
    {
        return (bool) $value;
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        return (string) $value;
    }
}
