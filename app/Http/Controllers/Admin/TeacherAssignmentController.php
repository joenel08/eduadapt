<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherClassAssignment;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    /**
     * Return existing assignments for a teacher (AJAX for the modal).
     */
    public function index($teacherId)
    {
        $assignments = TeacherClassAssignment::with(['class', 'subject'])
            ->where('teacher_profile_id', $teacherId)
            ->get()
            ->map(function ($a) {
                return [
                    'id'         => $a->id,
                    'class_id'   => $a->class_id,
                    'subject_id' => $a->subject_id,
                    'class_name' => $a->class
                        ? $a->class->grade_level . ' - ' . $a->class->section_name
                        : 'Unknown class',
                    'subject'    => $a->subject->name ?? 'Unknown subject',
                ];
            });

        return response()->json(['success' => true, 'assignments' => $assignments]);
    }

    /**
     * Assign a teacher to a class + subject.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'class_id'           => 'required|exists:classes,id',
            'subject_id'         => 'required|exists:subjects,id',
        ]);

        $class   = Classes::find($request->class_id);
        $subject = Subject::find($request->subject_id);

        // Verify subject grade level matches class grade level
        if ($class->grade_level !== $subject->grade_level) {
            return response()->json([
                'success' => false,
                'message' => 'Subject grade level does not match class grade level.',
            ], 422);
        }

        // firstOrCreate is now safe because the unique index is
        // (teacher_profile_id, class_id, subject_id)
        $assignment = TeacherClassAssignment::firstOrCreate([
            'teacher_profile_id' => $request->teacher_profile_id,
            'class_id'           => $request->class_id,
            'subject_id'         => $request->subject_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teacher assigned successfully.',
            'assignment' => [
                'id'         => $assignment->id,
                'class_id'   => $assignment->class_id,
                'subject_id' => $assignment->subject_id,
                'class_name' => $class->grade_level . ' - ' . $class->section_name,
                'subject'    => $subject->name,
            ],
        ]);
    }

    /**
     * Remove one assignment.
     */
    public function destroy($id)
    {
        $assignment = TeacherClassAssignment::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        $assignment->delete();

        return response()->json(['success' => true, 'message' => 'Assignment removed.']);
    }
}