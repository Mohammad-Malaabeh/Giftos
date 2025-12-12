<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['name','countries','flat_rate','free_min'];
    protected $casts = ['countries'=>'array','flat_rate'=>'decimal:2','free_min'=>'decimal:2'];
}
