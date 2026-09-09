<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Subject;

class InterventionQuiz extends Model
{
    protected $casts = ['questions' => 'array', 'settings' => 'array'];
    protected $fillable = [
        'teacher_profile_id',
        'subject_id',
        'school_year_id',
        'grade_level',
        'term',
        'week',
        'level',
        'exam_type',
        'input_method',
        'questions',
        'settings',
        'file_name',
    ];


    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
