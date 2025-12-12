<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'user_id', 'rating', 'comment', 'approved'];

    protected $casts = [
        'rating' => 'integer',
        'approved' => 'boolean',
    ];

    // Scopes
    public function scopeApproved($q)
    {
        return $q->where('approved', true);
    }

    public function scopePending($q)
    {
        return $q->where(function ($query) {
            $query->where('approved', false)
                ->orWhereNull('approved');
        });
    }

    public function scopeRecent($q)
    {
        return $q->orderByDesc('created_at');
    }

    public function scopeByRating($q, int $rating)
    {
        return $q->where('rating', $rating);
    }

    public function scopeHighRated($q)
    {
        return $q->where('rating', '>=', 4);
    }

    public function scopeLowRated($q)
    {
        return $q->where('rating', '<=', 2);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
