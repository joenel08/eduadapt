<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['grade_level', 'name'];

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherClassAssignment::class);
    }
}