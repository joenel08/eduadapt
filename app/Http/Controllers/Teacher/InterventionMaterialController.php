<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\InterventionMaterial;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InterventionMaterialController extends Controller
{


    public function createPage($grade, $term, $subject, $week)
    {
        return view('teacher.content-library.intervention-materials-create', compact('grade', 'term', 'subject', 'week'));
    }

    public function storePage(Request $request, $grade, $term, $subject, $week)
    {
        $request->validate([
            'level'          => 'required|in:basic,standard,advanced',
            'materials_json' => 'required|json',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();
        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();
        $schoolYear = SchoolYear::getActive();

        $level = $request->level;
        $materials = json_decode($request->materials_json, true);

        // Delete existing materials for this level
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

        return redirect()->route('teacher.content-library.content', [$grade, $term, $subject, $week])
            ->with('success', 'Intervention materials saved.');
    }


    public function fetch($grade, $term, $subject, $week)
    {
        $teacher = TeacherProfile::where('user_id', auth()->id())->firstOrFail();
        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->firstOrFail();

        $items = InterventionMaterial::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'materials' => $items]);
    }
}
