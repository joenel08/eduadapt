<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostAssessment extends Model
{
  protected $fillable = [
        'teacher_profile_id',
        'subject_id',
        'grade_level',
        'term',
        'week',
        'exam_type',
        'input_method',
        'questions',
        'settings',
        'file_name',
        'school_year_id', 
    ];

    protected $casts = [
        'questions' => 'array',
        'settings' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}