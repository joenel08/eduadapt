<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\InterventionQuiz;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\SchoolYear;
use App\Models\ContentRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InterventionQuizController extends Controller
{
    // =================================================================
    // PAGE
    // =================================================================
    public function createPage($grade, $term, $subject, $week)
    {
        return view('teacher.content-library.intervention-quiz-create',
            compact('grade', 'term', 'subject', 'week'));
    }

    // =================================================================
    // STORE (create-or-update one quiz per level per week)
    // =================================================================
    public function storePage(Request $request, $grade, $term, $subject, $week)
    {
        $request->validate([
            'level'        => 'required|in:basic,standard,advanced',
            'exam_type'    => 'required|in:multipleChoice,trueFalse,matchingType,mixed',
            'input_method' => 'required|in:upload,manual',
            'questions'    => 'required|json',
            'settings'     => 'nullable|json',
            'file'         => 'nullable|file|mimes:xlsx,xls|max:20480',
        ]);

        $teacher      = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();
        $schoolYear   = SchoolYear::getActive();

        $level     = $request->level;
        $questions = json_decode($request->questions, true) ?: [];
        $settings  = json_decode($request->settings ?? '{}', true) ?: [];

        // -----------------------------------------------------------------
        // Process manual images (per-level folder)
        // -----------------------------------------------------------------
        if ($request->input_method === 'manual') {
            $questions = $this->processQuizImages(
                $questions,
                $request,
                $teacher->id,
                $grade,
                $term,
                $subject,
                $week,
                $level
            );
        }

        // -----------------------------------------------------------------
        // Store uploaded Excel (if any)
        // -----------------------------------------------------------------
        $fileName = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder   = sprintf(
                'intervention_quizzes/%d/%s/%s/%s/%s/%s',
                $teacher->id, $grade, $term, $subject, $week, $level
            );
            $file->storeAs($folder, $fileName, 'public');
        }

        // -----------------------------------------------------------------
        // One quiz per (teacher, subject, grade, term, week, level)
        // Update in place — do NOT delete-then-create (would lose id / releases)
        // -----------------------------------------------------------------
        $existing = InterventionQuiz::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->where('level', $level)
            ->first();

        if ($existing) {
            // If a new Excel replaced the old one, clean up the old file
            if ($fileName && $existing->file_name && $existing->file_name !== $fileName) {
                $oldFolder = sprintf(
                    'intervention_quizzes/%d/%s/%s/%s/%s/%s',
                    $teacher->id, $grade, $term, $subject, $week, $level
                );
                $oldPath = $oldFolder . '/' . $existing->file_name;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $existing->update([
                'exam_type'    => $request->exam_type,
                'input_method' => $request->input_method,
                'questions'    => $questions,
                'settings'     => $settings,
                'file_name'    => $fileName ?: $existing->file_name,
            ]);
        } else {
            InterventionQuiz::create([
                'teacher_profile_id' => $teacher->id,
                'subject_id'         => $subjectModel->id,
                'school_year_id'     => $schoolYear?->id,
                'grade_level'        => $grade,
                'term'               => $term,
                'week'               => $week,
                'level'              => $level,
                'exam_type'          => $request->exam_type,
                'input_method'       => $request->input_method,
                'questions'          => $questions,
                'settings'           => $settings,
                'file_name'          => $fileName,
            ]);
        }

        return redirect()
            ->route('teacher.content-library.content', [$grade, $term, $subject, $week])
            ->with('success', 'Mini-quiz saved successfully.');
    }

    // =================================================================
    // FETCH — used by core.js to populate content cards
    // =================================================================
    public function fetch($grade, $term, $subject, $week)
    {
        $teacher      = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->first();

        if (!$subjectModel) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        $items = InterventionQuiz::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => $this->formatItem($item));

        return response()->json(['success' => true, 'quizzes' => $items]);
    }

    // =================================================================
    // DELETE — called by the content card's Delete menu item
    // =================================================================
    public function destroy($id)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();

        $item = InterventionQuiz::where('id', $id)
            ->where('teacher_profile_id', $teacher->id)
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Quiz not found.'], 404);
        }

        // Remove the uploaded Excel file, if any
        if ($item->file_name) {
            $folder = sprintf(
                'intervention_quizzes/%d/%s/%s/%s/%s/%s',
                $teacher->id,
                $item->grade_level,
                $item->term,
                optional($item->subject)->name ?? '',
                $item->week,
                $item->level
            );
            $path = $folder . '/' . $item->file_name;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Remove any release rows that pointed to this quiz
        ContentRelease::where('teacher_profile_id', $teacher->id)
            ->where('content_type', 'interventionQuiz')
            ->where('content_id', $item->id)
            ->delete();

        $item->delete();

        return response()->json(['success' => true]);
    }

    // =================================================================
    // PRIVATE HELPERS
    // =================================================================

    /**
     * Shape a quiz row for JSON responses.
     * Matches the keys core.js expects (questions, exam_type, timer, etc.).
     */
    private function formatItem(InterventionQuiz $item): array
    {
        $settings = $item->settings;

        // Defensive decode in case the model has no cast
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?: [];
        }
        if (!is_array($settings)) {
            $settings = [];
        }

        $questions = $item->questions;
        if (is_string($questions)) {
            $decoded = json_decode($questions, true);
            $questions = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($questions)) {
            $questions = [];
        }

        return [
            'id'                => $item->id,
            'title'             => 'Intervention Quiz (' . ucfirst((string) $item->level) . ')',
            'level'             => $item->level,
            'exam_type'         => $item->exam_type,
            'input_method'      => $item->input_method,
            'questions'         => $questions,
            'settings'          => $settings,
            'timer'             => $settings['timer'] ?? '00:00:00',
            'shuffle_questions' => $settings['shuffle_questions'] ?? false,
            'shuffle_choices'   => $settings['shuffle_choices'] ?? false,
            'due_date'          => $settings['due_date'] ?? null,
            'file_name'         => $item->file_name,
            'created_at'        => optional($item->created_at)->toISOString(),
        ];
    }

    /**
     * Handle question-image and choice-image uploads for a manual quiz.
     * Stores under intervention_quizzes/{teacher}/{grade}/{term}/{subject}/{week}/{level}/images/
     */
    private function processQuizImages(
        $questions,
        Request $request,
        $teacherId,
        $grade,
        $term,
        $subject,
        $week,
        $level = 'standard'
    ) {
        if (empty($questions) || !is_array($questions)) {
            return $questions;
        }

        $basePath = sprintf(
            'intervention_quizzes/%d/%s/%s/%s/%s/%s/images',
            $teacherId, $grade, $term, $subject, $week, $level
        );

        foreach ($questions as $index => &$q) {
            $qNum = $index + 1;

            // Question image
            $fileKey = "question_image_{$qNum}";
            if ($request->hasFile($fileKey)) {
                $file     = $request->file($fileKey);
                $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $q['image'] = $file->storeAs($basePath, $fileName, 'public');
            }

            // Choice images (MC only)
            if (($q['type'] ?? '') === 'multipleChoice' && !empty($q['choices']) && is_array($q['choices'])) {
                $choiceImages = [];
                for ($ci = 0; $ci < count($q['choices']); $ci++) {
                    $choiceFileKey = "choice_image_{$qNum}_{$ci}";
                    if ($request->hasFile($choiceFileKey)) {
                        $file     = $request->file($choiceFileKey);
                        $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                        $choiceImages[] = $file->storeAs($basePath, $fileName, 'public');
                    } else {
                        // Preserve previously saved image on partial edits
                        $choiceImages[] = $q['choiceImages'][$ci] ?? null;
                    }
                }
                $q['choiceImages'] = $choiceImages;
            }
        }
        unset($q);  // break the reference

        return $questions;
    }
}