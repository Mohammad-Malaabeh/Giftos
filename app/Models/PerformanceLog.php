<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceLog extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopeSlow($query, $threshold = 1000)
    {
        return $query->where('duration_ms', '>', $threshold);
    }
    
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }
    
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
