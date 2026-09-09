<?php
// app/Models/ContentRelease.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentRelease extends Model
{
    protected $fillable = [
        'teacher_profile_id',
        'content_type',
        'content_id',
        'class_id',
        'release_date',
        'subject_id',
        'due_date'
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    // ContentRelease.php
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
