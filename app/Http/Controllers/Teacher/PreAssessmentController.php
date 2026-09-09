<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\PreAssessment;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PreAssessmentController extends Controller
{
    // --- Full‑page creation methods ---

    public function create($grade, $term, $subject, $week)
    {
        return view('teacher.content-library.pre-assessment-create', compact('grade', 'term', 'subject', 'week'));
    }

    public function storePage(Request $request, $grade, $term, $subject, $week)
    {
        $request->validate([
            'input_method' => 'required|in:upload,manual',
            'questions'    => 'required|json',
            'settings'     => 'nullable|json',
            'file'         => 'nullable|file|mimes:xlsx,xls|max:20480',
            'exam_type'    => 'required|in:multipleChoice,trueFalse,matchingType,mixed',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subjectModel = Subject::where('name', $subject)
            ->where('grade_level', $grade)
            ->firstOrFail();

        $schoolYear = SchoolYear::getActive();
        if (!$schoolYear) {
            return back()->withErrors(['error' => 'No active school year set.']);
        }

        $questions = json_decode($request->questions, true);
        $settings = json_decode($request->settings ?? '{}', true);

        // --- Process manual images ---
        if ($request->input_method === 'manual') {
            $questions = $this->processQuestionImages($questions, $request, $teacher->id, $grade, $term, $subject, $week);
        }

        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = sprintf('pre_assessments/%d/%s/%s/%s/%s', $teacher->id, $grade, $term, $subject, $week);
            $file->storeAs($folder, $fileName, 'public');
        }

        $existing = PreAssessment::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->first();

        if ($existing) {
            $existing->update([
                'input_method' => $request->input_method,
                'questions'    => $questions,
                'settings'     => $settings,
                'file_name'    => $fileName,
                'exam_type'    => $request->exam_type,
            ]);
        } else {
            PreAssessment::create([
                'teacher_profile_id' => $teacher->id,
                'subject_id'         => $subjectModel->id,
                'school_year_id'     => $schoolYear->id,
                'grade_level'        => $grade,
                'term'               => $term,
                'week'               => $week,
                'input_method'       => $request->input_method,
                'questions'          => $questions,
                'settings'           => $settings,
                'file_name'          => $fileName,
                'exam_type'          => $request->exam_type,
            ]);
        }

        return redirect()->route('teacher.content-library.content', [$grade, $term, $subject, $week])
                         ->with('success', 'Pre‑Assessment saved successfully.');
    }

    // --- AJAX methods ---

    public function store(Request $request)
    {
        $request->validate([
            'input_method' => 'required|in:upload,manual',
            'questions' => 'required|json',
            'settings' => 'nullable|json',
            'grade_level' => 'required|string',
            'term' => 'required|string',
            'subject' => 'required|string',
            'week' => 'required|string',
            'file' => 'nullable|file|mimes:xlsx,xls|max:20480',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subject = Subject::where('name', $request->subject)
            ->where('grade_level', $request->grade_level)
            ->first();
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        $schoolYear = SchoolYear::getActive();
        if (!$schoolYear) {
            return response()->json(['success' => false, 'message' => 'No active school year set.'], 400);
        }

        $questions = json_decode($request->questions, true);
        $settings = json_decode($request->settings, true) ?? [];

        // --- Process manual images ---
        if ($request->input_method === 'manual') {
            $questions = $this->processQuestionImages($questions, $request, $teacher->id, $request->grade_level, $request->term, $request->subject, $request->week);
        }

        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = sprintf('pre_assessments/%d/%s/%s/%s/%s', $teacher->id, $request->grade_level, $request->term, $request->subject, $request->week);
            $file->storeAs($folder, $fileName, 'public');
        }

        $existing = PreAssessment::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->where('grade_level', $request->grade_level)
            ->where('term', $request->term)
            ->where('week', $request->week)
            ->first();
        $examType = $request->input('exam_type', 'multipleChoice');
        if ($existing) {
            $existing->update([
                'input_method' => $request->input_method,
                'questions' => $questions,
                'settings' => $settings,
                'file_name' => $fileName,
                'exam_type' => $examType,
            ]);
            $preAssessment = $existing;
        } else {
            $preAssessment = PreAssessment::create([
                'teacher_profile_id' => $teacher->id,
                'subject_id' => $subject->id,
                'school_year_id' => $schoolYear->id,
                'grade_level' => $request->grade_level,
                'term' => $request->term,
                'week' => $request->week,
                'input_method' => $request->input_method,
                'questions' => $questions,
                'settings' => $settings,
                'file_name' => $fileName,
                'exam_type' => $examType,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pre-Assessment saved.',
            'preAssessment' => $this->formatItem($preAssessment),
        ]);
    }

    // --- Helper to process uploaded images ---
    private function processQuestionImages($questions, $request, $teacherId, $grade, $term, $subject, $week)
    {
        if (empty($questions)) return $questions;

        $basePath = sprintf('pre_assessments/%d/%s/%s/%s/%s/images', $teacherId, $grade, $term, $subject, $week);
        $storage = Storage::disk('public');

        foreach ($questions as $index => &$q) {
            // Question image
            $qNum = $index + 1;
            $fileKey = "question_image_{$qNum}";
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs($basePath, $fileName, 'public');
                $q['image'] = $path;
            }

            // Choice images (only for multipleChoice)
            if (isset($q['type']) && $q['type'] === 'multipleChoice' && isset($q['choices'])) {
                $choiceImages = [];
                for ($ci = 0; $ci < count($q['choices']); $ci++) {
                    $choiceFileKey = "choice_image_{$qNum}_{$ci}";
                    if ($request->hasFile($choiceFileKey)) {
                        $file = $request->file($choiceFileKey);
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs($basePath, $fileName, 'public');
                        $choiceImages[] = $path;
                    } else {
                        $choiceImages[] = null;
                    }
                }
                $q['choiceImages'] = $choiceImages;
            }
        }

        return $questions;
    }

    public function fetch($grade, $term, $subject, $week)
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->first();
        if (!$subjectModel) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        $items = PreAssessment::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => $this->formatItem($item));

        return response()->json(['success' => true, 'preAssessments' => $items]);
    }

    public function destroy($id)
    {
        $item = PreAssessment::findOrFail($id);
        // Optionally delete associated image files (you can implement if needed)
        $item->delete();
        return response()->json(['success' => true]);
    }

    private function formatItem($item)
    {
        $settings = $item->settings ?? [];
        return [
            'id' => $item->id,
            'title' => 'Pre-Assessment',
            'exam_type' => $item->exam_type,
            // 'status' => $settings['status'] ?? 'open',
            'input_method' => $item->input_method,
            'questions' => $item->questions,
            'timer' => $settings['timer'] ?? '00:00:00',
            // 'due_date' => $settings['due_date'] ?? null,
            'shuffle_questions' => $settings['shuffle_questions'] ?? false,
            'shuffle_choices' => $settings['shuffle_choices'] ?? false,
            'file_name' => $item->file_name,
            'created_at' => $item->created_at->toISOString(),
        ];
    }
}