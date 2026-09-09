<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id',
        'prefix_name',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix_name',
        'contact_no',
        'address',
        'profile_picture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teacherClassAssignments()
    {
        return $this->hasMany(TeacherClassAssignment::class);
    }

    // Helper to get assigned subjects
    public function assignedSubjectsForClass($classId)
    {
        return $this->teacherClassAssignments()
            ->where('class_id', $classId)
            ->with('subject')
            ->get()
            ->pluck('subject.name')
            ->implode(', ');
    }
}
