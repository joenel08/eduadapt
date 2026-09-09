<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\InterventionMaterial;
use App\Models\InterventionVideo;
use App\Models\InterventionQuiz;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function create($grade, $term, $subject, $week)
    {
        return view('teacher.content-library.intervention-create', compact('grade', 'term', 'subject', 'week'));
    }

    public function store(Request $request, $grade, $term, $subject, $week)
    {
        $validated = $request->validate([
            'level'          => 'required|in:basic,standard,advanced',
            'materials_json' => 'nullable|json',
            'videos_json'    => 'nullable|json',
            'quizzes_json'   => 'nullable|json',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();
        $subjectModel = Subject::where('name', $subject)
            ->where('grade_level', $grade)
            ->firstOrFail();
        $schoolYear = SchoolYear::getActive();

        $level = $validated['level'];

        // --- Materials ---
        $materials = json_decode($validated['materials_json'] ?? '[]', true);
        InterventionMaterial::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->where('level', $level)
            ->delete();

        if (!empty($materials)) {
            $inserts = [];
            foreach ($materials as $mat) {
                $inserts[] = [
                    'teacher_profile_id' => $teacher->id,
                    'subject_id'         => $subjectModel->id,
                    'school_year_id'     => $schoolYear?->id,
                    'grade_level'        => $grade,
                    'term'               => $term,
                    'week'               => $week,
                    'level'              => $level,
                    'file_name'          => $mat['file_name'] ?? '',
                    'file_path'          => $mat['file_path'] ?? null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
            InterventionMaterial::insert($inserts);
        }

        // --- Videos ---
        $videos = json_decode($validated['videos_json'] ?? '[]', true);
        InterventionVideo::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->where('level', $level)
            ->delete();

        if (!empty($videos)) {
            $inserts = [];
            foreach ($videos as $idx => $video) {
                $inserts[] = [
                    'teacher_profile_id' => $teacher->id,
                    'subject_id'         => $subjectModel->id,
                    'school_year_id'     => $schoolYear?->id,
                    'grade_level'        => $grade,
                    'term'               => $term,
                    'week'               => $week,
                    'level'              => $level,
                    'sequence'           => $idx + 1,
                    'video_type'         => $video['video_type'] ?? 'link',
                    'video_url'          => $video['video_url'] ?? null,
                    'file_name'          => $video['file_name'] ?? null,
                    'file_path'          => null, // file uploads handled separately
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
            InterventionVideo::insert($inserts);
        }

        // --- Quizzes ---
        $quizzes = json_decode($validated['quizzes_json'] ?? '[]', true);
        InterventionQuiz::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->where('level', $level)
            ->delete();

        if (!empty($quizzes)) {
            $inserts = [];
            foreach ($quizzes as $quiz) {
                $inserts[] = [
                    'teacher_profile_id' => $teacher->id,
                    'subject_id'         => $subjectModel->id,
                    'school_year_id'     => $schoolYear?->id,
                    'grade_level'        => $grade,
                    'term'               => $term,
                    'week'               => $week,
                    'level'              => $level,
                    'exam_type'          => $quiz['exam_type'] ?? 'multipleChoice',
                    'input_method'       => $quiz['input_method'] ?? 'upload',
                    'questions'          => $quiz['questions'] ?? '[]',
                    'settings'           => $quiz['settings'] ?? null,
                    'file_name'          => $quiz['file_name'] ?? null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
            InterventionQuiz::insert($inserts);
        }

        return redirect()->route('teacher.content-library.content', [$grade, $term, $subject, $week])
                         ->with('success', 'Intervention saved successfully.');
    }
}