<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = ['year', 'is_active'];

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }
}