<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\InterventionVideo;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class InterventionVideoController extends Controller
{
    public function createPage($grade, $term, $subject, $week)
{
    return view('teacher.content-library.intervention-videos-create', compact('grade', 'term', 'subject', 'week'));
}

public function storePage(Request $request, $grade, $term, $subject, $week)
{
    $request->validate([
        'level'        => 'required|in:basic,standard,advanced',
        'videos_json'  => 'required|json',
    ]);

    $user = auth()->user();
    $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();
    $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();
    $schoolYear = SchoolYear::getActive();

    $level = $request->level;
    $videos = json_decode($request->videos_json, true);

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
                'file_path'          => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }
        InterventionVideo::insert($inserts);
    }

    return redirect()->route('teacher.content-library.content', [$grade, $term, $subject, $week])
                     ->with('success', 'Intervention videos saved.');
}
    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_level' => 'required|string',
            'term' => 'required|string',
            'subject' => 'required|string',
            'week' => 'required|string',
            'level' => 'required|in:basic,standard,advanced',
            'videos' => 'sometimes|array',
            'videos.*.video_type' => 'sometimes|in:link,file',
            'videos.*.video_url' => 'nullable|url',
            'videos.*.file_name' => 'nullable|string',
        ]);

        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $subject = Subject::where('name', $data['subject'])->where('grade_level', $data['grade_level'])->firstOrFail();
        $schoolYear = SchoolYear::getActive();

        $videos = $data['videos'] ?? [];

        // Delete existing
        InterventionVideo::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->where('grade_level', $data['grade_level'])
            ->where('term', $data['term'])
            ->where('week', $data['week'])
            ->where('level', $data['level'])
            ->delete();

        $inserts = [];
        foreach ($videos as $idx => $video) {
            $inserts[] = [
                'teacher_profile_id' => $teacher->id,
                'subject_id' => $subject->id,
                'school_year_id' => $schoolYear?->id,
                'grade_level' => $data['grade_level'],
                'term' => $data['term'],
                'week' => $data['week'],
                'level' => $data['level'],
                'sequence' => $idx + 1,
                'video_type' => $video['video_type'] ?? 'link',
                'video_url' => $video['video_url'] ?? null,
                'file_name' => $video['file_name'] ?? null,
                'file_path' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($inserts)) {
            InterventionVideo::insert($inserts);
        }

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:intervention_videos,id']);
        foreach ($request->ids as $idx => $id) {
            InterventionVideo::where('id', $id)->update(['sequence' => $idx + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function fetch($grade, $term, $subject, $week)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();

        $items = InterventionVideo::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->orderBy('sequence')
            ->get();

        return response()->json(['success' => true, 'videos' => $items]);
    }
}
