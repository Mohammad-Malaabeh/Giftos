<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    protected $casts = [
        'value' => 'json',
    ];

    public static function get(string $key, $default = null)
    {
        $cached = cache()->rememberForever("setting:$key", function () use ($key) {
            return optional(static::query()->where('key', $key)->first())->value;
        });
        return $cached ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        cache()->forget("setting:$key");
    }

    public static function allCached(): array
    {
        return cache()->rememberForever('settings:all', function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function forgetAllCache(): void
    {
        cache()->forget('settings:all');
        cache()->getStore()->flush();
    }
}
