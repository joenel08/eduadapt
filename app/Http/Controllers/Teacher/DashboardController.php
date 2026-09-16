<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\TeacherClassAssignment;
use App\Models\StudentContentProgress;
use App\Models\PostAssessment;
use App\Models\StudentClassRecord;
use App\Models\Classes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $assignments = TeacherClassAssignment::with([
            'class.schoolYear',
            'class.studentClassRecords.studentProfile',
            'subject'
        ])
            ->where('teacher_profile_id', $teacher->id)
            ->get();

        $classes = $assignments->groupBy('class_id')->map(function ($items, $classId) {
            $class = $items->first()->class;
            $subjects = $items->pluck('subject.name')->implode(', ');
            $studentCount = $class->studentClassRecords->count();
            return (object)[
                'id' => $class->id,
                'grade_level' => $class->grade_level,
                'section_name' => $class->section_name,
                'school_year' => $class->schoolYear->year,
                'subjects' => $subjects,
                'student_count' => $studentCount,
            ];
        })->values();

        $totalStudents = $classes->sum('student_count');

        // ---- 1. Readiness Distribution ----
        // Get all student IDs across all classes
        $classIds = $classes->pluck('id')->toArray();
        $studentIds = StudentClassRecord::whereIn('class_id', $classIds)
            ->pluck('student_profile_id')
            ->unique()
            ->values()
            ->toArray();

        $readiness = ['advanced' => 0, 'average' => 0, 'belowAverage' => 0];

        if (!empty($studentIds)) {
            // Fetch the latest post-assessment score for each student (across all subjects)
            $latestPosts = StudentContentProgress::whereIn('student_profile_id', $studentIds)
                ->where('content_type', 'App\\Models\\PostAssessment')
                ->whereNotNull('score')
                ->get()
                ->groupBy('student_profile_id')
                ->map(function ($records) {
                    return $records->sortByDesc('created_at')->first();
                });

            foreach ($latestPosts as $progress) {
                $score = $progress->score;
                if ($score >= 85) $readiness['advanced']++;
                elseif ($score >= 70) $readiness['average']++;
                else $readiness['belowAverage']++;
            }
        }

        // ---- 2. Average Score by Class ----
        $classScores = [];
        foreach ($classes as $class) {
            // Get students in this class
            $studentIdsInClass = StudentClassRecord::where('class_id', $class->id)
                ->pluck('student_profile_id')
                ->toArray();

            if (empty($studentIdsInClass)) {
                $classScores[$class->id] = 0;
                continue;
            }

            // Get latest post-assessment scores for these students
            $scores = StudentContentProgress::whereIn('student_profile_id', $studentIdsInClass)
                ->where('content_type', 'App\\Models\\PostAssessment')
                ->whereNotNull('score')
                ->get()
                ->groupBy('student_profile_id')
                ->map(function ($records) {
                    return $records->sortByDesc('created_at')->first()->score;
                })
                ->values();

            $average = $scores->isNotEmpty() ? round($scores->average()) : 0;
            $classScores[$class->id] = $average;
        }

        // Prepare data for charts
        $readinessData = [
            'labels' => ['Below Average', 'Average', 'Advanced'],
            'data' => [
                $readiness['belowAverage'],
                $readiness['average'],
                $readiness['advanced']
            ],
            'colors' => ['#FFB3BA', '#FFD9B3', '#B3E5B3'],
            'borderColors' => ['#CC0000', '#FF8800', '#00AA66'],
        ];

        $classScoreData = [
            'labels' => $classes->map(fn($c) => $c->grade_level . '-' . $c->section_name)->toArray(),
            'data' => $classes->map(fn($c) => $classScores[$c->id] ?? 0)->toArray(),
            'colors' => ['#0066CC', '#004D99', '#00AA66', '#FF8800', '#0066CC', '#FFB84D'],
        ];

        return view('teacher.dashboard', compact(
            'teacher',
            'classes',
            'totalStudents',
            'readinessData',
            'classScoreData'
        ));
    }

public function profile()
{
    $user = auth()->user();
    $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

    // Prefer users.name, fall back to TeacherProfile name parts
    $fullName = $user->name;
    if (empty($fullName)) {
        $fullName = trim(
            ($teacher->first_name ?? '') . ' ' .
            ($teacher->middle_name ? $teacher->middle_name . ' ' : '') .
            ($teacher->last_name ?? '') .
            ($teacher->suffix_name ? ' ' . $teacher->suffix_name : '')
        );
    }

    return view('teacher.profile', compact('teacher', 'user', 'fullName'));
}

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        $user->name = $request->name;
        $user->save();

        $teacher = TeacherProfile::where('user_id', $user->id)->first();
        if ($teacher) {
            $teacher->employee_id = $request->employee_id;
            $teacher->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        // Delete old picture if exists
        if ($teacher->profile_picture && Storage::disk('public')->exists($teacher->profile_picture)) {
            Storage::disk('public')->delete($teacher->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $teacher->profile_picture = $path;
        $teacher->save();

        return back()->with('success', 'Profile picture updated.');
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();
        $user->password = bcrypt($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }
}
