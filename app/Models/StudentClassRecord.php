<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassRecord extends Model
{
    protected $fillable = ['class_id', 'student_id', 'school_year_id'];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }
    // Relationship to the student (User model)
    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
