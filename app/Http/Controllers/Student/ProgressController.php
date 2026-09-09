<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\StudentClassRecord;
use App\Models\ContentRelease;
use App\Models\StudentContentProgress;
use App\Models\ContentItem;
use App\Models\PreAssessment;
use App\Models\PostAssessment;
use App\Models\InterventionMaterial;
use App\Models\InterventionVideo;
use App\Models\InterventionQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Show the progress tracking page for the student.
     */
    public function index()
    {
        $student = StudentProfile::where('user_id', Auth::id())->firstOrFail();
        $classRecords = StudentClassRecord::where('student_profile_id', $student->id)->get();

        $items = [];

        foreach ($classRecords as $record) {
            $class = $record->class;
            $releases = ContentRelease::where('class_id', $class->id)->get();

            foreach ($releases as $release) {
                $content = $this->getContentModel($release->content_type, $release->content_id);
                if (!$content) continue;

                $progress = StudentContentProgress::where('student_profile_id', $student->id)
                    ->where('content_type', get_class($content))
                    ->where('content_id', $content->id)
                    ->first();

                $status = $progress ? $progress->status : 'pending';
                $preScore = null;
                $postScore = null;
                $attempts = 0; // not tracked yet
                $progressPercent = 0;

                if ($progress) {
                    if ($status === 'completed') $progressPercent = 100;
                    elseif ($status === 'viewed') $progressPercent = 50;

                    // For assessments, compute percentage score
                    $classType = get_class($content);
                    if (in_array($classType, [PreAssessment::class, PostAssessment::class, InterventionQuiz::class])) {
                        $score = $progress->score;
                        if ($score !== null) {
                            $total = count($content->questions ?? []);
                            $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
                            if ($classType === PreAssessment::class) {
                                $preScore = $percentage;
                            } elseif ($classType === PostAssessment::class) {
                                $postScore = $percentage;
                            } else {
                                // Intervention quiz – treat as postScore for now
                                $postScore = $percentage;
                            }
                        }
                    }
                }

                // Determine category based on postScore
                $category = null;
                if ($postScore !== null) {
                    if ($postScore >= 85) $category = 'advanced';
                    elseif ($postScore >= 70) $category = 'average';
                    else $category = 'below-average';
                }

                // Build a meaningful title
                $title = $this->getContentTitle($content);

                $items[] = (object) [
                    'title' => $title,
                    'class_name' => $class->grade_level . ' - ' . $class->section_name,
                    'status' => $status,
                    'pre_score' => $preScore,
                    'post_score' => $postScore,
                    'category' => $category,
                    'attempts' => 0,
                    'progress_percent' => $progressPercent,
                    'content_type' => get_class($content),
                ];
            }
        }

        return view('student.progress', ['items' => $items]);
    }

    /**
     * Helper to get the actual content model from a release.
     */
    private function getContentModel($contentType, $contentId)
    {
        $map = [
            'learningMaterial'      => ContentItem::class,
            'preAssessment'         => PreAssessment::class,
            'postAssessment'        => PostAssessment::class,
            'interventionMaterial'  => InterventionMaterial::class,
            'interventionVideo'     => InterventionVideo::class,
            'interventionQuiz'      => InterventionQuiz::class,
        ];

        $class = $map[$contentType] ?? null;
        if (!$class) return null;

        return $class::find($contentId);
    }

    /**
     * Get a user‑friendly title for the content.
     */
    private function getContentTitle($content)
    {
        $class = get_class($content);

        if (in_array($class, [ContentItem::class, InterventionMaterial::class, InterventionVideo::class])) {
            return $content->title ?? 'Untitled';
        }

        if ($class === PreAssessment::class) {
            return 'Pre-Assessment' . ($content->week ? ' (Week ' . $content->week . ')' : '');
        }

        if ($class === PostAssessment::class) {
            return 'Post-Assessment' . ($content->week ? ' (Week ' . $content->week . ')' : '');
        }

        if ($class === InterventionQuiz::class) {
            return 'Mini Quiz' . ($content->week ? ' (Week ' . $content->week . ')' : '');
        }

        return 'Unknown Content';
    }
}