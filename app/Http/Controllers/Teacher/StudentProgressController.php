<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\StudentProfile;
use App\Models\StudentContentProgress;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionQuiz;

class StudentProgressController extends Controller
{
    public function show($classId, $studentId)
    {
        $class = Classes::with('schoolYear')->findOrFail($classId);
        $student = StudentProfile::findOrFail($studentId);

        // Full name
        $fullName = trim(
            ($student->first_name ?? '') . ' ' .
            ($student->middle_name ? $student->middle_name . ' ' : '') .
            ($student->last_name ?? '') .
            ($student->suffix_name ? ' ' . $student->suffix_name : '')
        ) ?: 'Unknown';

        // All progress records for this student
        $progressRecords = StudentContentProgress::where('student_profile_id', $student->id)
            ->whereIn('content_type', [
                'App\\Models\\PreAssessment',
                'App\\Models\\PostAssessment',
                'App\\Models\\InterventionQuiz',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Map content IDs to their assessment models for week info
        $preIds  = $progressRecords->where('content_type', 'App\\Models\\PreAssessment')->pluck('content_id')->unique();
        $postIds = $progressRecords->where('content_type', 'App\\Models\\PostAssessment')->pluck('content_id')->unique();
        $quizIds = $progressRecords->where('content_type', 'App\\Models\\InterventionQuiz')->pluck('content_id')->unique();

        $preAssessments  = PreAssessment::whereIn('id', $preIds)->get()->keyBy('id');
        $postAssessments = PostAssessment::whereIn('id', $postIds)->get()->keyBy('id');
        $quizzes         = InterventionQuiz::whereIn('id', $quizIds)->get()->keyBy('id');

        // Build the exam records for this student
        $examRecords = $progressRecords->map(function ($record) use ($preAssessments, $postAssessments, $quizzes) {
            $contentId = $record->content_id;
            $type      = $record->content_type;

            $week = null;
            if ($type === 'App\\Models\\PreAssessment' && isset($preAssessments[$contentId])) {
                $week = $preAssessments[$contentId]->week;
            } elseif ($type === 'App\\Models\\PostAssessment' && isset($postAssessments[$contentId])) {
                $week = $postAssessments[$contentId]->week;
            } elseif ($type === 'App\\Models\\InterventionQuiz' && isset($quizzes[$contentId])) {
                $week = $quizzes[$contentId]->week;
            }

            $examType = match ($type) {
                'App\\Models\\PreAssessment'    => 'Pre-Assessment',
                'App\\Models\\PostAssessment'   => 'Post-Assessment',
                'App\\Models\\InterventionQuiz' => 'Intervention Quiz',
                default                          => 'Assessment',
            };

            return (object) [
                'code'       => 'EXM-' . strtoupper(substr(md5($record->id . $record->student_profile_id), 0, 8)),
                'exam_type'  => $examType,
                'score'      => $record->score,
                'week'       => $week ?? 'N/A',
                'created_at' => $record->created_at,
                'video_path' => $record->video_path,
            ];
        });

        return view('teacher.student-progress', compact(
            'class',
            'student',
            'fullName',
            'examRecords'
        ));
    }
}