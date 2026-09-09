<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionVideo extends Model
{
    protected $fillable = [
        'teacher_profile_id', 'subject_id', 'school_year_id',
        'grade_level', 'term', 'week', 'level',
        'sequence', 'video_type', 'video_url', 'file_name', 'file_path',
    ];
}