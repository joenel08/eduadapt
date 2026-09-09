<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentContentProgress extends Model
{
    protected $fillable = [
        'student_profile_id',
        'content_type',
        'content_id',
        'status',
        'score',
        'answers',
        'started_at',
        'video_path',
        'completed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
        'score' => 'integer',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class);
    }
}
