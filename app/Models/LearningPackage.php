<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_profile_id', 'grade_level', 'term', 'subject', 'week',
        'assigned_class_ids', 'release_date', 'due_date', 'status'
    ];

    protected $casts = [
        'assigned_class_ids' => 'array',
        'release_date' => 'date',
        'due_date' => 'date',
    ];
}