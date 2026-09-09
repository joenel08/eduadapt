@extends('layouts.teacher-student')

@section('page_title', 'Edit Learning Material - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>
<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-book-open"></i> Edit Learning Material
    </div>
    <div class="week-subheader">Update your learning material</div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('teacher.content-library.update', [$grade, $term, $subject, $week, 'learningMaterial', $item->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="title">Module Title:</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $item->title) }}" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $item->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Current File:</label>
                    @if($item->file_path)
                        <p><a href="{{ Storage::url($item->file_path) }}" target="_blank">{{ basename($item->file_path) }}</a></p>
                    @else
                        <p>No file uploaded.</p>
                    @endif
                    <label for="file">Upload New File (optional):</label>
                    <div class="file-upload-area" onclick="document.getElementById('file').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload</div>
                        <div class="upload-subtext">PDF / PPT / Videos / DOCX (Max 50MB)</div>
                    </div>
                    <input type="file" name="file" id="file" style="display:none;" accept=".pdf,.ppt,.pptx,.doc,.docx,.mp4,.mov,.avi,.webm">
                </div>
                <div class="form-group mt-3">
                    <div class="modal-buttons">
                        <button type="submit" class="btn-save">Update Material</button>
                        <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('file').addEventListener('change', function() {
        const nameEl = document.querySelector('.file-upload-area .upload-text');
        if (this.files && this.files[0]) {
            nameEl.textContent = this.files[0].name;
        }
    });
</script>
@endpush