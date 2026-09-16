<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\TeacherClassAssignment;
use App\Models\StudentContentProgress;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionQuiz;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class ClassDetailsController extends Controller
{
    public function show($assignmentId)
    {
        // ---- Load the assignment, then derive class + subject from it ----
        $assignment = TeacherClassAssignment::with([
            'class.schoolYear',
            'class.studentClassRecords.studentProfile',
            'subject',
        ])->findOrFail($assignmentId);

        $class   = $assignment->class;
        $subject = $assignment->subject;

        if (!$subject) {
            return back()->with('error', 'No subject assigned to this class.');
        }

        $schoolYear = SchoolYear::where('is_active', true)->first();
        if (!$schoolYear) {
            return back()->with('error', 'No active school year.');
        }

        // Same hash as ClassController::index() → same code guaranteed
        $class->code = 'CLS-' . strtoupper(
            substr(md5($class->id . $assignment->subject_id), 0, 8)
        );

        // ---- Get students ----
        $students = $this->getStudentsWithProgress($class, $schoolYear, $subject);

        // ---- Stats ----
        $stats = [
            'total'         => $students->count(),
            'advanced'      => $students->where('category', 'advanced')->count(),
            'average'       => $students->where('category', 'average')->count(),
            'below_average' => $students->where('category', 'below_average')->count(),
        ];

        // ---- Get all progress records for this class + subject ----
        $studentIds = $students->pluck('id')->toArray();

        $progressRecords = StudentContentProgress::whereIn('student_profile_id', $studentIds)
            ->whereIn('content_type', [
                'App\\Models\\PreAssessment',
                'App\\Models\\PostAssessment',
                'App\\Models\\InterventionQuiz',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $preIds  = $progressRecords->where('content_type', 'App\\Models\\PreAssessment')->pluck('content_id')->unique();
        $postIds = $progressRecords->where('content_type', 'App\\Models\\PostAssessment')->pluck('content_id')->unique();
        $quizIds = $progressRecords->where('content_type', 'App\\Models\\InterventionQuiz')->pluck('content_id')->unique();

        $preAssessments  = PreAssessment::whereIn('id', $preIds)->get()->keyBy('id');
        $postAssessments = PostAssessment::whereIn('id', $postIds)->get()->keyBy('id');
        $quizzes         = InterventionQuiz::whereIn('id', $quizIds)->get()->keyBy('id');

        $weekMap = [];
        foreach ($progressRecords as $record) {
            $contentId = $record->content_id;
            $type      = $record->content_type;
            $week      = null;

            if ($type === 'App\\Models\\PreAssessment' && isset($preAssessments[$contentId])) {
                $week = $preAssessments[$contentId]->week;
            } elseif ($type === 'App\\Models\\PostAssessment' && isset($postAssessments[$contentId])) {
                $week = $postAssessments[$contentId]->week;
            } elseif ($type === 'App\\Models\\InterventionQuiz' && isset($quizzes[$contentId])) {
                $week = $quizzes[$contentId]->week;
            }

            if ($week) {
                $weekMap[$record->id] = $week;
            }
        }

        $weeklyData     = [];
        $studentNameMap = [];

        foreach ($students as $student) {
            $studentNameMap[$student->id] = $student->name;
        }

        foreach ($progressRecords as $record) {
            $studentId   = $record->student_profile_id;
            $studentName = $studentNameMap[$studentId] ?? 'Unknown';
            $week        = $weekMap[$record->id] ?? null;
            if (!$week) continue;

            $examType = $this->mapContentTypeToExamType($record->content_type);
            $score    = $record->score;

            if (!isset($weeklyData[$studentName])) {
                $weeklyData[$studentName] = [];
            }
            if (!isset($weeklyData[$studentName][$week])) {
                $weeklyData[$studentName][$week] = [
                    'pre'          => null,
                    'post'         => null,
                    'intervention' => null,
                    'category'     => 'not_assessed',
                ];
            }
            $weeklyData[$studentName][$week][$examType] = $score;
        }

        foreach ($weeklyData as $studentName => &$weeks) {
            foreach ($weeks as $week => &$data) {
                $post = $data['post'] ?? null;
                $data['category'] = $this->determineCategory($post);
            }
        }

        // ---- Exam records for the verification tab ----
        $examRecords = $progressRecords->map(function ($record) use ($studentNameMap, $weekMap) {
            $studentName = $studentNameMap[$record->student_profile_id] ?? 'Unknown';
            $examType    = $this->mapContentTypeToExamType($record->content_type);
            $week        = $weekMap[$record->id] ?? 'N/A';
            return (object) [
                'code'         => 'EXM-' . strtoupper(substr(md5($record->id . $record->student_profile_id), 0, 8)),
                'student_name' => $studentName,
                'student_id'   => $record->student_profile_id,
                'exam_type'    => $examType,
                'score'        => $record->score,
                'week'         => $week,
                'created_at'   => $record->created_at,
                'video_path'   => $record->video_path,
            ];
        });

        return view('teacher.class-details', compact(
            'class',
            'students',
            'stats',
            'examRecords',
            'subject',
            'weeklyData',
            'studentNameMap',
        ));
    }

    private function getStudentsWithProgress($class, $schoolYear, $subject)
    {
        $students = collect();

        foreach ($class->studentClassRecords as $record) {
            $studentProfile = $record->studentProfile;
            if (!$studentProfile) continue;

            $fullName = trim(
                ($studentProfile->first_name ?? '') . ' ' .
                ($studentProfile->middle_name ? $studentProfile->middle_name . ' ' : '') .
                ($studentProfile->last_name ?? '') .
                ($studentProfile->suffix_name ? ' ' . $studentProfile->suffix_name : '')
            ) ?: 'Unknown';

            $latestPre = StudentContentProgress::where('student_profile_id', $studentProfile->id)
                ->where('content_type', 'App\\Models\\PreAssessment')
                ->orderBy('created_at', 'desc')
                ->first();

            $latestPost = StudentContentProgress::where('student_profile_id', $studentProfile->id)
                ->where('content_type', 'App\\Models\\PostAssessment')
                ->orderBy('created_at', 'desc')
                ->first();

            $latestIntervention = StudentContentProgress::where('student_profile_id', $studentProfile->id)
                ->where('content_type', 'App\\Models\\InterventionQuiz')
                ->orderBy('created_at', 'desc')
                ->first();

            $preScore          = $latestPre ? $latestPre->score : null;
            $postScore         = $latestPost ? $latestPost->score : null;
            $interventionScore = $latestIntervention ? $latestIntervention->score : null;

            $category = $this->determineCategory($postScore);

            $students->push((object) [
                'id'                 => $studentProfile->id,
                'name'               => $fullName,
                'lrn'                => $studentProfile->lrn ?? 'N/A',
                'category'           => $category,
                'pre_score'          => $preScore,
                'post_score'         => $postScore,
                'intervention_score' => $interventionScore,
                'initials'           => $this->getInitials($fullName),
            ]);
        }

        return $students;
    }

    private function determineCategory($score)
    {
        if (is_null($score)) return 'not_assessed';
        if ($score >= 85) return 'advanced';
        if ($score >= 70) return 'average';
        return 'below_average';
    }

    private function getInitials($name)
    {
        if (empty($name)) return 'NA';
        $parts = array_filter(explode(' ', trim($name)));
        $initials = '';
        foreach ($parts as $part) {
            $initials .= strtoupper($part[0]);
        }
        return substr($initials, 0, 2) ?: 'NA';
    }

    private function mapContentTypeToExamType($contentType)
    {
        return match ($contentType) {
            'App\\Models\\PreAssessment'    => 'pre',
            'App\\Models\\PostAssessment'   => 'post',
            'App\\Models\\InterventionQuiz' => 'intervention',
            default                          => 'unknown',
        };
    }
}