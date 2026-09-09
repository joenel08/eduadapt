<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherClassAssignment;
use App\Models\Classes;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $teacher = $user->teacherProfile;

        $assignments = TeacherClassAssignment::with([
            'class.schoolYear',
            'class.studentClassRecords.studentProfile',
            'subject'
        ])
            ->where('teacher_profile_id', $teacher->id)
            ->get();

        // Each assignment becomes a "class card" for the teacher
        $classes = $assignments->map(function ($assignment) {
            $class = $assignment->class;
            return (object) [
                'id' => $class->id,
                'assignment_id' => $assignment->id,
                'subject' => $assignment->subject->name ?? 'N/A',
                'grade' => $class->grade_level,
                'section_name' => $class->section_name,
                'school_year' => $class->schoolYear->year,
                'students' => $class->studentClassRecords->count(),
                'code' => 'CLS-' . strtoupper(substr(md5($class->id . $assignment->subject_id), 0, 8)), // simple unique code
            ];
        });

        return view('teacher.classes', compact('classes'));
    }

    public function students(Classes $class)
    {
        $user = auth()->user();
        $teacher = $user->teacherProfile;

        // Verify teacher is assigned to this class
        $assigned = TeacherClassAssignment::where('teacher_profile_id', $teacher->id)
            ->where('class_id', $class->id)
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to this class.');
        }

        $class->load('studentClassRecords.studentProfile.user');
        $students = $class->studentClassRecords;

        return view('teacher.class-students', compact('class', 'students'));
    }
}