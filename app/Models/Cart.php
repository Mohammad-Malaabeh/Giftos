<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = ['user_id', 'meta'];

    /**
     * Cast meta to array so Eloquent serializes it for JSON columns.
     * This prevents array-to-string conversion errors on SQLite during tests.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
