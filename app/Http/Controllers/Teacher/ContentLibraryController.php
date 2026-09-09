<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\ContentItem;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\Intervention;
use App\Models\InterventionMaterial;
use App\Models\InterventionVideo;
use App\Models\InterventionQuiz;
use App\Models\LearningPackage;
use App\Models\Classes;
use App\Models\ContentRelease;
use Illuminate\Support\Facades\Storage;


class ContentLibraryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $grades = TeacherClassAssignment::with('class')
            ->where('teacher_profile_id', $teacher->id)
            ->get()
            ->pluck('class.grade_level')
            ->unique()
            ->values();

        if ($grades->isEmpty()) {
            $grades = ['Grade 5', 'Grade 6'];
        }

        return view('teacher.content-library.index', compact('grades'));
    }

    public function subjects($grade, $term)
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subjects = TeacherClassAssignment::with(['class', 'subject'])
            ->where('teacher_profile_id', $teacher->id)
            ->whereHas('class', function ($q) use ($grade) {
                $q->where('grade_level', $grade);
            })
            ->get()
            ->pluck('subject.name')
            ->unique()
            ->values();

        return view('teacher.content-library.subjects', compact('grade', 'term', 'subjects'));
    }

    public function weeks($grade, $term, $subject)
    {
        $weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8', 'Week 9','Week 8','Week 10','Week 11','Week 12'];
        return view('teacher.content-library.weeks', compact('grade', 'term', 'subject', 'weeks'));
    }

    public function content($grade, $term, $subject, $week)
    {
        return view('teacher.content-library.content', compact('grade', 'term', 'subject', 'week'));
    }

  

    public function materialsCreate($grade, $term, $subject, $week)
    {
        return view('teacher.content-library.materials-create', compact('grade', 'term', 'subject', 'week'));
    }

    public function materialsStore(Request $request, $grade, $term, $subject, $week)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'required|file|mimes:pdf,ppt,pptx,doc,docx,mp4,mov,avi,webm|max:51200',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subjectModel = Subject::where('name', $subject)
            ->where('grade_level', $grade)
            ->firstOrFail();

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $folder = sprintf('learning_materials/%d/%s/%s/%s/%s', $teacher->id, $grade, $term, $subject, $week);
        $path = $file->storeAs($folder, $fileName, 'public');

        ContentItem::create([
            'teacher_profile_id' => $teacher->id,
            'subject_id'         => $subjectModel->id,
            'grade_level'        => $grade,
            'term'               => $term,
            'week'               => $week,
            'title'              => $request->title,
            'description'        => $request->description,
            'file_path'          => $path,
            'file_name'          => $fileName,
            'type'               => 'learning_material',
        ]);

        return redirect()->route('teacher.content-library.content', [$grade, $term, $subject, $week])
            ->with('success', 'Learning material added successfully.');
    }

    // --- AJAX endpoints ---

    public function saveContent(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function deleteContent(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $item = ContentItem::findOrFail($id);
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();
        if ($item->teacher_profile_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,mp4,mov,avi,webm|max:51200',
        ]);

        $item->title = $request->title;
        $item->description = $request->description;

        if ($request->hasFile('file')) {
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
            $folder = dirname($item->file_path);
            $file = $request->file('file');
            $filename = \Illuminate\Support\Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');
            $item->file_path = $path;
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Material updated.',
            'item' => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'file_name' => basename($item->file_path),
                'file_url' => $item->file_url,
                'uploaded_at' => $item->created_at->toISOString(),
            ]
        ]);
    }


    public function view($grade, $term, $subject, $week, $type, $id)
    {
        $item = $this->fetchContentItem($type, $id, $grade, $term, $subject, $week);
        if (!$item) {
            abort(404, 'Content not found.');
        }

        return view('teacher.content-library.view', compact('grade', 'term', 'subject', 'week', 'item', 'type'));
    }

    public function edit($grade, $term, $subject, $week, $type, $id)
    {
        $item = $this->fetchContentItem($type, $id, $grade, $term, $subject, $week);
        if (!$item) {
            abort(404, 'Content not found.');
        }

        $viewMap = [
            'learningMaterial'      => 'teacher.content-library.materials-edit',
            'preAssessment'         => 'teacher.content-library.pre-assessment-edit',
            'postAssessment'        => 'teacher.content-library.post-assessment-edit',
            'interventionMaterial'  => 'teacher.content-library.intervention-materials-edit',
            'interventionVideo'     => 'teacher.content-library.intervention-videos-edit',
            'interventionQuiz'      => 'teacher.content-library.intervention-quiz-edit',
        ];

        if (!isset($viewMap[$type])) {
            abort(400, 'Invalid content type.');
        }

        return view($viewMap[$type], compact('grade', 'term', 'subject', 'week', 'item'));
    }
    public function updatePage(Request $request, $grade, $term, $subject, $week, $type, $id)
    {
        $item = $this->fetchContentItem($type, $id, $grade, $term, $subject, $week);
        if (!$item) {
            abort(404, 'Content not found.');
        }

        switch ($type) {
            case 'learningMaterial':
                return $this->updateLearningMaterial($request, $item);
            case 'preAssessment':
                return $this->updatePreAssessment($request, $item);
            case 'postAssessment':
                return $this->updatePostAssessment($request, $item);
            case 'interventionMaterial':
                return $this->updateInterventionMaterial($request, $item);
            case 'interventionVideo':
                return $this->updateInterventionVideo($request, $item);
            case 'interventionQuiz':
                return $this->updateInterventionQuiz($request, $item);
            default:
                abort(400, 'Invalid content type.');
        }
    }

    private function fetchContentItem($type, $id, $grade, $term, $subject, $week)
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        switch ($type) {
            case 'learningMaterial':
                $item = ContentItem::where('id', $id)->first();
                // Verify ownership and context
                if (
                    $item && $item->teacher_profile_id == $teacher->id &&
                    $item->grade_level == $grade &&
                    $item->term == $term &&
                    $item->week == $week
                ) {
                    return $item;
                }
                return null;

            case 'preAssessment':
                $item = PreAssessment::where('id', $id)->first();
                if (
                    $item && $item->teacher_profile_id == $teacher->id &&
                    $item->grade_level == $grade &&
                    $item->term == $term &&
                    $item->week == $week
                ) {
                    return $item;
                }
                return null;

            case 'postAssessment':
                $item = PostAssessment::where('id', $id)->first();
                if (
                    $item && $item->teacher_profile_id == $teacher->id &&
                    $item->grade_level == $grade &&
                    $item->term == $term &&
                    $item->week == $week
                ) {
                    return $item;
                }
                return null;

            case 'interventionMaterial':
                return InterventionMaterial::where('id', $id)
                    ->where('teacher_profile_id', $teacher->id)
                    ->where('grade_level', $grade)
                    ->where('term', $term)
                    ->where('week', $week)
                    ->first();
            case 'interventionVideo':
                return InterventionVideo::where('id', $id)
                    ->where('teacher_profile_id', $teacher->id)
                    ->where('grade_level', $grade)
                    ->where('term', $term)
                    ->where('week', $week)
                    ->first();
            case 'interventionQuiz':
                return InterventionQuiz::where('id', $id)
                    ->where('teacher_profile_id', $teacher->id)
                    ->where('grade_level', $grade)
                    ->where('term', $term)
                    ->where('week', $week)
                    ->first();

            default:
                return null;
        }
    }

    private function updateLearningMaterial(Request $request, $item)
    {
        // Similar to the existing update method, but with redirect.
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,mp4,mov,avi,webm|max:51200',
        ]);

        $item->title = $request->title;
        $item->description = $request->description;

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
            $file = $request->file('file');
            $fileName = \Illuminate\Support\Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $folder = dirname($item->file_path);
            $path = $file->storeAs($folder, $fileName, 'public');
            $item->file_path = $path;
        }
        $item->save();

        return redirect()->route('teacher.content-library.content', [$item->grade_level, $item->term, $item->subject->name, $item->week])
            ->with('success', 'Learning material updated.');
    }

    private function updatePreAssessment(Request $request, $item)
    {
        // Similar to storePage but update existing.
        // We'll keep it simple: validate and update.
        $request->validate([
            'exam_type' => 'required|in:multipleChoice,trueFalse,matchingType,mixed',
            'input_method' => 'required|in:upload,manual',
            'questions' => 'required|json',
            'settings' => 'nullable|json',
            'file' => 'nullable|file|mimes:xlsx,xls|max:20480',
        ]);

        $questions = json_decode($request->questions, true);
        $settings = json_decode($request->settings ?? '{}', true);

        // Handle file upload if new file provided
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($item->file_name && Storage::disk('public')->exists('pre_assessments/...')) {
                // You can implement file deletion logic
            }
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = sprintf('pre_assessments/%d/%s/%s/%s/%s', $item->teacher_profile_id, $item->grade_level, $item->term, $item->subject->name, $item->week);
            $file->storeAs($folder, $fileName, 'public');
            $item->file_name = $fileName;
        }

        $item->exam_type = $request->exam_type;
        $item->input_method = $request->input_method;
        $item->questions = $questions;
        $item->settings = $settings;
        $item->save();

        return redirect()->route('teacher.content-library.content', [$item->grade_level, $item->term, $item->subject->name, $item->week])
            ->with('success', 'Pre‑assessment updated.');
    }
    private function updatePostAssessment(Request $request, $item)
    {
        $request->validate([
            'exam_type'     => 'required|in:multipleChoice,trueFalse,matchingType,mixed',
            'input_method'  => 'required|in:upload,manual',
            'questions'     => 'required|json',
            'settings'      => 'nullable|json',
            'file'          => 'nullable|file|mimes:xlsx,xls|max:20480',
        ]);

        $questions = json_decode($request->questions, true);
        $settings = json_decode($request->settings ?? '{}', true);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = sprintf('post_assessments/%d/%s/%s/%s/%s', $item->teacher_profile_id, $item->grade_level, $item->term, $item->subject->name, $item->week);
            $file->storeAs($folder, $fileName, 'public');
            $item->file_name = $fileName;
        }

        $item->exam_type = $request->exam_type;
        $item->input_method = $request->input_method;
        $item->questions = $questions;
        $item->settings = $settings;
        $item->save();

        return redirect()->route('teacher.content-library.content', [$item->grade_level, $item->term, $item->subject->name, $item->week])
            ->with('success', 'Post-assessment updated.');
    }
   

    private function updateInterventionMaterial(Request $request, $item)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
        ]);

        $item->file_name = $request->file_name;
        $item->save();

        return redirect()->route('teacher.content-library.content', [$item->grade_level, $item->term, $item->subject->name, $item->week])
            ->with('success', 'Intervention material updated.');
    }
    private function updateInterventionVideo(Request $request, $item)
    {
        $request->validate([
            'video_type' => 'required|in:link,file',
            'video_url'  => 'nullable|url',
            'file_name'  => 'nullable|string|max:255',
        ]);

        $item->video_type = $request->video_type;
        $item->video_url = $request->video_url;
        $item->file_name = $request->file_name;
        $item->save();

        return redirect()->route('teacher.content-library.content', [$item->grade_level, $item->term, $item->subject->name, $item->week])
            ->with('success', 'Intervention video updated.');
    }
    private function updateInterventionQuiz(Request $request, $item)
    {
        $request->validate([
            'exam_type'     => 'required|in:multipleChoice,trueFalse,matchingType,mixed',
            'questions'     => 'required|json',
            'settings'      => 'nullable|json',
            'file'          => 'nullable|file|mimes:xlsx,xls|max:20480',
        ]);

        $questions = json_decode($request->questions, true);
        $settings = json_decode($request->settings ?? '{}', true);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = sprintf('intervention_quizzes/%d/%s/%s/%s/%s/%s', $item->teacher_profile_id, $item->grade_level, $item->term, $item->subject->name, $item->week, $item->level);
            $file->storeAs($folder, $fileName, 'public');
            $item->file_name = $fileName;
        }

        $item->exam_type = $request->exam_type;
        $item->questions = $questions;
        $item->settings = $settings;
        $item->save();

        return redirect()->route('teacher.content-library.content', [$item->grade_level, $item->term, $item->subject->name, $item->week])
            ->with('success', 'Intervention quiz updated.');
    }




    public function getConfig($grade, $term, $subject, $week, $type, $id)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();

        // Get all classes the teacher teaches for this grade & subject
        $assignedClasses = TeacherClassAssignment::with('class')
            ->where('teacher_profile_id', $teacher->id)
            ->whereHas('class', fn($q) => $q->where('grade_level', $grade))
            ->whereHas('subject', fn($q) => $q->where('name', $subject))
            ->get()
            ->map(fn($tca) => [
                'id' => $tca->class_id,
                'name' => $tca->class->section_name,
            ]);

        // Get existing releases for this content item
        $releases = ContentRelease::where('teacher_profile_id', $teacher->id)
            ->where('content_type', $type)
            ->where('content_id', $id)
            ->get()
            ->keyBy('class_id');

        return response()->json([
            'success' => true,
            'classes' => $assignedClasses,
            'releases' => $releases,
        ]);
    }

    public function saveConfig(Request $request, $grade, $term, $subject, $week)
    {
        $data = $request->validate([
            'content_type' => 'required|string',
            'content_id' => 'required|integer',
            'assignments' => 'required|array',
            'assignments.*.class_id' => 'required|exists:classes,id',
            'assignments.*.release_date' => 'nullable|date_format:Y-m-d H:i:s',
            'assignments.*.due_date' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();

        // Resolve subject ID from name (assumes unique subject names)
        $subjectModel = Subject::where('name', $subject)->first();
        if (!$subjectModel) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 400);
        }
        $subjectId = $subjectModel->id;
        \Log::info('Subject lookup: ' . $subject . ' -> ID: ' . ($subjectModel ? $subjectModel->id : 'null'));

        // Delete old releases for this content
        ContentRelease::where('teacher_profile_id', $teacher->id)
            ->where('content_type', $data['content_type'])
            ->where('content_id', $data['content_id'])
            ->delete();

        // Insert new releases
        foreach ($data['assignments'] as $assignment) {
            if ($assignment['release_date'] || $assignment['due_date']) {
                ContentRelease::create([
                    'teacher_profile_id' => $teacher->id,
                    'content_type' => $data['content_type'],
                    'content_id' => $data['content_id'],
                    'class_id' => $assignment['class_id'],
                    'subject_id' => $subjectId,
                    'release_date' => $assignment['release_date'] ?? null,
                    'due_date' => $assignment['due_date'] ?? null,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }



    public function getWeekReleases($grade, $term, $subject, $week)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();

        // 1. Get all content items for this week, grouped by type
        $pairs = [];

        // Learning Materials
        $materials = ContentItem::where('teacher_profile_id', $teacher->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->get(['id']);
        foreach ($materials as $mat) {
            $pairs[] = ['content_type' => 'learningMaterial', 'content_id' => $mat->id];
        }

        // Pre‑Assessments
        $pres = PreAssessment::where('teacher_profile_id', $teacher->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->get(['id']);
        foreach ($pres as $pre) {
            $pairs[] = ['content_type' => 'preAssessment', 'content_id' => $pre->id];
        }

        // Post‑Assessments
        $posts = PostAssessment::where('teacher_profile_id', $teacher->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->get(['id']);
        foreach ($posts as $post) {
            $pairs[] = ['content_type' => 'postAssessment', 'content_id' => $post->id];
        }

        // Intervention Materials
        $intMats = InterventionMaterial::where('teacher_profile_id', $teacher->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->get(['id']);
        foreach ($intMats as $mat) {
            $pairs[] = ['content_type' => 'interventionMaterial', 'content_id' => $mat->id];
        }

        // Intervention Videos
        $intVids = InterventionVideo::where('teacher_profile_id', $teacher->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->get(['id']);
        foreach ($intVids as $vid) {
            $pairs[] = ['content_type' => 'interventionVideo', 'content_id' => $vid->id];
        }

        // Intervention Quizzes
        $intQuizzes = InterventionQuiz::where('teacher_profile_id', $teacher->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->get(['id']);
        foreach ($intQuizzes as $quiz) {
            $pairs[] = ['content_type' => 'interventionQuiz', 'content_id' => $quiz->id];
        }

        if (empty($pairs)) {
            return response()->json(['success' => true, 'releases' => []]);
        }

        // 2. Fetch all releases for these content items
        $releases = ContentRelease::where('teacher_profile_id', $teacher->id)
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as $pair) {
                    $query->orWhere(function ($q) use ($pair) {
                        $q->where('content_type', $pair['content_type'])
                            ->where('content_id', $pair['content_id']);
                    });
                }
            })
            ->with('class') // assuming relationship exists
            ->get();

        // 3. Group by content_type and content_id
        $grouped = [];
        foreach ($releases as $rel) {
            $type = $rel->content_type;
            $id = $rel->content_id;
            $class = $rel->class;

            $grouped[$type][$id][] = [
                'id' => $rel->id,
                'class_id' => $rel->class_id,
                'class_name' => $class ? $class->section_name : 'Unknown',
                'release_date' => $rel->release_date,
                'due_date' => $rel->due_date,
            ];
        }

        return response()->json(['success' => true, 'releases' => $grouped]);
    }

    public function deleteRelease($id)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $release = ContentRelease::where('id', $id)
            ->where('teacher_profile_id', $teacher->id)
            ->firstOrFail();

        $release->delete();

        return response()->json(['success' => true]);
    }
}
