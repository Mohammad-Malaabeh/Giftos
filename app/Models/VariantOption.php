<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantOption extends Model
{
    protected $fillable = ['variant_id', 'name', 'value'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
