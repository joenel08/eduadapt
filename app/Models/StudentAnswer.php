<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
        'school_year_id', 'exam_type', 'student_id', 'grade_level',
        'class_id', 'subject_id', 'week',
        'answers', 'score', 'video_path'
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
    ];

    public function student()
{
    return $this->belongsTo(StudentProfile::class, 'student_id');
}
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

}