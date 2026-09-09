@extends('layouts.teacher-student')

@section('page_title', 'Add Intervention Videos - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>

<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-video"></i> Add Intervention Videos
    </div>
    <div class="week-subheader">Add video links or upload video files for a specific intervention level</div>

    <div class="card">
        <div class="card-body">
            <form id="videosForm" action="{{ route('teacher.content-library.intervention.videos.store', [$grade, $term, $subject, $week]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Level selector --}}
                <div class="form-group">
                    <label for="level">Select Level:</label>
                    <select name="level" id="level" class="form-control" required>
                        <option value="">-- Select Level --</option>
                        <option value="basic">🔹 Basic (Below Average)</option>
                        <option value="standard" selected>🔹 Standard (Average)</option>
                        <option value="advanced">🔹 Advanced (Above Average)</option>
                    </select>
                </div>

                <div class="option-grid">
                    <button type="button" class="option-btn active" onclick="selectVideoType('link')"><i class="fas fa-link"></i> Video Link</button>
                    <button type="button" class="option-btn" onclick="selectVideoType('file')"><i class="fas fa-file-video"></i> Upload File</button>
                </div>
                <div id="videoLinkInput" class="form-group">
                    <label>Paste Video Link:</label>
                    <input type="url" id="videoLink" class="form-control" placeholder="https://youtube.com/...">
                </div>
                <div id="videoFileInput" class="form-group" style="display:none;">
                    <div class="file-upload-area" onclick="document.getElementById('videoFileInputElem').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload</div>
                        <div class="upload-subtext">MP4 / MOV / AVI (Max 100MB)</div>
                    </div>
                    <input type="file" id="videoFileInputElem" style="display:none;" accept=".mp4,.mov,.avi,.webm">
                </div>
                <button type="button" class="btn btn-save" onclick="addVideo()">
                    <i class="fas fa-plus"></i> Add This Video
                </button>

                <div class="items-list" style="max-height:250px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; margin-top:10px;">
                    <table class="table table-striped">
                        <thead><tr><th>#</th><th>Video</th><th>Actions</th></tr></thead>
                        <tbody id="videosTableBody"></tbody>
                    </table>
                </div>
                <input type="hidden" name="videos_json" id="videosJsonInput" value="">

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-save">Save Videos</button>
                    <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let videos = [];
    let videoType = 'link';

    function selectVideoType(type) {
        videoType = type;
        document.getElementById('videoLinkInput').style.display = (type === 'link') ? 'block' : 'none';
        document.getElementById('videoFileInput').style.display = (type === 'file') ? 'block' : 'none';
        document.querySelectorAll('.option-btn').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }

    function addVideo() {
        let videoData = { video_type: videoType };
        if (videoType === 'link') {
            const url = document.getElementById('videoLink').value.trim();
            if (!url) { alert('Please enter a video link.'); return; }
            videoData.video_url = url;
            document.getElementById('videoLink').value = '';
        } else {
            const fileInput = document.getElementById('videoFileInputElem');
            if (!fileInput.files.length) { alert('Please select a video file.'); return; }
            videoData.file_name = fileInput.files[0].name;
            fileInput.value = '';
        }
        videos.push(videoData);
        renderVideos();
        document.getElementById('videosJsonInput').value = JSON.stringify(videos);
    }

    function renderVideos() {
        const tbody = document.getElementById('videosTableBody');
        tbody.innerHTML = videos.map((v, i) => `
            <tr>
                <td>${i+1}</td>
                <td>${v.video_url || v.file_name || 'Video'}</td>
                <td><button type="button" class="btn btn-sm btn-remove" onclick="removeVideo(${i})">Remove</button></td>
            </tr>
        `).join('');
    }

    function removeVideo(index) {
        videos.splice(index, 1);
        renderVideos();
        document.getElementById('videosJsonInput').value = JSON.stringify(videos);
    }

    document.getElementById('videosForm').addEventListener('submit', function() {
        document.getElementById('videosJsonInput').value = JSON.stringify(videos);
    });
</script>
@endpush