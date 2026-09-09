<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TeacherClassAssignment;

use App\Models\Classes;
use App\Models\StudentClassRecord;
use App\Models\StudentContentProgress;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionQuiz;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class GlobalAnalyticsController extends Controller
{
    public function index()
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $schoolYear = SchoolYear::getActive();

        if (!$schoolYear) {
            return view('teacher.global-analytics', [
                'summary' => $this->emptySummary(),
                'chartData' => $this->emptyChartData(),
                'hasData' => false,
            ]);
        }

        // ---- 1. Get all class assignments for this teacher ----
        $assignments = TeacherClassAssignment::where('teacher_profile_id', $teacher->id)
            ->with(['class' => function ($q) use ($schoolYear) {
                $q->where('school_year_id', $schoolYear->id);
            }, 'subject'])
            ->get();

        // Filter out assignments where class is null (should not happen)
        $assignments = $assignments->filter(fn($a) => $a->class !== null);

        if ($assignments->isEmpty()) {
            return view('teacher.global-analytics', [
                'summary' => $this->emptySummary(),
                'chartData' => $this->emptyChartData(),
                'hasData' => false,
            ]);
        }

        // Extract class IDs and build a map of class_id -> subject_name
        $classIds = $assignments->pluck('class_id')->unique()->values()->toArray();
        $classSubjectMap = [];
        foreach ($assignments as $assignment) {
            $classSubjectMap[$assignment->class_id] = $assignment->subject->name ?? 'Unknown';
        }

        // ---- 2. Get all student IDs from these classes ----
        $studentIds = StudentClassRecord::whereIn('class_id', $classIds)
            ->pluck('student_profile_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($studentIds)) {
            return view('teacher.global-analytics', [
                'summary' => $this->emptySummary(),
                'chartData' => $this->emptyChartData(),
                'hasData' => false,
            ]);
        }

        // ---- 3. Fetch all progress records for these students ----
        $contentTypes = [
            'App\\Models\\PreAssessment',
            'App\\Models\\PostAssessment',
            'App\\Models\\InterventionQuiz',
        ];

        $progressRecords = StudentContentProgress::whereIn('student_profile_id', $studentIds)
            ->whereIn('content_type', $contentTypes)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($progressRecords->isEmpty()) {
            return view('teacher.global-analytics', [
                'summary' => $this->emptySummary(),
                'chartData' => $this->emptyChartData(),
                'hasData' => false,
            ]);
        }

        // ---- 4. Load content models ----
        $preIds = $progressRecords->where('content_type', 'App\\Models\\PreAssessment')->pluck('content_id')->unique();
        $postIds = $progressRecords->where('content_type', 'App\\Models\\PostAssessment')->pluck('content_id')->unique();
        $quizIds = $progressRecords->where('content_type', 'App\\Models\\InterventionQuiz')->pluck('content_id')->unique();

        $preAssessments = PreAssessment::whereIn('id', $preIds)->with('subject')->get()->keyBy('id');
        $postAssessments = PostAssessment::whereIn('id', $postIds)->with('subject')->get()->keyBy('id');
        $quizzes = InterventionQuiz::whereIn('id', $quizIds)->with('subject')->get()->keyBy('id');

        // ---- 5. Build metadata array ----
        $metadata = [];
        // Also get class->section mapping for section names
        $classSections = Classes::whereIn('id', $classIds)->pluck('section_name', 'id')->toArray();

        foreach ($progressRecords as $record) {
            $contentId = $record->content_id;
            $type = $record->content_type;
            $model = null;
            if ($type === 'App\\Models\\PreAssessment' && isset($preAssessments[$contentId])) {
                $model = $preAssessments[$contentId];
            } elseif ($type === 'App\\Models\\PostAssessment' && isset($postAssessments[$contentId])) {
                $model = $postAssessments[$contentId];
            } elseif ($type === 'App\\Models\\InterventionQuiz' && isset($quizzes[$contentId])) {
                $model = $quizzes[$contentId];
            }
            if (!$model) continue;

            // Get the class (section) for this student
            $studentClass = StudentClassRecord::where('student_profile_id', $record->student_profile_id)
                ->whereIn('class_id', $classIds)
                ->first();
            $sectionName = $studentClass ? ($classSections[$studentClass->class_id] ?? 'Unknown') : 'Unknown';

            $metadata[$record->id] = (object) [
                'subject_name' => $model->subject->name ?? 'Unknown',
                'week' => $model->week ?? null,
                'grade' => $model->grade_level ?? $studentClass->class->grade_level ?? 'N/A',
                'section' => $sectionName,
                'type' => $this->mapContentTypeToExamType($type),
                'score' => $record->score,
                'student_id' => $record->student_profile_id,
                'created_at' => $record->created_at,
            ];
        }

        $metadata = array_filter($metadata, fn($m) => !is_null($m->week));

        // ---- 6. Compute aggregates ----
        $summary = $this->computeSummary($studentIds, $metadata, $progressRecords);
        $chartData = $this->computeChartData($metadata);

        return view('teacher.global-analytics', [
            'summary' => $summary,
            'chartData' => $chartData,
            'hasData' => true,
        ]);
    }


    private function emptySummary()
    {
        return (object) [
            'overallScore' => 0,
            'completionRate' => 0,
            'improvement' => 0,
        ];
    }

    private function emptyChartData()
    {
        return (object) [
            'readiness' => ['advanced' => 0, 'average' => 0, 'belowAverage' => 0],
            'subjects' => [],
            'weeklyProgress' => [],
            'prePost' => [],
            'sections' => [],
        ];
    }

    private function computeSummary($studentIds, $metadata, $progressRecords)
    {
        $totalStudents = count($studentIds);
        $studentsWithPost = collect($studentIds)->filter(function ($sid) use ($progressRecords) {
            return $progressRecords->where('student_profile_id', $sid)
                ->where('content_type', 'App\\Models\\PostAssessment')
                ->isNotEmpty();
        })->count();

        $completionRate = $totalStudents > 0 ? round(($studentsWithPost / $totalStudents) * 100) : 0;

        $postScores = array_filter($metadata, fn($m) => $m->type === 'post' && !is_null($m->score));
        $postScores = array_column($postScores, 'score');
        $overallScore = count($postScores) > 0 ? round(array_sum($postScores) / count($postScores)) : 0;

        $improvements = [];
        foreach ($studentIds as $sid) {
            $pre = $progressRecords->where('student_profile_id', $sid)
                ->where('content_type', 'App\\Models\\PreAssessment')
                ->sortByDesc('created_at')
                ->first();
            $post = $progressRecords->where('student_profile_id', $sid)
                ->where('content_type', 'App\\Models\\PostAssessment')
                ->sortByDesc('created_at')
                ->first();
            if ($pre && $post && !is_null($pre->score) && !is_null($post->score)) {
                $improvements[] = $post->score - $pre->score;
            }
        }
        $avgImprovement = count($improvements) > 0 ? round(array_sum($improvements) / count($improvements)) : 0;

        return (object) [
            'overallScore' => $overallScore,
            'completionRate' => $completionRate,
            'improvement' => $avgImprovement,
        ];
    }

    private function computeChartData($metadata)
    {
        // Readiness distribution
        $readiness = ['advanced' => 0, 'average' => 0, 'belowAverage' => 0];
        $studentPostScores = [];
        foreach ($metadata as $m) {
            if ($m->type === 'post' && !is_null($m->score)) {
                if (!isset($studentPostScores[$m->student_id]) || $m->created_at > $studentPostScores[$m->student_id]['created_at']) {
                    $studentPostScores[$m->student_id] = ['score' => $m->score, 'created_at' => $m->created_at];
                }
            }
        }
        foreach ($studentPostScores as $data) {
            $score = $data['score'];
            if ($score >= 85) $readiness['advanced']++;
            elseif ($score >= 70) $readiness['average']++;
            else $readiness['belowAverage']++;
        }

        // Performance by subject
        $subjectScores = [];
        foreach ($metadata as $m) {
            if ($m->type === 'post' && !is_null($m->score)) {
                $subjectScores[$m->subject_name][] = $m->score;
            }
        }
        $subjectsData = [];
        foreach ($subjectScores as $subject => $scores) {
            $subjectsData[] = [
                'name' => $subject,
                'avgScore' => round(array_sum($scores) / count($scores)),
                'preTest' => null,
                'postTest' => round(array_sum($scores) / count($scores)),
            ];
        }

        // Pre-test scores per subject
        $preScores = [];
        foreach ($metadata as $m) {
            if ($m->type === 'pre' && !is_null($m->score)) {
                $preScores[$m->subject_name][] = $m->score;
            }
        }
        foreach ($subjectsData as &$subject) {
            $subject['preTest'] = isset($preScores[$subject['name']]) ? round(array_sum($preScores[$subject['name']]) / count($preScores[$subject['name']])) : 0;
        }

        // Weekly progress
        $weeklyScores = [];
        foreach ($metadata as $m) {
            if (!is_null($m->score) && $m->week) {
                $weeklyScores[$m->week][] = $m->score;
            }
        }
        ksort($weeklyScores);
        $weeklyProgress = [];
        foreach ($weeklyScores as $week => $scores) {
            $weeklyProgress[] = [
                'week' => 'Week ' . $week,
                'avg' => round(array_sum($scores) / count($scores)),
            ];
        }

        // Pre vs Post per subject
        $prePostData = [];
        foreach ($subjectsData as $subject) {
            $prePostData[] = [
                'subject' => $subject['name'],
                'pre' => $subject['preTest'] ?? 0,
                'post' => $subject['postTest'] ?? 0,
            ];
        }

        // Section performance
        $sectionScores = [];
        foreach ($metadata as $m) {
            if ($m->type === 'post' && !is_null($m->score)) {
                $sectionScores[$m->section][] = $m->score;
            }
        }
        $sectionsData = [];
        foreach ($sectionScores as $section => $scores) {
            $sectionsData[] = [
                'section' => $section,
                'avgScore' => round(array_sum($scores) / count($scores)),
            ];
        }

        return (object) [
            'readiness' => $readiness,
            'subjects' => $subjectsData,
            'weeklyProgress' => $weeklyProgress,
            'prePost' => $prePostData,
            'sections' => $sectionsData,
        ];
    }

    private function mapContentTypeToExamType($contentType)
    {
        return match ($contentType) {
            'App\\Models\\PreAssessment' => 'pre',
            'App\\Models\\PostAssessment' => 'post',
            'App\\Models\\InterventionQuiz' => 'intervention',
            default => 'unknown',
        };
    }
}
