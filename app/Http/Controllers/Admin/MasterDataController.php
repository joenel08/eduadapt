<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Imports\TeachersImport;
use App\Models\StudentClassRecord;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SchoolYear;
use App\Models\Classes;
use App\Models\Subject;

use Illuminate\Support\Facades\Log;

class MasterDataController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::orderBy('year', 'desc')->get();
        $activeSchoolYear = SchoolYear::getActive();
        $subjectsByGrade = Subject::all()->groupBy('grade_level');

        $classes = Classes::with('schoolYear', 'studentClassRecords.studentProfile')
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                return $query->where('school_year_id', $activeSchoolYear->id);
            })
            ->get()
            ->groupBy('grade_level');

        // Eager load teacher assignments and their classes
        $teachers = TeacherProfile::with(['user', 'teacherClassAssignments.class'])->get();

        return view('admin.masterdata', compact('schoolYears', 'activeSchoolYear', 'classes', 'teachers','subjectsByGrade'));
    }

    public function uploadStudent(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'class_id' => 'required|exists:classes,id',
        ]);

        try {
            $import = new StudentsImport($request->class_id);
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            $message = "{$successCount} students uploaded successfully.";

            if (!empty($errors)) {
                $errorCount = count($errors);
                $message .= " {$errorCount} rows had errors.";
                // Store errors in session to display on view
                session()->flash('import_errors', array_slice($errors, 0, 10));
            }

            if ($successCount > 0 && empty($errors)) {
                return redirect()->route('admin.master-data')->with('success', $message);
            } elseif ($successCount > 0 && !empty($errors)) {
                return redirect()->route('admin.master-data')->with('warning', $message);
            } else {
                return redirect()->route('admin.master-data')->with('error', 'Import failed. No students were added. ' . (isset($errors[0]) ? 'First error: ' . $errors[0] : ''));
            }
        } catch (\Exception $e) {
            Log::error('Student upload failed: ' . $e->getMessage());
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }
    public function uploadTeacher(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new TeachersImport, $request->file('file'));

        return redirect()->route('admin.master-data')->with('success', 'Teachers imported successfully.');
    }

    public function deleteStudent($lrn)
    {
        // Option to delete a student profile (and user)
        $profile = StudentProfile::where('lrn', $lrn)->first();
        if ($profile) {
            $profile->user->delete(); // cascades to profile and class records
        }
        return back()->with('success', 'Student removed.');
    }

    public function deleteTeacher($employeeId)
    {
        $profile = TeacherProfile::where('employee_id', $employeeId)->first();
        if ($profile) {
            $profile->user->delete();
        }
        return back()->with('success', 'Teacher removed.');
    }
}
