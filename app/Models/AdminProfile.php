<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'profile_picture', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}