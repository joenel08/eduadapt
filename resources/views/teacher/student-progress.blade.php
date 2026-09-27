@extends('layouts.teacher-student')

@section('page_title', 'Student Progress')
@section('page', 'classes')

@section('content')
<style>
    /* ===== Summary Cards ===== */
    .progress-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .progress-card {
        position: relative;
        background: #fff;
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        gap: 18px;
        transition: all 0.3s ease;
        overflow: hidden;
        border: 1px solid #eef1f6;
    }

    .progress-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 5px;
        border-radius: 14px 0 0 14px;
    }

    .progress-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.1);
    }

    .progress-card .pc-icon {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .progress-card .pc-body {
        flex: 1;
        min-width: 0;
    }

    .progress-card .pc-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
        color: #8892a0;
        margin-bottom: 6px;
    }

    .progress-card .pc-value {
        font-size: 30px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.1;
    }

    .progress-card .pc-sub {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    /* Variants */
    .progress-card.total::before { background: linear-gradient(180deg, #0066CC, #004D99); }
    .progress-card.total .pc-icon { background: rgba(0, 102, 204, 0.1); color: #0066CC; }

    .progress-card.average-score::before { background: linear-gradient(180deg, #FFB84D, #F59E0B); }
    .progress-card.average-score .pc-icon { background: rgba(255, 184, 77, 0.15); color: #F59E0B; }

    .progress-card.below::before { background: linear-gradient(180deg, #FF6B6B, #DC2626); }
    .progress-card.below .pc-icon { background: rgba(255, 107, 107, 0.12); color: #DC2626; }

    /* Pass/fail chip in the below-passing card */
    .pc-chip {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        margin-top: 4px;
    }
    .pc-chip.danger { background: #fee2e2; color: #991b1b; }
    .pc-chip.success { background: #def7ec; color: #0f6f5d; }
    .pc-chip.neutral { background: #eef2ff; color: #1d4ed8; }
</style>

<div class="breadcrumb">
    <a class="breadcrumb-item" href="{{ route('teacher.classes') }}"><i class="fas fa-home"></i> My Classes</a>
    <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
    <a class="breadcrumb-item" href="{{ url()->previous() }}">{{ $class->grade_level }} • {{ $class->section_name }}</a>
    <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
    <span class="breadcrumb-item active">Student Progress</span>
</div>


<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1 style="font-size:28px;font-weight:700;"><i class="fas fa-user-graduate"></i> {{ $fullName }}</h1>
        <p style="color:#999;">LRN: {{ $student->lrn ?? 'N/A' }} • {{ $class->grade_level }} • {{ $class->section_name }}</p>
    </div>
   
</div>

@php
    $scored = $examRecords->whereNotNull('score');
    $total  = $examRecords->count();
    $avg    = $scored->count() ? round($scored->avg('score'), 1) : null;
    $below  = $scored->where('score', '<', 70)->count();
    $passing = $scored->count() - $below;
@endphp

<!-- Summary Cards -->
<div class="progress-summary">
    {{-- Total Assessments --}}
    <div class="progress-card total">
        <div class="pc-icon"><i class="fas fa-clipboard-check"></i></div>
        <div class="pc-body">
            <div class="pc-label">Total Assessments</div>
            <div class="pc-value">{{ $total }}</div>
            <div class="pc-sub">{{ $scored->count() }} scored • {{ $total - $scored->count() }} pending</div>
        </div>
    </div>

    {{-- Average Score --}}
    <div class="progress-card average-score">
        <div class="pc-icon"><i class="fas fa-chart-line"></i></div>
        <div class="pc-body">
            <div class="pc-label">Average Score</div>
            <div class="pc-value">{{ $avg !== null ? $avg : '—' }}<span style="font-size:16px;font-weight:600;color:#94a3b8;">{{ $avg !== null ? '/100' : '' }}</span></div>
            <div class="pc-sub">
                @if($avg === null)
                    No scores yet
                @elseif($avg >= 85)
                    <span class="pc-chip success">Advanced</span>
                @elseif($avg >= 70)
                    <span class="pc-chip neutral">Average</span>
                @else
                    <span class="pc-chip danger">Needs Improvement</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Below Passing --}}
    <div class="progress-card below">
        <div class="pc-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="pc-body">
            <div class="pc-label">Below Passing</div>
            <div class="pc-value">{{ $below }}</div>
            <div class="pc-sub">{{ $passing }} passing · {{ $scored->count() }} total scored</div>
        </div>
    </div>
</div>

<!-- Exam Records -->
<div class="table-card">
    <table class="student-table">
        <thead>
            <tr>
                <th>Assessment</th>
                <th>Week</th>
                <th>Score</th>
                <th>Date Taken</th>
                <th>Recording</th>
            </tr>
        </thead>
        <tbody>
            @forelse($examRecords as $record)
            <tr>
                <td>{{ $record->exam_type }}</td>
                <td>{{ $record->week }}</td>
                <td>
                    @if($record->score !== null)
                        <span style="font-weight:600;">{{ $record->score }} / 100</span>
                    @else
                        <span style="color:#999;">—</span>
                    @endif
                </td>
                <td>{{ $record->created_at->format('M d, Y g:i A') }}</td>
                <td>
                    <button class="btn-view"
                        data-student="{{ json_encode($fullName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                        data-assessment="{{ json_encode($record->exam_type, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                        data-score="{{ json_encode($record->score, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                        data-date="{{ json_encode($record->created_at->format('M d, Y'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                        data-video="{{ json_encode($record->video_path ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                        onclick="openVideoModalFromData(this)">
                        <i class="fas fa-play-circle"></i> View
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#64748b;">No exam records found for this student.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- VIDEO PLAYER MODAL -->
<div class="modal-overlay" id="videoModalOverlay">
    <div class="video-modal">
        <div class="video-modal-header">
            <div class="video-modal-header-content">
                <h2 class="video-modal-title"><i class="fas fa-film"></i> <span id="videoModalTitle">Exam Recording</span></h2>
                <div class="video-modal-info">
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Student</div>
                        <div class="video-modal-info-value" id="videoStudentName">-</div>
                    </div>
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Assessment</div>
                        <div class="video-modal-info-value" id="videoAssessmentType">-</div>
                    </div>
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Score</div>
                        <div class="video-modal-info-value" id="videoScore">-</div>
                    </div>
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Date Taken</div>
                        <div class="video-modal-info-value" id="videoDateTaken">-</div>
                    </div>
                </div>
            </div>
            <button class="video-modal-close" onclick="closeVideoModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="video-modal-body">
            <div class="video-player-wrapper">
                <div class="video-placeholder" id="videoPlaceholder">
                    <div class="video-placeholder-icon"><i class="fas fa-video"></i></div>
                    <div class="video-placeholder-text">No recording available for this exam.</div>
                </div>
                <video id="videoPlayer" class="video-player" style="display: none;" controls>
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="video-download-wrapper" id="videoDownloadWrapper" style="display:none; text-align:center; margin-top:15px;">
                <a id="videoDownloadLink" href="#" download="exam_recording.mp4" class="btn btn-download">
                    <i class="fas fa-download"></i> Download Video
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.openVideoModalFromData = function(button) {
        var studentName = button.dataset.student;
        var assessment = button.dataset.assessment;
        var score = button.dataset.score;
        var dateTaken = button.dataset.date;
        var videoPath = button.dataset.video;

        document.getElementById('videoStudentName').textContent = studentName;
        document.getElementById('videoAssessmentType').textContent = assessment;
        document.getElementById('videoScore').textContent = score;
        document.getElementById('videoDateTaken').textContent = dateTaken;
        document.getElementById('videoModalTitle').textContent = assessment + ' - ' + studentName;

        var placeholder = document.getElementById('videoPlaceholder');
        var videoPlayer = document.getElementById('videoPlayer');
        var downloadWrapper = document.getElementById('videoDownloadWrapper');
        var downloadLink = document.getElementById('videoDownloadLink');

        if (videoPath && videoPath !== '') {
            placeholder.style.display = 'none';
            videoPlayer.style.display = 'block';
            videoPlayer.querySelector('source').src = videoPath;
            videoPlayer.load();

            downloadWrapper.style.display = 'block';
            downloadLink.href = videoPath;
            var fileName = videoPath.split('/').pop() || 'exam_recording.mp4';
            downloadLink.download = fileName;
        } else {
            placeholder.style.display = 'flex';
            videoPlayer.style.display = 'none';
            downloadWrapper.style.display = 'none';
        }

        document.getElementById('videoModalOverlay').classList.add('active');
    };

    window.closeVideoModal = function() {
        document.getElementById('videoModalOverlay').classList.remove('active');
        var videoPlayer = document.getElementById('videoPlayer');
        if (videoPlayer) videoPlayer.pause();
    };

    document.addEventListener('DOMContentLoaded', function () {
        var overlay = document.getElementById('videoModalOverlay');
        if (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) closeVideoModal();
            });
        }
    });
</script>
@endpush