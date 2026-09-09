@extends('layouts.teacher-student')

@section('page_title', 'Edit ' . ucfirst($type) . ' - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>

<div class="week-content-page">
    <div class="week-header">
        <i class="fas {{ $type === 'learningMaterial' ? 'fa-book-open' : ($type === 'preAssessment' ? 'fa-clipboard' : ($type === 'postAssessment' ? 'fa-clipboard-check' : 'fa-graduation-cap')) }}"></i>
        Edit {{ ucfirst(str_replace('learningMaterial', 'Learning Material', $type)) }}
    </div>
    <div class="week-subheader">Update the content</div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('teacher.content-library.update', [$grade, $term, $subject, $week, $type, $item->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if($type === 'learningMaterial')
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $item->title) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $item->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Current File</label>
                        @if($item->file_path)
                            <p><a href="{{ Storage::url($item->file_path) }}" target="_blank">{{ basename($item->file_path) }}</a></p>
                        @else
                            <p>No file uploaded.</p>
                        @endif
                        <label for="file">Upload New File (optional)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".pdf,.ppt,.pptx,.doc,.docx,.mp4,.mov,.avi,.webm">
                    </div>
                @elseif(in_array($type, ['preAssessment', 'postAssessment']))
                    <div class="form-group">
                        <label for="exam_type">Exam Type</label>
                        <select name="exam_type" id="exam_type" class="form-control">
                            <option value="multipleChoice" {{ $item->exam_type == 'multipleChoice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="trueFalse" {{ $item->exam_type == 'trueFalse' ? 'selected' : '' }}>True or False</option>
                            <option value="matchingType" {{ $item->exam_type == 'matchingType' ? 'selected' : '' }}>Matching Type</option>
                            <option value="mixed" {{ $item->exam_type == 'mixed' ? 'selected' : '' }}>Mixed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="input_method">Input Method</label>
                        <select name="input_method" id="input_method" class="form-control">
                            <option value="upload" {{ $item->input_method == 'upload' ? 'selected' : '' }}>Upload File</option>
                            <option value="manual" {{ $item->input_method == 'manual' ? 'selected' : '' }}>Manual Input</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="questions">Questions (JSON)</label>
                        <textarea name="questions" id="questions" class="form-control" rows="10">{{ old('questions', json_encode($item->questions, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="settings">Settings (JSON)</label>
                        <textarea name="settings" id="settings" class="form-control" rows="5">{{ old('settings', json_encode($item->settings, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Current File</label>
                        <p>{{ $item->file_name ?? 'None' }}</p>
                        <label for="file">Upload New File (optional)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls">
                    </div>
                @elseif($type === 'intervention')
                    <div class="form-group">
                        <label for="level">Level</label>
                        <select name="level" id="level" class="form-control">
                            <option value="basic" {{ $item->level == 'basic' ? 'selected' : '' }}>Basic</option>
                            <option value="standard" {{ $item->level == 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="advanced" {{ $item->level == 'advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                    <!-- Add other fields as needed -->
                @endif

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('teacher.content-library.content', [$grade, $term, $subject, $week]) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection