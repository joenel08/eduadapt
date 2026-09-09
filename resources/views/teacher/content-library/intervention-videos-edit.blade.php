@extends('layouts.teacher-student')

@section('page_title', 'Edit Intervention Video - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>
<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-video"></i> Edit Intervention Video
    </div>
    <div class="week-subheader">Update video details</div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('teacher.content-library.update', [$grade, $term, $subject, $week, 'interventionVideo', $item->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Video Type:</label>
                    <select name="video_type" class="form-control">
                        <option value="link" {{ $item->video_type == 'link' ? 'selected' : '' }}>Link</option>
                        <option value="file" {{ $item->video_type == 'file' ? 'selected' : '' }}>File</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Video URL (if link):</label>
                    <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $item->video_url) }}">
                </div>
                <div class="form-group">
                    <label>File Name (if file):</label>
                    <input type="text" name="file_name" class="form-control" value="{{ old('file_name', $item->file_name) }}">
                </div>
                <div class="form-group mt-3">
                    <div class="modal-buttons">
                        <button type="submit" class="btn-save">Update Video</button>
                        <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection