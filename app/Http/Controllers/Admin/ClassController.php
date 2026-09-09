<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'school_year_id' => 'required|exists:school_years,id',
                'grade_level' => 'required|in:Grade 5,Grade 6',
                'section_name' => 'required|string|max:255',
            ]);

            // Check for duplicate class
            $exists = Classes::where([
                'school_year_id' => $request->school_year_id,
                'grade_level' => $request->grade_level,
                'section_name' => $request->section_name,
            ])->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This class already exists for the selected school year.'
                ], 422);
            }

            $class = Classes::create($request->all());

            return response()->json([
                'success' => true,
                'class' => $class,
                'message' => 'Class created successfully.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Classes $class)
    {
        if ($class->studentClassRecords()->count() > 0) {
            return back()->with('error', 'Cannot delete class with enrolled students.');
        }
        $class->delete();
        return back()->with('success', 'Class deleted.');
    }

    public function students(Classes $class)
    {
        // Paginate student records (10 per page)
        $studentRecords = $class->studentClassRecords()
            ->with('studentProfile.user')
            ->paginate(10);

        return view('admin.class-students', compact('class', 'studentRecords'));
    }
}
