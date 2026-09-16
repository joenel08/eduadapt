<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\StudentClassRecord;
use App\Models\StudentContentProgress;
use App\Models\ContentRelease;
use App\Models\TeacherClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\ContentItem;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionMaterial;
use App\Models\InterventionVideo;
use App\Models\InterventionQuiz;
use App\Models\SchoolYear;

class DashboardController extends Controller
{
    // Map content_release content_type keys to model classes and display names
    protected $contentTypeMap = [
        'learningMaterial' => [
            'model' => ContentItem::class,
            'label' => 'Lesson Material',
            'icon' => 'fa-book',
        ],
        'preAssessment' => [
            'model' => PreAssessment::class,
            'label' => 'Pre-Assessment',
            'icon' => 'fa-clipboard-list',
        ],
        'postAssessment' => [
            'model' => PostAssessment::class,
            'label' => 'Post-Assessment',
            'icon' => 'fa-star',
        ],
        'interventionMaterial' => [
            'model' => InterventionMaterial::class,
            'label' => 'Intervention Material',
            'icon' => 'fa-file-lines',
        ],
        'interventionVideo' => [
            'model' => InterventionVideo::class,
            'label' => 'Intervention Video',
            'icon' => 'fa-video',
        ],
        'interventionQuiz' => [
            'model' => InterventionQuiz::class,
            'label' => 'Mini Quiz',
            'icon' => 'fa-list-check',
        ],
    ];

    public function index()
    {
        $user = auth()->user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();

        // --- 1. Get all class IDs this student is enrolled in ---
        $classIds = StudentClassRecord::where('student_profile_id', $student->id)->pluck('class_id');

        // --- 2. Enrolled subjects ---
        $enrolledSubjects = TeacherClassAssignment::whereIn('class_id', $classIds)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->count();

        // --- 3. Get all content releases for these classes ---
        $releases = ContentRelease::whereIn('class_id', $classIds)->get();

        // --- 4. Get all progress records for this student ---
        $progressRecords = StudentContentProgress::where('student_profile_id', $student->id)->get();

        // --- 5. Compute pending tasks: releases with NO matching progress record ---
        $pendingTasks = 0;
        $releasesWithNoProgress = [];

        // Build a set of (content_type, content_id) from progress records (full class names)
        $progressSet = $progressRecords->map(function ($p) {
            return $p->content_type . '|' . $p->content_id;
        })->toArray();

        foreach ($releases as $release) {
            // Convert release's short type to full model class using map
            $modelClass = $this->contentTypeMap[$release->content_type]['model'] ?? null;
            if (!$modelClass) {
                continue; // skip unknown types
            }
            $fullType = $modelClass; // e.g., 'App\Models\ContentItem'
            $key = $fullType . '|' . $release->content_id;

            if (!in_array($key, $progressSet)) {
                $pendingTasks++;
                $releasesWithNoProgress[] = $release;
            }
        }

        // --- 6. Completed: count progress records with status = 'completed' ---
        $completedCount = $progressRecords->where('status', 'completed')->count();

        // --- 7. Average score ---
        $scores = $progressRecords->where('status', 'completed')
            ->whereNotNull('score')
            ->pluck('score')
            ->toArray();
        $averageScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) . '%' : 'N/A';

        // --- 8. Build class list for bottom section ---
        $assignments = TeacherClassAssignment::whereIn('class_id', $classIds)
            ->with(['class.schoolYear', 'class.studentClassRecords', 'subject'])
            ->get();

        $classes = $assignments->groupBy('class_id')->map(function ($group) {
            $first = $group->first();
            $class = $first->class;
            $subject = $first->subject;
            return (object) [
                'id' => $class->id,
                'grade_level' => $class->grade_level,
                'section_name' => $class->section_name,
                'school_year' => $class->schoolYear->year,
                'student_count' => $class->studentClassRecords->count(),
                'first_subject_id' => $subject?->id,
                'subject_name' => $subject?->name ?? 'General',
            ];
        })->values();

        // --- 9. Build notifications grouped by subject ---
        $notifications = [];

        // Helper to get class/subject info
        $getClassSubject = function ($release) {
            $class = Classes::find($release->class_id);
            $subject = Subject::find($release->subject_id);
            return [
                'class' => $class,
                'subject' => $subject,
                'class_name' => $class ? $class->grade_level . ' - ' . $class->section_name : 'Unknown',
                'subject_name' => $subject ? $subject->name : 'General',
            ];
        };

        // Group pending releases by subject_id
        $grouped = [];
        foreach ($releasesWithNoProgress as $release) {
            $info = $getClassSubject($release);
            $subjectId = $release->subject_id ?? 0;
            if (!isset($grouped[$subjectId])) {
                $grouped[$subjectId] = [
                    'subject_name' => $info['subject_name'],
                    'class_name' => $info['class_name'],
                    'class_id' => $release->class_id,
                    'subject_id' => $subjectId,
                    'items' => [],
                ];
            }

            $typeInfo = $this->contentTypeMap[$release->content_type] ?? null;
            if (!$typeInfo) continue;

            // Get content title if possible
            $contentTitle = '';
            try {
                $modelClass = $typeInfo['model'];
                if (class_exists($modelClass)) {
                    $content = $modelClass::find($release->content_id);
                    if ($content && isset($content->title)) {
                        $contentTitle = $content->title;
                    }
                }
            } catch (\Exception $e) {
                // ignore
            }
            $displayTitle = $contentTitle ?: $typeInfo['label'];

            $grouped[$subjectId]['items'][] = [
                'type_name' => $typeInfo['label'],
                'icon' => $typeInfo['icon'],
                'title' => $displayTitle,
                'link' => route('student.class.details', ['classId' => $release->class_id, 'subjectId' => $release->subject_id]),
            ];
        }

        // Build final notification cards (one per subject with pending items)
        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'];
        $colorIndex = 0;
        foreach ($grouped as $subjectId => $data) {
            $color = $colors[$colorIndex % count($colors)];
            $colorIndex++;
            $notifications[] = [
                'subject_name' => $data['subject_name'],
                'class_name' => $data['class_name'],
                'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'items' => $data['items'],
                'color' => $color,
            ];
        }

        // Sort by subject name
        usort($notifications, function ($a, $b) {
            return strcmp($a['subject_name'], $b['subject_name']);
        });

          $schoolYears = SchoolYear::orderBy('year', 'desc')->get();
        $activeSchoolYear = SchoolYear::getActive();

        return view('student.dashboard', compact(
             'schoolYears',
            'activeSchoolYear',
            'student',
            'classes',
            'enrolledSubjects',
            'pendingTasks',
            'completedCount',
            'averageScore',
            'notifications'
        ));
    }

    public function profile()
    {
        $user = auth()->user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();
        return view('student.profile', compact('student', 'user'));
    }
}