<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    public function scopeValid($q)
    {
        $now = now();
        return $q->where('active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            });
    }

    public function scopeExpired($q)
    {
        return $q->whereNotNull('expires_at')->where('expires_at', '<', now());
    }

    public function scopeNotStarted($q)
    {
        return $q->whereNotNull('starts_at')->where('starts_at', '>', now());
    }

    public function scopeByCode($q, string $code)
    {
        return $q->where('code', 'LIKE', $code);
    }

    public function scopeAvailable($q)
    {
        return $q->where(function ($query) {
            $query->whereNull('usage_limit')
                ->orWhereRaw('used_count < usage_limit');
        });
    }

    // Helpers
    public function isValidNow(): bool
    {
        $now = now();
        if (!$this->active)
            return false;
        if ($this->starts_at && $now->lt($this->starts_at))
            return false;
        if ($this->expires_at && $now->gt($this->expires_at))
            return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit)
            return false;
        return true;
    }

    public function applyToAmount(string|float $amount): float
    {
        $amount = (float) $amount;
        if ($this->type === 'fixed') {
            return max(0, $amount - (float) $this->value);
        }
        // percent
        $discount = $amount * ((float) $this->value) / 100.0;
        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }
        return max(0, $amount - $discount);
    }
}