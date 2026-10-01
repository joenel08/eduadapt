<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\StudentClassRecord;
use App\Models\TeacherClassAssignment;
use App\Models\User;

class NotificationService
{
    /**
     * Teacher released content → notify every enrolled student.
     */
    public static function notifyContentReleased($release, $contentTitle = null, $contentType = 'material')
    {
        // Get all students enrolled in this class
        $studentRecords = StudentClassRecord::with('studentProfile.user')
            ->where('class_id', $release->class_id)
            ->get();

        // Get the class + subject for context
        $class   = \App\Models\Classes::find($release->class_id);
        $subject = \App\Models\Subject::find($release->subject_id);

        $label = match ($contentType) {
            'material'      => 'Lesson Material',
            'pre'           => 'Pre-Assessment',
            'post'          => 'Post-Assessment',
            'intervention'  => 'Intervention',
            'quiz'          => 'Mini Quiz',
            default         => 'Content',
        };

        $title = "New {$label} released";
        $message = ($contentTitle ? "\"{$contentTitle}\" " : '') .
            "is now available in " .
            ($class ? $class->grade_level . ' - ' . $class->section_name : 'your class') .
            ($subject ? ' • ' . $subject->name : '');

        $link = route('student.class.details', [
            'classId'   => $release->class_id,
            'subjectId' => $release->subject_id,
        ]);

        foreach ($studentRecords as $record) {
            $studentProfile = $record->studentProfile;
            if (!$studentProfile) continue;

            Notification::create([
                'user_id'      => $studentProfile->id,   // ← changed: profile ID, not user ID
                'type'         => 'content_released',
                'title'        => $title,
                'message'      => $message,
                'link'         => $link,
                'icon'         => 'fa-book-open',
                'color'        => 'blue',
                'available_at' => $release->release_date ?? now(),
            ]);
        }
    }

    /**
     * Student viewed material or submitted assessment → notify the teacher.
     */
   public static function notifyStudentAction($student, $classId, $actionType, $contentTitle = null)
{
    // Find the teacher who owns this class
    $assignment = TeacherClassAssignment::with('teacherProfile.user')
        ->where('class_id', $classId)
        ->first();

    $teacherUser = $assignment->teacherProfile->user ?? null;
    if (!$teacherUser) return;

    $class = \App\Models\Classes::find($classId);
    $studentName = trim(
        ($student->first_name ?? '') . ' ' .
        ($student->middle_name ? $student->middle_name . ' ' : '') .
        ($student->last_name ?? '')
    ) ?: 'A student';

    $config = match ($actionType) {
        'material_viewed' => [
            'title' => 'Student viewed material',
            'msg'   => "{$studentName} viewed " . ($contentTitle ? "\"{$contentTitle}\"" : 'a material'),
            'icon'  => 'fa-eye',
            'color' => 'blue',
        ],
        'assessment_submitted' => [
            'title' => 'Assessment submitted',
            'msg'   => "{$studentName} submitted " . ($contentTitle ? "\"{$contentTitle}\"" : 'an assessment'),
            'icon'  => 'fa-clipboard-check',
            'color' => 'green',
        ],
        'quiz_submitted' => [
            'title' => 'Quiz submitted',
            'msg'   => "{$studentName} completed " . ($contentTitle ? "\"{$contentTitle}\"" : 'the mini quiz'),
            'icon'  => 'fa-list-check',
            'color' => 'green',
        ],
        default => [
            'title' => 'Student activity',
            'msg'   => "{$studentName} performed an action",
            'icon'  => 'fa-info-circle',
            'color' => 'blue',
        ],
    };

    // ✅ Fixed: correct name + correct parameter shape
    $link = $assignment
        ? route('teacher.class-details.show', ['assignment' => $assignment->id])
        : null;

    Notification::create([
        'user_id'      => $teacherUser->id,
        'type'         => $actionType,
        'title'        => $config['title'],
        'message'      => $config['msg'] . ($class ? ' in ' . $class->grade_level . ' - ' . $class->section_name : ''),
        'link'         => $link,
        'icon'         => $config['icon'],
        'color'        => $config['color'],
        'available_at' => now(),
    ]);
}
}
