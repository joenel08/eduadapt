<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionMaterial extends Model
{
    protected $fillable = [
        'teacher_profile_id', 'subject_id', 'school_year_id',
        'grade_level', 'term', 'week', 'level',
        'file_name', 'file_path',
    ];
}