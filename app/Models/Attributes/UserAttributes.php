<?php

declare(strict_types=1);

namespace App\Models\Attributes;

use Illuminate\Database\Eloquent\Casts\Attributes;

trait UserAttributes
{
    #[Attributes\Get]
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    #[Attributes\Get]
    public function getFormattedNameAttribute(): string
    {
        return ucwords(strtolower($this->name));
    }

    #[Attributes\Get]
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->email_verified_at);
    }

    #[Attributes\Get]
    public function getVerificationStatusAttribute(): string
    {
        return $this->is_verified ? 'verified' : 'unverified';
    }

    #[Attributes\Get]
    public function getRoleDisplayNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'customer' => 'Customer',
            'manager' => 'Manager',
            default => 'User'
        };
    }

    #[Attributes\Get]
    public function getAvatarUrlAttribute(): string
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s=200&d=identicon";
    }

    #[Attributes\Get]
    public function getJoinDateAttribute(): string
    {
        return $this->created_at->format('F j, Y');
    }

    #[Attributes\Get]
    public function getLastLoginAttribute(): ?string
    {
        return $this->updated_at->format('M j, Y H:i');
    }

    #[Attributes\Get]
    public function getHasAddressesAttribute(): bool
    {
        return $this->addresses()->exists();
    }

    #[Attributes\Get]
    public function getDefaultAddressAttribute(): ?\App\Models\UserAddress
    {
        return $this->defaultShippingAddress ?? $this->addresses()->first();
    }

    #[Attributes\Get]
    public function getOrderStatsAttribute(): array
    {
        $orders = $this->orders();
        
        return [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->sum('total'),
            'average_order_value' => $orders->avg('total') ?? 0,
            'last_order_date' => $orders->latest()->first()?->created_at?->format('Y-m-d'),
        ];
    }

    #[Attributes\Set]
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    #[Attributes\Set]
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = trim($value);
    }
}
