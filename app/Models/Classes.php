<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes'; // optional, Laravel will guess 'classes'
    protected $fillable = ['school_year_id', 'grade_level', 'section_name'];

    public function subjects()
    {

        return Subject::where('grade_level', $this->grade_level)->get();
    }

    // Or if you have a pivot class_subjects, use that.

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function studentClassRecords()
    {
        return $this->hasMany(StudentClassRecord::class, 'class_id'); // foreign key
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherClassAssignment::class, 'class_id');
    }

    public function getSubjectNamesAttribute()
    {
        return $this->teacherAssignments->pluck('subject.name')->filter()->implode(', ');
    }
    
}
