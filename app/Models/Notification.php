<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Vérifications
    public function isRead(): bool
    {
        return $this->is_read;
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    // Scopes
    public function scopeNonLu($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeLu($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }
}