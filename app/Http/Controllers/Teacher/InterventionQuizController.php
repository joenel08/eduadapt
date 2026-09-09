<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\InterventionQuiz;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class InterventionQuizController extends Controller
{
    public function createPage($grade, $term, $subject, $week)
{
    return view('teacher.content-library.intervention-quiz-create', compact('grade', 'term', 'subject', 'week'));
}

public function storePage(Request $request, $grade, $term, $subject, $week)
{
    $request->validate([
        'level'         => 'required|in:basic,standard,advanced',
        'exam_type'     => 'required|in:multipleChoice,trueFalse,matchingType,mixed',
        'input_method'  => 'required|in:upload,manual',
        'questions'     => 'required|json',
        'settings'      => 'nullable|json',
        'file'          => 'nullable|file|mimes:xlsx,xls|max:20480',
    ]);

    $user = auth()->user();
    $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();
    $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();
    $schoolYear = SchoolYear::getActive();

    $level = $request->level;
    $questions = json_decode($request->questions, true);
    $settings = json_decode($request->settings ?? '{}', true);

    // Process images if manual
    if ($request->input_method === 'manual') {
        $questions = $this->processQuizImages($questions, $request, $teacher->id, $grade, $term, $subject, $week);
    }

    $fileName = null;
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $folder = sprintf('intervention_quizzes/%d/%s/%s/%s/%s/%s', $teacher->id, $grade, $term, $subject, $week, $level);
        $file->storeAs($folder, $fileName, 'public');
    }

    // Delete existing quiz for this level
    InterventionQuiz::where('teacher_profile_id', $teacher->id)
        ->where('subject_id', $subjectModel->id)
        ->where('grade_level', $grade)
        ->where('term', $term)
        ->where('week', $week)
        ->where('level', $level)
        ->delete();

    // Create new quiz
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

    return redirect()->route('teacher.content-library.content', [$grade, $term, $subject, $week])
                     ->with('success', 'Mini‑quiz saved successfully.');
}

// reuse the processQuizImages helper from previous answer
    public function fetch($grade, $term, $subject, $week)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();

        $items = InterventionQuiz::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'quizzes' => $items]);
    }
}
