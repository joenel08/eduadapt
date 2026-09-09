<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherClassAssignment;
use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Subject;

class TeacherAssignmentController extends Controller
{
    public function assign(Request $request)
    {
        $request->validate([
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        // Verify subject belongs to the same grade level as class
        $class = Classes::find($request->class_id);
        $subject = Subject::find($request->subject_id);
        if ($class->grade_level !== $subject->grade_level) {
            return back()->with('error', 'Subject grade level does not match class grade level.');
        }

        TeacherClassAssignment::firstOrCreate([
            'teacher_profile_id' => $request->teacher_profile_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
        ]);

        return back()->with('success', 'Teacher assigned to subject for this class.');
    }

    public function unassign(TeacherClassAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Teacher unassigned.');
    }
}
