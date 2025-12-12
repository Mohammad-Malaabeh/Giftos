<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'name',
        'line1',
        'line2',
        'city',
        'zip',
        'country',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected $casts = [
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
    ];

    // Add a type accessor for backward compatibility
    public function getTypeAttribute(): string
    {
        if ($this->is_default_billing) {
            return 'billing';
        }
        if ($this->is_default_shipping) {
            return 'shipping';
        }
        return 'shipping'; // default fallback
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
