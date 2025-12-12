<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'status',
        'page_url',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
