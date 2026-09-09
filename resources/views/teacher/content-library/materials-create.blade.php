@extends('layouts.teacher-student')

@section('page_title', 'Add Learning Material - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>
<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-book-open"></i> Add Learning Material
    </div>
    <div class="week-subheader">Manage your learning materials</div>

    <div class="card">

        <div class="card-body">
            <form action="{{ route('teacher.content-library.materials.store', [$grade, $term, $subject, $week]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="title">Module Title:</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label>Upload Module:</label>
                    <div class="file-upload-area" onclick="document.getElementById('file').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload</div>
                        <div class="upload-subtext">PDF / PPT / Videos / DOCX (Max 50MB)</div>
                    </div>
                    <input type="file" name="file" id="file" style="display:none;" accept=".pdf,.ppt,.pptx,.doc,.docx,.mp4,.mov,.avi,.webm">
                </div>
                <div class="form-group mt-3">
                    <div class="modal-buttons">
                    <button type="submit" class="btn-save">Save Material</button>
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