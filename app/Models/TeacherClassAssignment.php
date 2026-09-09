<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassAssignment extends Model
{
    protected $fillable = ['teacher_profile_id', 'class_id', 'subject_id'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
