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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

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

        return view('admin.masterdata', compact('schoolYears', 'activeSchoolYear', 'classes', 'teachers', 'subjectsByGrade'));
    }

    // public function uploadStudent(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls',
    //         'class_id' => 'required|exists:classes,id',
    //     ]);

    //     try {
    //         $import = new StudentsImport($request->class_id);
    //         Excel::import($import, $request->file('file'));

    //         $successCount = $import->getSuccessCount();
    //         $errors = $import->getErrors();

    //         $message = "{$successCount} students uploaded successfully.";

    //         if (!empty($errors)) {
    //             $errorCount = count($errors);
    //             $message .= " {$errorCount} rows had errors.";
    //             // Store errors in session to display on view
    //             session()->flash('import_errors', array_slice($errors, 0, 10));
    //         }

    //         if ($successCount > 0 && empty($errors)) {
    //             return redirect()->route('admin.master-data')->with('success', $message);
    //         } elseif ($successCount > 0 && !empty($errors)) {
    //             return redirect()->route('admin.master-data')->with('warning', $message);
    //         } else {
    //             return redirect()->route('admin.master-data')->with('error', 'Import failed. No students were added. ' . (isset($errors[0]) ? 'First error: ' . $errors[0] : ''));
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Student upload failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Upload failed: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

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
            $message .= " " . count($errors) . " rows had errors.";
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => $message,
            'errors'  => array_slice($errors, 0, 10),
        ]);
    } catch (\Exception $e) {
        Log::error('Student upload failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage(),
        ], 500);
    }
}
    public function uploadTeacher(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new TeachersImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Teachers imported successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Teacher upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
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


    public function downloadTeacherTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header row
        $headers = ['employee_id', 'prefix_name','first_name', 'middle_name','last_name', 'suffix_name','contact_no','address'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0066CC'],
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Sample row
        $sheet->fromArray([
            ['EMP-0001', 'Dr.','Juan','Pluto', 'Dela Cruz', 'Jr.','09123456790','Purok 1, San Juan, Cabagan, Isabela'],
        ], null, 'A2');

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Note row
        $sheet->setCellValue('A4', 'Note: Do not change the column headers. Keep the same order. Delete this line if finish.');
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF666666'));

        $filename = 'teacher_upload_template.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
