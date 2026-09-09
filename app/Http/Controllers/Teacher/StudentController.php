<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\TeacherProfile;

use App\Models\TeacherClassAssignment;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Classes $class)
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        // Ensure teacher is assigned to this class
        $assignment = TeacherClassAssignment::where('teacher_profile_id', $teacher->id)
            ->where('class_id', $class->id)
            ->exists();

        if (!$assignment) {
            abort(403, 'You are not assigned to this class.');
        }

        // Load students with profiles
        $class->load('studentClassRecords.studentProfile.user');
        $students = $class->studentClassRecords;

        return view('teacher.students', compact('class', 'students'));
    }
}