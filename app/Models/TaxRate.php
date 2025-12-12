<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = ['country','percent'];
    protected $casts = ['percent'=>'decimal:2'];
}
