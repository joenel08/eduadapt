<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    protected $fillable = [
        'teacher_profile_id',
        'subject_id',
        'grade_level',
        'term',
        'week',
        'type',
        'title',
        // 'description',
        'file_path'
    ];

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
