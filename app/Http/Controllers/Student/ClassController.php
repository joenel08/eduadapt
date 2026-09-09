<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\StudentClassRecord;
use App\Models\Classes;
use App\Models\ContentRelease;
use App\Models\StudentContentProgress;
// Import all content models
use App\Models\ContentItem;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionMaterial;
use App\Models\InterventionVideo;
use App\Models\InterventionQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\TeacherClassAssignment;

class ClassController extends Controller
{
    protected $contentTypeMap = [
        'learningMaterial'      => ContentItem::class,
        'preAssessment'         => PreAssessment::class,
        'postAssessment'        => PostAssessment::class,
        'interventionMaterial'  => InterventionMaterial::class,
        'interventionVideo'     => InterventionVideo::class,
        'interventionQuiz'      => InterventionQuiz::class,
    ];

    /**
     * Determine if a content item is considered "done" based on its type and progress status.
     */
    private function isContentDone($release, $progressRecords)
{
    // Map short content_type (from releases) to full class name (used in progress)
    $typeMap = [
        'learningMaterial'      => ContentItem::class,
        'preAssessment'         => PreAssessment::class,
        'postAssessment'        => PostAssessment::class,
        'interventionMaterial'  => InterventionMaterial::class,
        'interventionVideo'     => InterventionVideo::class,
        'interventionQuiz'      => InterventionQuiz::class,
    ];

    $fullClass = $typeMap[$release->content_type] ?? null;
    if (!$fullClass) {
        return false;
    }

    $key = $fullClass . '|' . $release->content_id;
    $progress = $progressRecords->get($key);

    if (!$progress) {
        return false;
    }

    $status = $progress->status;

    // Material types: viewed or completed counts
    $materialTypes = [
        ContentItem::class,
        InterventionMaterial::class,
        InterventionVideo::class
    ];
    // Assessment types: only completed counts
    $assessmentTypes = [
        PreAssessment::class,
        PostAssessment::class,
        InterventionQuiz::class
    ];

    if (in_array($fullClass, $materialTypes)) {
        return in_array($status, ['viewed', 'completed']);
    } elseif (in_array($fullClass, $assessmentTypes)) {
        return $status === 'completed';
    }

    // Fallback: non-pending is considered done
    return $status !== 'pending';
}
    public function index()
    {
        $user = auth()->user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();

        $classIds = StudentClassRecord::where('student_profile_id', $student->id)->pluck('class_id');

        // Get all assignments
        $assignments = TeacherClassAssignment::with(['class.schoolYear', 'class.studentClassRecords', 'subject'])
            ->whereIn('class_id', $classIds)
            ->get();

        // Fetch all releases for these classes at once
        $allReleases = ContentRelease::whereIn('class_id', $classIds)->get();

        // Group releases by class_id|subject_id
        $releasesByClassSubject = [];
        foreach ($allReleases as $release) {
            $key = $release->class_id . '|' . $release->subject_id;
            $releasesByClassSubject[$key][] = $release;
        }

        // Fetch all progress records for this student once
        $progressRecords = StudentContentProgress::where('student_profile_id', $student->id)
            ->get()
            ->keyBy(fn($p) => $p->content_type . '|' . $p->content_id);

        $classes = $assignments->map(function ($assignment) use ($student, $releasesByClassSubject, $progressRecords) {
            $class = $assignment->class;
            $subject = $assignment->subject;
            $key = $class->id . '|' . $subject->id;

            $releases = $releasesByClassSubject[$key] ?? [];

            $total = count($releases);
            $completed = 0;

            foreach ($releases as $release) {
                if ($this->isContentDone($release, $progressRecords)) {
                    $completed++;
                }
            }

            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

            return (object) [
                'id' => $class->id,
                'subject_id' => $subject->id,
                'grade_level' => $class->grade_level,
                'section_name' => $class->section_name,
                'subject_name' => $subject->name,
                'school_year' => $class->schoolYear->year,
                'student_count' => $class->studentClassRecords->count(),
                'total_lessons' => $total,
                'completed_lessons' => $completed,
                'progress' => $progress,
            ];
        });

        return view('student.classes', compact('classes'));
    }
    // Show the class details page
    /**
     * Show the class details page for a specific subject.
     */
    public function show($classId, $subjectId = null)
    {
        $user = Auth::user();
        $student = StudentProfile::where('user_id', $user->id)->firstOrFail();


        $enrollment = StudentClassRecord::where('student_profile_id', $student->id)
            ->where('class_id', $classId)
            ->first();
        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        $class = Classes::with(['schoolYear', 'teacherAssignments.subject'])->findOrFail($classId);

        // Get all subjects assigned to this class
        $subjects = TeacherClassAssignment::where('class_id', $classId)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->filter();

        if ($subjects->isEmpty()) {
            abort(404, 'No subjects assigned to this class.');
        }

        // If no subject ID is provided, redirect to the first subject
        if (!$subjectId || !$subjects->contains('id', $subjectId)) {
            $firstSubject = $subjects->first();
            return redirect()->route('student.class.details', ['classId' => $classId, 'subjectId' => $firstSubject->id]);
        }

        $selectedSubject = $subjects->firstWhere('id', $subjectId);

        // Fetch releases for this class AND this subject
        $releases = ContentRelease::where('class_id', $class->id)
            ->where('subject_id', $subjectId)
            ->get();

        // dd($releases->pluck('subject_id', 'id')->toArray());

        // Extract content from releases
        $lessonMaterials = $this->extractContent($releases, 'learningMaterial');
        $preAssessment   = $this->extractContent($releases, 'preAssessment')->first();
        $postAssessment  = $this->extractContent($releases, 'postAssessment')->first();
        $interventionMaterials = $this->extractContent($releases, 'interventionMaterial');
        $interventionVideos    = $this->extractContent($releases, 'interventionVideo');
        $interventionQuiz      = $this->extractContent($releases, 'interventionQuiz')->first();

        // Parse time limits
        $preTimeLimit = null;
        if ($preAssessment && isset($preAssessment->settings['timer'])) {
            $parts = explode(':', $preAssessment->settings['timer']);
            $preTimeLimit = (int)$parts[0] * 60 + (int)$parts[1];
        }
        $postTimeLimit = null;
        if ($postAssessment && isset($postAssessment->settings['timer'])) {
            $parts = explode(':', $postAssessment->settings['timer']);
            $postTimeLimit = (int)$parts[0] * 60 + (int)$parts[1];
        }
        $quizTimeLimit = null;
        if ($interventionQuiz && isset($interventionQuiz->settings['timer'])) {
            $parts = explode(':', $interventionQuiz->settings['timer']);
            $quizTimeLimit = (int)$parts[0] * 60 + (int)$parts[1];
        }
        $preTimeLimit = $preTimeLimit ?? 10;
        $postTimeLimit = $postTimeLimit ?? 10;
        $quizTimeLimit = $quizTimeLimit ?? 5;

        // Attach progress
        $lessonMaterials = $this->attachProgress($lessonMaterials, $student);
        $interventionMaterials = $this->attachProgress($interventionMaterials, $student);
        $interventionVideos = $this->attachProgress($interventionVideos, $student);
        if ($preAssessment) {
            $preAssessment = $this->attachProgress(collect([$preAssessment]), $student)->first();
        }
        if ($postAssessment) {
            $postAssessment = $this->attachProgress(collect([$postAssessment]), $student)->first();
        }
        if ($interventionQuiz) {
            $interventionQuiz = $this->attachProgress(collect([$interventionQuiz]), $student)->first();
        }

        // Compute access for this subject
        $access = $this->computeAccess($student, $lessonMaterials, $preAssessment, $postAssessment, $interventionMaterials, $interventionVideos, $interventionQuiz);

        // Progress only for this subject's materials
        $totalMaterials = $lessonMaterials->count();
        $completedMaterials = $lessonMaterials->filter(fn($m) => in_array($m->progress, ['viewed', 'completed']))->count();
        $lessonProgress = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;

        $subjectNames = $selectedSubject->name ?? 'General';

        // Get lock flags
        $materialsLocked = $enrollment->materials_locked ?? false;
        $interventionMaterialsLocked = $enrollment->intervention_materials_locked ?? false;

        return view('student.class-details', compact(
            'class',
            'subjects',
            'selectedSubject',
            'subjectNames',
            'lessonMaterials',
            'lessonProgress',
            'preAssessment',
            'postAssessment',
            'interventionMaterials',
            'interventionVideos',
            'interventionQuiz',
            'access',
            'student',
            'materialsLocked',
            'interventionMaterialsLocked',
            'preTimeLimit',
            'postTimeLimit',
            'quizTimeLimit'
        ));
    }

    /**
     * Extract content items from releases based on the content_type key.
     * Uses the $contentTypeMap to resolve the actual model class.
     *
     * @param \Illuminate\Support\Collection $releases
     * @param string $typeKey The short type (e.g., 'learningMaterial')
     * @return \Illuminate\Support\Collection
     */
    private function extractContent($releases, $typeKey)
    {
        // Get the corresponding model class from the map
        $modelClass = $this->contentTypeMap[$typeKey] ?? null;
        if (!$modelClass) {
            return collect();
        }

        // Filter releases by content_type matching the map key
        $ids = $releases->filter(fn($r) => $r->content_type === $typeKey)
            ->pluck('content_id')
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        // Load the actual models
        return $modelClass::whereIn('id', $ids)->get();
    }

    // Helper: attach progress to each content item
    private function attachProgress($items, $student)
    {
        return $items->map(function ($item) use ($student) {
            $progress = StudentContentProgress::where('student_profile_id', $student->id)
                ->where('content_type', get_class($item))
                ->where('content_id', $item->id)
                ->first();
            $item->progress = $progress ? $progress->status : 'pending';
            $item->score = $progress ? $progress->score : null;
            $item->answers = $progress ? $progress->answers : null;
            $item->progress_id = $progress ? $progress->id : null;
            return $item;
        });
    }

    // Compute which steps are accessible
    private function computeAccess($student, $lessonMaterials, $preAssessment, $postAssessment, $interventionMaterials, $interventionVideos, $interventionQuiz)
    {
        $materialsCompleted = $this->areAllCompleted($lessonMaterials);

        $interventionMaterialsCompleted = $this->areAllCompleted($interventionMaterials->merge($interventionVideos));
        $preCompleted = $preAssessment ? $this->isAssessmentCompleted($student, $preAssessment) : true;
        $postCompleted = $postAssessment ? $this->isAssessmentCompleted($student, $postAssessment) : true;
        $quizCompleted = $interventionQuiz ? $this->isAssessmentCompleted($student, $interventionQuiz) : true;


        return [
            'materials' => true,
            'pre_assessment' => $materialsCompleted,
            'post_assessment' => $materialsCompleted && $preCompleted,
            'intervention' => $materialsCompleted && $preCompleted && $postCompleted,
            'intervention_quiz' => $materialsCompleted && $preCompleted && $postCompleted && $interventionMaterialsCompleted,
            'materials_done' => $materialsCompleted,
            'pre_done' => $preCompleted,
            'post_done' => $postCompleted,
            'intervention_materials_done' => $interventionMaterialsCompleted,
            'quiz_done' => $quizCompleted,
        ];
    }

    public function lockMaterials(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'type' => 'required|in:lesson,intervention'
        ]);

        $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();
        $record = StudentClassRecord::where('student_profile_id', $student->id)
            ->where('class_id', $request->class_id)
            ->firstOrFail();

        if ($request->type === 'lesson') {
            $record->materials_locked = true;                  // existing column
        } else {
            $record->intervention_materials_locked = true;     // new column
        }
        $record->save();

        return response()->json(['success' => true]);
    }
    private function areAllCompleted($items)
    {
        if ($items->isEmpty()) return true;
        return $items->every(fn($item) => in_array($item->progress, ['viewed', 'completed']));
    }

    private function isAssessmentCompleted($student, $assessment)
    {
        if (!$assessment) return true; // no assessment → done
        $progress = StudentContentProgress::where('student_profile_id', $student->id)
            ->where('content_type', get_class($assessment))
            ->where('content_id', $assessment->id)
            ->first();
        return $progress && $progress->status === 'completed';
    }

    public function markViewed(Request $request)
    {
        try {
            $request->validate([
                'content_type' => 'required|string',
                'content_id' => 'required|integer',
                'class_id' => 'required|integer',
                'status' => 'sometimes|in:viewed,completed', // optional, default to 'viewed'
            ]);

            $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();
            $record = StudentClassRecord::where('student_profile_id', $student->id)
                ->where('class_id', $request->class_id)
                ->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Not enrolled.'], 403);
            }

            $contentType = $request->content_type;
            $status = $request->input('status', 'viewed');

            // Check locks (keep existing logic)
            if (in_array($contentType, ['App\\Models\\InterventionMaterial', 'App\\Models\\InterventionVideo'])) {
                if ($record->intervention_materials_locked) {
                    return response()->json(['success' => false, 'message' => 'Intervention materials are locked.'], 403);
                }
            } elseif ($contentType === 'App\\Models\\ContentItem') {
                if ($record->materials_locked) {
                    return response()->json(['success' => false, 'message' => 'Lesson materials are locked.'], 403);
                }
            }

            $progress = StudentContentProgress::updateOrCreate(
                [
                    'student_profile_id' => $student->id,
                    'content_type' => $request->content_type,
                    'content_id' => $request->content_id,
                ],
                [
                    'status' => $status,
                    'updated_at' => now(),
                    // Optionally set 'completed_at' if status is 'completed'
                    'completed_at' => $status === 'completed' ? now() : null,
                ]
            );

            return response()->json(['success' => true, 'progress' => $progress]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // AJAX: Submit an assessment (pre, post, or intervention quiz)
    public function submitAssessment(Request $request)
    {
        try {
            $request->validate([
                'content_type' => 'required|string',
                'content_id' => 'required|integer',
                'answers' => 'required|string',
                'auto_submit' => 'boolean',
                'video' => 'nullable|file|mimes:webm,mp4|max:51200',
            ]);

            $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();

            // Decode answers JSON
            $answers = json_decode($request->answers, true);
            if (!is_array($answers)) {
                return response()->json(['success' => false, 'message' => 'Invalid answers format'], 400);
            }

            $contentType = $request->content_type;
            if (!class_exists($contentType)) {
                return response()->json(['success' => false, 'message' => 'Invalid content type'], 400);
            }

            $assessment = $contentType::findOrFail($request->content_id);

            // Score the answers
            $score = $this->calculateScore($assessment, $answers);
            $total = count($assessment->questions);
            $percentage = round(($score / $total) * 100);

            // Handle video
            $videoPath = null;
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('assessment_videos', 'public');
            }

            // Build data – answers will be JSON encoded (either via cast or manually)
            $data = [
                'status' => 'completed',
                'score' => $score,
                'answers' => $answers, // If model has casts, this is fine; otherwise use json_encode($answers)
                'completed_at' => now(),
            ];
            if ($videoPath) {
                $data['video_path'] = $videoPath;
            }

            $progress = StudentContentProgress::updateOrCreate(
                [
                    'student_profile_id' => $student->id,
                    'content_type' => $contentType,
                    'content_id' => $request->content_id,
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'progress' => $progress,
            ]);
        } catch (\Exception $e) {
            // \Log::error('Assessment submission failed: ' . $e->getMessage(), [
            //     'request' => $request->all(),
            //     'trace' => $e->getTraceAsString()
            // ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
    // Private scoring method (handles multiple choice, true/false, matching)
    private function calculateScore($assessment, $submittedAnswers)
    {
        $questions = $assessment->questions;
        $score = 0;

        foreach ($questions as $index => $q) {
            $submitted = $submittedAnswers[$index] ?? null;
            if ($submitted === null) continue;

            switch ($q['type']) {
                case 'multipleChoice':
                case 'trueFalse':
                    if ($submitted === $q['correctAnswer']) {
                        $score++;
                    }
                    break;
                case 'matchingType':
                    $correct = true;
                    foreach ($q['pairs'] as $pairIndex => $pair) {
                        if (($submitted[$pairIndex] ?? null) !== $pair['answer']) {
                            $correct = false;
                            break;
                        }
                    }
                    if ($correct) $score++;
                    break;
            }
        }
        return $score;
    }

    // Optional: Get overall progress (used by AJAX)
    public function getProgress(Request $request)
    {
        $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();
        $classId = $request->input('class_id');

        if (!$classId) {
            return response()->json(['percentage' => 0]);
        }

        $class = Classes::findOrFail($classId);
        $releases = ContentRelease::where('class_id', $class->id)->get();

        $lessonMaterials = $this->extractContent($releases, 'learningMaterial');
        $lessonMaterials = $this->attachProgress($lessonMaterials, $student);

        $totalMaterials = $lessonMaterials->count();
        $completedMaterials = $lessonMaterials->filter(fn($m) => in_array($m->progress, ['viewed', 'completed']))->count();
        $percentage = $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0;

        return response()->json(['percentage' => $percentage]);
    }
    public function completeLesson(Request $request)
    {
        $request->validate(['class_id' => 'required|integer']);
        $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();
        $class = Classes::findOrFail($request->class_id);

        // Get all learning materials for this class
        $releases = ContentRelease::where('class_id', $class->id)
            ->where('content_type', 'learningMaterial')  // from your content_release types
            ->get();

        foreach ($releases as $release) {
            $model = ContentItem::find($release->content_id);
            if ($model) {
                StudentContentProgress::updateOrCreate(
                    [
                        'student_profile_id' => $student->id,
                        'content_type' => ContentItem::class,  // 'App\Models\ContentItem'
                        'content_id' => $model->id,
                    ],
                    [
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]
                );
            }
        }

        return response()->json(['success' => true]);
    }

    public function getInterventionProgress(Request $request)
    {
        $request->validate(['class_id' => 'required|integer']);
        $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();

        $class = Classes::findOrFail($request->class_id);
        $releases = ContentRelease::where('class_id', $class->id)->get();

        $interventionMaterials = $this->extractContent($releases, 'interventionMaterial');
        $interventionVideos = $this->extractContent($releases, 'interventionVideo');

        $interventionMaterials = $this->attachProgress($interventionMaterials, $student);
        $interventionVideos = $this->attachProgress($interventionVideos, $student);

        $allItems = $interventionMaterials->merge($interventionVideos);
        $completed = $allItems->every(fn($item) => in_array($item->progress, ['viewed', 'completed']));
        $total = $allItems->count();

        return response()->json(['complete' => $completed, 'total' => $total]);
    }

    public function completeIntervention(Request $request)
    {
        $request->validate(['class_id' => 'required|integer']);

        $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();
        $class = Classes::findOrFail($request->class_id);

        // Get all intervention releases for this class (materials + videos)
        $releases = ContentRelease::where('class_id', $class->id)
            ->whereIn('content_type', ['interventionMaterial', 'interventionVideo'])
            ->get();

        if ($releases->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No intervention items found.']);
        }

        foreach ($releases as $release) {
            // Find the actual model instance (InterventionMaterial or InterventionVideo)
            $contentClass = $release->content_type === 'interventionMaterial'
                ? InterventionMaterial::class
                : InterventionVideo::class;
            $content = $contentClass::find($release->content_id);
            if ($content) {
                StudentContentProgress::updateOrCreate(
                    [
                        'student_profile_id' => $student->id,
                        'content_type' => $contentClass,
                        'content_id' => $content->id,
                    ],
                    [
                        'status' => 'completed',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Also mark the intervention_materials_done flag? Not needed as the JS will handle it.
        return response()->json(['success' => true]);
    }


    
}
