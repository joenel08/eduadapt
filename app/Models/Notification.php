<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message',
        'link', 'icon', 'color', 'available_at', 'read_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'read_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('available_at')
              ->orWhere('available_at', '<=', now());
        });
    }
}