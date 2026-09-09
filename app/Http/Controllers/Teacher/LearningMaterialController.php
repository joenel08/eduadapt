<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningMaterialController extends Controller
{
    // Upload a learning material
    public function upload(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,ppt,pptx,doc,docx,mp4,mov,avi,webm|max:51200', // 50MB
            'grade_level' => 'required|string',
            'term' => 'required|string',
            'subject' => 'required|string',
            'week' => 'required|string',
        ]);

        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subject = Subject::where('name', $request->subject)
            ->where('grade_level', $request->grade_level)
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        // Folder: content/{teacher_id}/{grade}/{term}/{subject}/{week}
        $folder = sprintf(
            'content/%d/%s/%s/%s/%s',
            $teacher->id,
            $request->grade_level,
            $request->term,
            $request->subject,
            $request->week
        );

        $file = $request->file('file');
        $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        $item = ContentItem::create([
            'teacher_profile_id' => $teacher->id,
            'subject_id' => $subject->id,
            'grade_level' => $request->grade_level,
            'term' => $request->term,
            'week' => $request->week,
            'type' => 'learning_material',
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material uploaded.',
            'item' => $this->formatItem($item),
        ]);
    }

    // Fetch all learning materials for a given week
    public function fetch($grade, $term, $subject, $week)
    {
        $user = auth()->user();
        $teacher = TeacherProfile::where('user_id', $user->id)->firstOrFail();

        $subjectModel = Subject::where('name', $subject)->where('grade_level', $grade)->first();
        if (!$subjectModel) {
            return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
        }

        $items = ContentItem::where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectModel->id)
            ->where('grade_level', $grade)
            ->where('term', $term)
            ->where('week', $week)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => $this->formatItem($item));

        return response()->json(['success' => true, 'items' => $items]);
    }

    // Delete a material
    public function delete($id)
    {
        $item = ContentItem::findOrFail($id);
        if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
            Storage::disk('public')->delete($item->file_path);
        }
        $item->delete();
        return response()->json(['success' => true]);
    }

    private function formatItem($item)
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'file_name' => basename($item->file_path),
            'file_url' => $item->file_url,
            'uploaded_at' => $item->created_at->toISOString(),
        ];
    }
}