@extends('layouts.teacher-student')

@section('page_title', $class->grade_level . ' - ' . $class->section_name)
@section('page', 'class-details')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="{{ route('student.classes') }}">
        <i class="fas fa-home"></i> My Classes
    </a>
    <i class="fas fa-chevron-right"></i>
    <span>{{ $class->grade_level }} - {{ $class->section_name }}</span>
</div>

<h1 class="page-title">
    <i class="fas fa-book"></i>
    <span>{{ $class->grade_level }} - {{ $class->section_name }}</span>
</h1>
<p class="page-subtitle">
    {{ $selectedSubject->name ?? 'General' }} — Learning Path
</p>

@if($lessonMaterials->isEmpty() && !$preAssessment && !$postAssessment && $interventionMaterials->isEmpty() && $interventionVideos->isEmpty() && !$interventionQuiz)
<div class="lesson-placeholder">
    <i class="fas fa-file-lines"></i>
    <p>No content available for this subject yet.</p>
</div>
@else
<div class="due-date-section" id="dueDateSection">
    <div class="due-date-text">
        <i class="fas fa-calendar-check"></i> Due Date:
    </div>
    <div class="due-date-value" id="dueDateValue">—</div>
</div>

<!-- Step Navigation Buttons -->
<!-- Step Navigation Buttons -->
<div class="filter-buttons" id="filterButtons">
    <button class="filter-btn {{ $access['materials_done'] ? 'disabled completed' : 'active' }}" onclick="goToStep(1)" id="btn-step-1">
        <i class="fas fa-book"></i> Lesson Materials
        @if($access['materials_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ ($access['pre_assessment'] && !$access['pre_done']) ? '' : 'disabled' }} {{ $access['pre_done'] ? 'completed' : '' }}" onclick="goToStep(2)" id="btn-step-2">
        <i class="fas fa-question-circle"></i> Pre-Assessment
        @if(!$access['pre_assessment'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['pre_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ ($access['post_assessment'] && !$access['post_done']) ? '' : 'disabled' }} {{ $access['post_done'] ? 'completed' : '' }}" onclick="goToStep(3)" id="btn-step-3">
        <i class="fas fa-star"></i> Post-Assessment
        @if(!$access['post_assessment'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['post_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ $access['intervention'] ? '' : 'disabled' }} {{ ($access['intervention_materials_done'] && $access['quiz_done']) ? 'completed' : '' }}" onclick="goToStep(4)" id="btn-step-4">
        <i class="fas fa-lightbulb"></i> Learning Intervention
        @if(!$access['intervention'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['intervention_materials_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
        @if($access['intervention_materials_done'] && $access['quiz_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ ($access['intervention_quiz'] && !$access['quiz_done']) ? '' : 'disabled' }} {{ $access['quiz_done'] ? 'completed' : '' }}" onclick="goToStep(5)" id="btn-step-5">
        <i class="fas fa-list-check"></i> Mini Quiz
        @if(!$access['intervention_quiz'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['quiz_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
</div>

<!-- STEP 1: LESSON MATERIALS -->
<div class="step-content" id="step-1">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-book"></i>
            <span>Lesson: {{ $class->grade_level }} - {{ $class->section_name }}</span>
        </div>
        <div class="section-subtitle">Study the assigned learning materials below</div>
        <div style="margin-bottom:16px;">
            <span style="font-weight:600;">Progress:</span>
            <span id="lessonProgress">{{ $lessonProgress }}%</span>
            <div style="width:100%; height:6px; background:#e9ecef; border-radius:4px; margin-top:4px;">
                <div style="width:{{ $lessonProgress }}%; height:100%; background:linear-gradient(90deg, #0066CC, #00AA66); border-radius:4px;" id="lessonProgressBar"></div>
            </div>
        </div>
        <div id="lessonMaterialsList">
            @forelse($lessonMaterials as $material)
            <div class="material-item" data-id="{{ $material->id }}" data-type="{{ get_class($material) }}">
                <div class="material-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="material-info">
                    <div class="material-name">{{ $material->title }}</div>
                    <div class="material-type">{{ $material->description ?? 'No description' }}</div>
                </div>
                <div class="material-status">
                    @if($material->progress === 'completed')
                    <i class="fas fa-check-circle" style="color:#00AA66;"></i>
                    @elseif($material->progress === 'viewed')
                    <i class="fas fa-eye" style="color:#FF9800;"></i>
                    @else
                    <i class="fas fa-clock" style="color:#999;"></i>
                    @endif
                </div>
                <div class="material-download">
                    @if($material->file_url)
                    <a href="{{ $material->file_url }}" target="_blank" class="download-btn">
                        <i class="fas fa-download"></i>
                    </a>
                    @endif
                </div>
                @php
                $isDone = in_array($material->progress, ['viewed', 'completed']);
                @endphp
                @if(!$materialsLocked && !$access['materials_done'])
                @if(!$isDone)
                <button class="btn-mark-viewed" onclick="markMaterialViewed(this, {{ json_encode(get_class($material)) }}, {{ $material->id }}, {{ $class->id }})">
                    Mark as Viewed
                </button>
                @else
                <span class="text-muted">Already {{ $material->progress }}</span>
                @endif
                @else
                <span class="text-muted">🔒 {{ $access['materials_done'] ? 'Completed' : 'Locked' }}</span>
                @endif
            </div>
            @empty
            <div class="lesson-placeholder">
                <i class="fas fa-file-lines"></i>
                <p>No learning materials available for this lesson yet.</p>
            </div>
            @endforelse
        </div>
        @if(!$materialsLocked && !$access['materials_done'])
        <button class="btn-continue" onclick="completeStep(1)">
            <i class="fas fa-check"></i> Mark Lesson as Completed
        </button>
        @else
        <button class="btn-continue" disabled style="opacity:0.5; cursor:not-allowed;">
            <i class="fas fa-lock"></i> {{ $access['materials_done'] ? 'Completed' : 'Locked' }}
        </button>
        @endif
    </div>
</div>

<!-- STEP 2: PRE-ASSESSMENT -->
<div class="step-content" id="step-2" style="display: none;">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-question-circle"></i>
            <span>Pre-Assessment: {{ $class->subject->name ?? 'General' }}</span>
        </div>
        <div class="section-subtitle">
            @if($preAssessment)
            Time remaining: <span id="pre-timer">--:--</span>
            @else
            No pre-assessment assigned.
            @endif
        </div>
        @if($preAssessment)
        <div id="preAssessmentContainer">
            @include('student.partials.assessment', ['assessment' => $preAssessment, 'step' => 'pre'])
        </div>
        <button class="btn-continue" onclick="submitAssessment('pre')">
            <i class="fas fa-check"></i> Submit Pre-Assessment
        </button>
        @else
        <div class="lesson-placeholder">
            <i class="fas fa-file-lines"></i>
            <p>No pre-assessment assigned.</p>
        </div>
        @endif
    </div>
</div>

<!-- STEP 3: POST-ASSESSMENT -->
<div class="step-content" id="step-3" style="display: none;">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-star"></i>
            <span>Post-Assessment: {{ $class->subject->name ?? 'General' }}</span>
        </div>
        <div class="section-subtitle">
            @if($postAssessment)
            Time remaining: <span id="post-timer">--:--</span>
            @else
            No post-assessment assigned.
            @endif
        </div>
        @if($postAssessment)
        <div id="postAssessmentContainer">
            @include('student.partials.assessment', ['assessment' => $postAssessment, 'step' => 'post'])
        </div>
        <button class="btn-continue" onclick="submitAssessment('post')">
            <i class="fas fa-check"></i> Submit Post-Assessment
        </button>
        @else
        <div class="lesson-placeholder">
            <i class="fas fa-file-lines"></i>
            <p>No post-assessment assigned.</p>
        </div>
        @endif
    </div>
</div>

<!-- STEP 4: LEARNING INTERVENTION -->
<div class="step-content" id="step-4" style="display: none;">
    <div class="score-section" id="postAssessmentScore">
        <div class="score-text">Post-Assessment Score</div>
        <div class="score-display">—</div>
        <div class="score-label">—</div>
    </div>

    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-lightbulb"></i>
            Personalized Learning Intervention
        </div>
        <div class="section-subtitle">Review the recommended materials and videos, then take the mini quiz.</div>

        @if($interventionMaterials->isNotEmpty())
        <div style="margin: 20px 0;">
            <h4>Intervention Materials</h4>
            @foreach($interventionMaterials as $material)
            <div class="material-item" data-id="{{ $material->id }}" data-type="{{ get_class($material) }}">
                <div class="material-icon"><i class="fas fa-file-alt"></i></div>
                <div class="material-info">
                    <div class="material-name">{{ $material->title }}</div>
                    <div class="material-type">{{ $material->description ?? '' }}</div>
                </div>
                <div class="material-status">
                    @if($material->progress === 'viewed' || $material->progress === 'completed')
                    <i class="fas fa-check-circle" style="color:#00AA66;"></i>
                    @else
                    <i class="fas fa-clock" style="color:#999;"></i>
                    @endif
                </div>
                @php
                $isDone = in_array($material->progress, ['viewed', 'completed']);
                @endphp
                @if(!$materialsLocked && !$access['intervention_materials_done'])
                @if(!$isDone)
                <button class="btn-mark-viewed" onclick="markMaterialViewed(this, '{{ get_class($material) }}', {{ $material->id }}, {{ $class->id }}, 'completed')">
                    Mark as Completed
                </button>
                @else
                <span class="text-muted">✓ {{ ucfirst($material->progress) }}</span>
                @endif
                @else
                <span class="text-muted">🔒 {{ $access['intervention_materials_done'] ? 'Completed' : 'Locked' }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($interventionVideos->isNotEmpty())
        <div style="margin: 20px 0;">
            <h4>Intervention Videos</h4>
            @foreach($interventionVideos as $video)
            <div class="material-item" data-id="{{ $video->id }}" data-type="{{ get_class($video) }}">
                <div class="material-icon"><i class="fas fa-video"></i></div>
                <div class="material-info">
                    <div class="material-name">{{ $video->title }}</div>
                    <div class="material-type">{{ $video->description ?? '' }}</div>

                    <!-- Video Player -->
                    <div style="margin-top: 10px; max-width: 560px;">
                        @if($video->video_type === 'link' && $video->video_url)
                        @php
                        $embedUrl = $video->video_url;
                        if (strpos($video->video_url, 'youtube.com/watch?v=') !== false) {
                        parse_str(parse_url($video->video_url, PHP_URL_QUERY), $query);
                        $embedUrl = 'https://www.youtube.com/embed/' . ($query['v'] ?? '');
                        } elseif (strpos($video->video_url, 'youtu.be/') !== false) {
                        $embedUrl = 'https://www.youtube.com/embed/' . substr($video->video_url, strrpos($video->video_url, '/') + 1);
                        }
                        @endphp
                        <iframe width="100%" height="315" src="{{ $embedUrl }}" frameborder="0" allowfullscreen></iframe>
                        @elseif($video->video_type === 'file' && $video->file_path)
                        <video width="100%" height="240" controls>
                            <source src="{{ asset('storage/' . $video->file_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        @else
                        <p class="text-muted">Video not available</p>
                        @endif
                    </div>
                </div>

                <div class="material-status">
                    @if($video->progress === 'viewed' || $video->progress === 'completed')
                    <i class="fas fa-check-circle" style="color:#00AA66;"></i>
                    @else
                    <i class="fas fa-clock" style="color:#999;"></i>
                    @endif
                </div>

                @php
                $isDone = in_array($video->progress, ['viewed', 'completed']);
                @endphp

                @if(!$materialsLocked && !$access['intervention_materials_done'])
                @if(!$isDone)
                <button class="btn-mark-viewed" onclick="markMaterialViewed(this, '{{ get_class($video) }}', {{ $video->id }}, {{ $class->id }}, 'completed')">
                    Mark as Completed
                </button>
                @else
                <span class="text-muted">✓ {{ ucfirst($video->progress) }}</span>
                @endif
                @else
                <span class="text-muted">🔒 {{ $access['intervention_materials_done'] ? 'Completed' : 'Locked' }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if(!$access['intervention_materials_done'] && !$materialsLocked)
        <div style="margin-top: 20px; text-align: center;">
            <button class="btn-continue" onclick="markAllInterventionCompleted()" style="background: linear-gradient(135deg, #00AA66, #008C52);">
                <i class="fas fa-check-double"></i> Mark All as Completed
            </button>
            <p style="font-size: 12px; color: #999; margin-top: 8px;">
                This will mark all intervention materials and videos as completed and unlock the Mini Quiz.
            </p>
        </div>
        @elseif($access['intervention_materials_done'])
        <p style="color: #00AA66; text-align: center; margin-top: 20px;">
            <i class="fas fa-check-circle"></i> All intervention materials completed! Proceed to the Mini Quiz.
        </p>
        @endif
        @if($access['intervention_materials_done'])
        <button class="btn-continue" onclick="goToStep(5)" style="margin-top:20px;">
            <i class="fas fa-arrow-right"></i> Proceed to Mini Quiz
        </button>
        @else
        <p style="color:#999; margin-top:20px;">Complete all intervention materials to unlock the quiz.</p>
        @endif
    </div>
</div>

<!-- STEP 5: INTERVENTION QUIZ (Mini Quiz) -->
<div class="step-content" id="step-5" style="display: none;">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-list-check"></i>
            <span>Mini Quiz: {{ $class->subject->name ?? 'General' }}</span>
        </div>
        <div class="section-subtitle">
            @if($interventionQuiz)
            Time remaining: <span id="quiz-timer">--:--</span>
            @else
            No mini quiz assigned.
            @endif
        </div>
        @if($interventionQuiz)
        <div id="interventionQuizContainer">
            @include('student.partials.assessment', ['assessment' => $interventionQuiz, 'step' => 'quiz'])
        </div>
        <button class="btn-continue" onclick="submitAssessment('quiz')">
            <i class="fas fa-check"></i> Submit Quiz
        </button>
        @else
        <div class="lesson-placeholder">
            <i class="fas fa-file-lines"></i>
            <p>No intervention quiz assigned.</p>
        </div>
        @endif
    </div>
</div>

<!-- FLOATING CAMERA -->
<div class="floating-camera" id="floatingCamera">
    <div class="floating-camera-header">
        <div class="floating-camera-title">
            <div class="floating-camera-status"></div>
            <span>Proctoring Camera</span>
        </div>
        <button class="floating-camera-close" onclick="closeFloatingCamera()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <video id="floatingCameraVideo" autoplay playsinline muted></video>
</div>

<!-- EXAM START WARNING MODAL -->
<div class="exam-start-modal" id="examStartModal">
    <div class="exam-start-content">
        <div class="exam-start-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="exam-start-title">Ready to Start Assessment?</div>
        <div class="exam-start-subtitle" id="examStartSubtitle">Pre-Assessment</div>

        <div class="warning-label">
            <i class="fas fa-exclamation-triangle"></i> Important Notice
        </div>
        <div class="warning-list">
            <div class="warning-item">
                <i class="fas fa-times-circle"></i>
                <span><strong>Do NOT open new tabs</strong> – any attempt to leave will auto‑submit your answers.</span>
            </div>
            <div class="warning-item">
                <i class="fas fa-times-circle"></i>
                <span><strong>Do NOT minimize the window</strong> – any attempt to leave will auto‑submit your answers.</span>
            </div>
            <div class="warning-item">
                <i class="fas fa-times-circle"></i>
                <span><strong>Do NOT switch windows</strong> – any attempt to leave will auto‑submit your answers.</span>
            </div>
            <div class="warning-item">
                <i class="fas fa-times-circle"></i>
                <span><strong>Camera must be active</strong> – Required for proctoring.</span>
            </div>
        </div>

        <div class="requirements-list">
            <div class="warning-label" style="color: #0066CC; margin-bottom: 15px;">
                <i class="fas fa-check-circle"></i> Requirements
            </div>
            <div class="requirement-item">
                <i class="fas fa-video"></i>
                <span>Stable internet connection and working camera</span>
            </div>
            <div class="requirement-item">
                <i class="fas fa-desktop"></i>
                <span>Keep this window in focus throughout the exam</span>
            </div>
            <div class="requirement-item">
                <i class="fas fa-clock"></i>
                <span id="examTimeRequirement">You have <strong><span id="examTimeDisplay">--</span> minutes</strong> to complete this assessment</span>
            </div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="understandWarning">
            <label for="understandWarning">
                I understand that any attempt to leave the page will automatically submit my answers.
            </label>
        </div>

        <div class="exam-start-buttons">
            <button class="exam-start-btn cancel" onclick="closeExamStartModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="exam-start-btn start" onclick="startExamAssessment()" id="startExamBtn" disabled>
                <i class="fas fa-play"></i> Start Assessment
            </button>
        </div>
    </div>
</div>

<!-- WARNING POPUP (only for materials) -->
<div class="warning-popup-overlay" id="warningPopupOverlay"></div>
<div class="warning-popup" id="warningPopup">
    <div class="warning-popup-icon" id="warningPopupIcon">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="warning-popup-title" id="warningPopupTitle">
        WARNING
    </div>
    <div class="warning-popup-text" id="warningPopupText">
        You have switched tabs or minimized the window while viewing lesson materials.
    </div>
    <div class="warning-popup-remaining" id="warningPopupRemaining">
        Remaining Violations: 2
    </div>
    <button class="warning-popup-btn" onclick="closeWarningPopup()">
        Continue
    </button>
</div>

<!-- SCORE RESULT MODAL -->
<div class="score-result-overlay" id="scoreResultOverlay">
    <div class="score-result-modal">
        <div class="score-result-icon" id="scoreResultIcon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="score-result-title" id="scoreResultTitle">Assessment Complete!</div>
        <div class="score-result-score" id="scoreResultScore">85%</div>
        <div class="score-result-message" id="scoreResultMessage">
            Great job! You've completed your assessment.
        </div>
        <button class="score-result-btn" onclick="closeScoreResultModal()">
            Continue
        </button>
    </div>
</div>

<!-- CAMERA REQUEST MODAL -->
<div class="modal-overlay" id="cameraModal">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-camera"></i>
        </div>
        <div class="modal-title">Camera Required</div>
        <div class="modal-text">
            Your camera is required for proctoring. Please allow access to your camera to proceed.
        </div>
        <div class="modal-buttons">
            <button class="modal-btn secondary" onclick="closeCameraModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="modal-btn primary" onclick="requestCameraAccess()">
                <i class="fas fa-check"></i> Allow Camera
            </button>
        </div>
    </div>
</div>


@endif

<!-- CONGRATULATIONS MODAL -->
<div class="congrats-overlay" id="congratsOverlay">
    <div class="congrats-modal">
        <div class="congrats-icon">
            <i class="fas fa-trophy"></i>
        </div>

        <h2 class="congrats-title">🎉 Congratulations!</h2>

        <p class="congrats-subtitle">
            You have completed <span class="congrats-highlight">all assessments</span> for this subject.
            Your hard work and dedication have paid off!
        </p>

        <div class="congrats-stats">
            <div class="congrats-stat">
                <div class="congrats-stat-icon"><i class="fas fa-book"></i></div>
                <div class="congrats-stat-value">{{ $class->grade_level ?? '—' }}</div>
                <div class="congrats-stat-label">Grade</div>
            </div>
            <div class="congrats-stat">
                <div class="congrats-stat-icon"><i class="fas fa-users"></i></div>
                <div class="congrats-stat-value">{{ $class->section_name ?? '—' }}</div>
                <div class="congrats-stat-label">Section</div>
            </div>
            <div class="congrats-stat">
                <div class="congrats-stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="congrats-stat-value">100%</div>
                <div class="congrats-stat-label">Completed</div>
            </div>
        </div>

       <button class="congrats-btn" onclick="closeCongratsModal()">
    <i class="fas fa-check"></i> Back to My Classes
</button>
    </div>
</div>
@endsection



@push('scripts')
<script>
    window.CLASSES_URL = "{{ route('student.classes') }}";
</script>
<script>
    
    // ---------- PHP-to-JS Variables ----------
    const classId = {{ $class -> id }};

    const preAssessmentClass = @json($preAssessment ? get_class($preAssessment) : null);
    const preAssessmentId = @json($preAssessment ?-> id);

    const postAssessmentClass = @json($postAssessment ? get_class($postAssessment) : null);
    const postAssessmentId = @json($postAssessment ?-> id);

    const quizAssessmentClass = @json($interventionQuiz ? get_class($interventionQuiz) : null);
    const quizAssessmentId = @json($interventionQuiz ?-> id);

    // Time limits (in minutes) from controller
    const preTimeLimit = {{$preTimeLimit }};
    const postTimeLimit = {{ $postTimeLimit}};
    const quizTimeLimit = {{$quizTimeLimit}};

    // ---------- Application State ----------
    let currentStep = 1;
    let examActive = false;
    let materialsActive = false;
    let examStep = null; // 'pre', 'post', 'quiz'
    let materialsWarningCount = 0;
    const MAX_WARNINGS = 3;
    let timerInterval = null;
    let mediaRecorder = null;
    let recordedChunks = [];
    let cameraStreams = {};
    let pendingExamStep = null;







// ---------- Congratulations Modal ----------
let congratsRedirectTimer = null;

function showCongratsModal() {
    const overlay = document.getElementById('congratsOverlay');
    if (!overlay) return;

    overlay.classList.add('active');

    // Clean up any lingering exam state
    if (typeof stopFloatingCamera === 'function') stopFloatingCamera();
    if (typeof timerInterval !== 'undefined' && timerInterval) clearInterval(timerInterval);
    examActive = false;

    // Auto-redirect after 5 seconds
    congratsRedirectTimer = setTimeout(() => {
        window.location.href = window.CLASSES_URL;
    }, 5000);
}

function closeCongratsModal() {
    const overlay = document.getElementById('congratsOverlay');
    if (overlay) overlay.classList.remove('active');

    // Cancel the auto-redirect timer if user clicked Done first
    if (congratsRedirectTimer) {
        clearTimeout(congratsRedirectTimer);
        congratsRedirectTimer = null;
    }

    // Redirect to My Classes
    window.location.href = window.CLASSES_URL;
}

// Close on overlay click (also triggers redirect)
document.addEventListener('click', function (e) {
    const overlay = document.getElementById('congratsOverlay');
    if (overlay && overlay.classList.contains('active') && e.target === overlay) {
        closeCongratsModal();
    }
});

// Close on Escape key (also triggers redirect)
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('congratsOverlay');
        if (overlay && overlay.classList.contains('active')) {
            closeCongratsModal();
        }
    }
});






    // ---------- Navigation ----------
    function goToStep(step) {
        const btn = document.getElementById(`btn-step-${step}`);

        if (btn && btn.classList.contains('completed')) {
            alert('This step is already completed.');
            return;
        }

        if (btn && btn.classList.contains('disabled')) {
            alert('This step is not yet available.');
            return;
        }

        if ([2, 3, 5].includes(step)) {
            pendingExamStep = step;
            showExamStartModal(step);
            return;
        }

        proceedToStep(step);
    }

    function proceedToStep(step) {
        document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
        const target = document.getElementById(`step-${step}`);
        if (target) target.style.display = 'block';

        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        const btn = document.getElementById(`btn-step-${step}`);
        if (btn) btn.classList.add('active');

        currentStep = step;

        if ([2, 3, 5].includes(step)) {
            materialsActive = false;
            examActive = true;
            examStep = step === 2 ? 'pre' : (step === 3 ? 'post' : 'quiz');
            startFloatingCamera();
            const timeLimit = step === 2 ? preTimeLimit : (step === 3 ? postTimeLimit : quizTimeLimit);
            if (timeLimit) {
                startTimer(step, timeLimit);
            }
        } else if (step === 1 || step === 4) {
            materialsActive = true;
            examActive = false;
            examStep = null;
            materialsWarningCount = 0;
            stopFloatingCamera();
            clearInterval(timerInterval);
        } else {
            materialsActive = false;
            examActive = false;
            examStep = null;
            stopFloatingCamera();
            clearInterval(timerInterval);
        }
    }

    // ---------- Assessment Start Modal ----------
    function showExamStartModal(step) {
        const names = {
            2: 'Pre-Assessment',
            3: 'Post-Assessment',
            5: 'Mini Quiz'
        };
        const times = {
            2: preTimeLimit || 10,
            3: postTimeLimit || 10,
            5: quizTimeLimit || 5
        };
        document.getElementById('examTimeRequirement').innerHTML =
            `You have <strong>${times[step]} minutes</strong> to complete this assessment`;
        document.getElementById('examStartSubtitle').textContent = names[step];
        document.getElementById('examStartModal').classList.add('active');
        document.getElementById('understandWarning').checked = false;
        document.getElementById('startExamBtn').disabled = true;
    }

    function closeExamStartModal() {
        document.getElementById('examStartModal').classList.remove('active');
    }

    function startExamAssessment() {
        if (!document.getElementById('understandWarning').checked) {
            alert('Please confirm you understand the rules.');
            return;
        }
        closeExamStartModal();
        setTimeout(() => {
            proceedToStep(pendingExamStep);
        }, 300);
    }

    document.addEventListener('change', function(e) {
        if (e.target.id === 'understandWarning') {
            document.getElementById('startExamBtn').disabled = !e.target.checked;
        }
    });

    // ---------- Timer ----------
    function startTimer(step, minutes) {
        let seconds = minutes * 60;
        const timerId = step === 2 ? 'pre-timer' : (step === 3 ? 'post-timer' : 'quiz-timer');
        const display = document.getElementById(timerId);
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            seconds--;
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            display.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            if (seconds <= 60) display.style.color = '#FF6B6B';
            if (seconds <= 0) {
                clearInterval(timerInterval);
                alert('⏰ Time is up! Submitting automatically.');
                const stepName = step === 2 ? 'pre' : (step === 3 ? 'post' : 'quiz');
                submitAssessment(stepName, true);
            }
        }, 1000);
    }

    // ---------- Floating Camera ----------
    async function startFloatingCamera() {
        try {
            const video = document.getElementById('floatingCameraVideo');
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: 640,
                    height: 480
                },
                audio: false
            });
            video.srcObject = stream;
            cameraStreams['floatingCamera'] = stream;
            document.getElementById('floatingCamera').classList.add('active');
            startRecording(stream);
        } catch (err) {
            console.warn('Camera access denied:', err);
            document.getElementById('cameraModal').classList.add('active');
        }
    }

    function stopFloatingCamera() {
        if (cameraStreams['floatingCamera']) {
            cameraStreams['floatingCamera'].getTracks().forEach(t => t.stop());
            delete cameraStreams['floatingCamera'];
        }
        const video = document.getElementById('floatingCameraVideo');
        if (video) {
            video.srcObject = null; // release the stream
        }
        document.getElementById('floatingCamera').classList.remove('active');
        stopRecording();
    }

    function closeFloatingCamera() {
        stopFloatingCamera();
    }

    // ---------- Video Recording ----------
    function startRecording(stream) {
        try {
            mediaRecorder = new MediaRecorder(stream, {
                mimeType: 'video/webm'
            });
            recordedChunks = [];
            mediaRecorder.ondataavailable = event => {
                if (event.data.size > 0) recordedChunks.push(event.data);
            };
            mediaRecorder.start();
        } catch (err) {
            console.warn('Recording error:', err);
        }
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
    }

    function getVideoBlob() {
        if (recordedChunks.length === 0) return null;
        return new Blob(recordedChunks, {
            type: 'video/webm'
        });
    }

    // ---------- Warning (only for materials) ----------
    function showWarningPopup(title, message) {
        document.getElementById('warningPopupTitle').textContent = title || 'Warning!';
        document.getElementById('warningPopupText').textContent = message || 'Please return to the page.';
        document.getElementById('warningPopupOverlay').style.display = 'block';
        document.getElementById('warningPopup').style.display = 'block';
    }

    function closeWarningPopup() {
        document.getElementById('warningPopupOverlay').style.display = 'none';
        document.getElementById('warningPopup').style.display = 'none';
    }

    function showProctoringAlert(message) {
        const alert = document.createElement('div');
        alert.className = 'proctoring-alert';
        alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    function markAllInterventionCompleted() {
        if (!confirm('Mark all intervention materials and videos as completed? This will unlock the Mini Quiz.')) {
            return;
        }

        fetch('{{ route("student.intervention.complete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    class_id: classId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update UI: mark all items as completed
                    document.querySelectorAll('#step-4 .material-item').forEach(item => {
                        const statusIcon = item.querySelector('.material-status i');
                        if (statusIcon) {
                            statusIcon.className = 'fas fa-check-circle';
                            statusIcon.style.color = '#00AA66';
                        }
                        const btn = item.querySelector('.btn-mark-viewed');
                        if (btn) {
                            btn.textContent = 'Completed';
                            btn.disabled = true;
                            btn.style.opacity = '0.6';
                            btn.style.cursor = 'not-allowed';
                        }
                    });

                    // Remove the "Mark All" button and show success message
                    const container = document.querySelector('#step-4 .btn-continue')?.closest('div');
                    if (container) {
                        container.innerHTML = `
                    <p style="color: #00AA66; text-align: center; margin-top: 20px;">
                        <i class="fas fa-check-circle"></i> All intervention materials completed! Proceeding to Mini Quiz...
                    </p>
                `;
                    }

                    // Update the step button (step 5) to be active
                    const btnStep5 = document.getElementById('btn-step-5');
                    if (btnStep5) {
                        btnStep5.classList.remove('disabled');
                        const lockBadge = btnStep5.querySelector('.lock-badge');
                        if (lockBadge) lockBadge.remove();
                        if (!btnStep5.querySelector('.fa-check-circle')) {
                            const icon = document.createElement('i');
                            icon.className = 'fas fa-check-circle';
                            icon.style.color = '#00AA66';
                            icon.style.marginLeft = '5px';
                            btnStep5.appendChild(icon);
                        }
                    }

                    // Show success toast
                    showSuccessMessage('✅ All intervention materials completed!');

                    // Navigate to mini quiz after 1.5 seconds
                    setTimeout(() => goToStep(5), 1500);
                } else {
                    alert('Error: ' + (data.message || 'Could not complete intervention items.'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network error.');
            });
    }
    // ---------- Event Listeners ----------
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (materialsActive) {
                materialsWarningCount++;
                if (materialsWarningCount >= MAX_WARNINGS) {
                    // Lock materials
                    fetch('{{ route("student.lock.materials") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                class_id: classId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) window.location.reload();
                        })
                        .catch(err => console.error(err));
                } else {
                    showWarningPopup(
                        'Warning!',
                        `You have switched tabs while viewing materials (${materialsWarningCount}/${MAX_WARNINGS}). After ${MAX_WARNINGS} attempts, materials will be locked.`
                    );
                }
            } else if (examActive) {
                // Assessment: auto-submit immediately
                showProctoringAlert('❌ You left the exam. Submitting automatically.');
                submitAssessment(examStep, true);
            }
        }
    });

    // Remove the window.addEventListener('blur', ...) entirely.

    // window.addEventListener('blur', function() {
    //     if (materialsActive) {
    //         materialsWarningCount++;
    //         if (materialsWarningCount >= MAX_WARNINGS) {
    //             fetch('{{ route("student.lock.materials") }}', {
    //                 method: 'POST',
    //                 headers: {
    //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    //                     'Content-Type': 'application/json',
    //                 },
    //                 body: JSON.stringify({ class_id: classId })
    //             })
    //             .then(res => res.json())
    //             .then(data => {
    //                 if (data.success) window.location.reload();
    //             })
    //             .catch(err => console.error(err));
    //         } else {
    //             showWarningPopup(
    //                 'Warning!',
    //                 `You have switched windows while viewing materials (${materialsWarningCount}/${MAX_WARNINGS}). After ${MAX_WARNINGS} attempts, materials will be locked.`
    //             );
    //         }
    //     } else if (examActive) {
    //         showProctoringAlert('❌ You switched windows during the exam. Submitting automatically.');
    //         submitAssessment(examStep, true);
    //     }
    // });

    // Prevent right-click during exam
    document.addEventListener('contextmenu', function(e) {
        if (examActive) {
            e.preventDefault();
            showProctoringAlert('⚠️ Right-click is disabled during exam');
            return false;
        }
    });

    // Prevent dev tools shortcuts
    document.addEventListener('keydown', function(e) {
        if (examActive) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key))) {
                e.preventDefault();
                showProctoringAlert('⚠️ Developer tools are disabled during exam');
                return false;
            }
        }
    });

    function markMaterialViewed(btn, contentType, contentId, classId, status = 'viewed') {
        const item = btn.closest('.material-item');
        fetch('{{ route("student.content.view") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content_type: contentType,
                    content_id: contentId,
                    class_id: classId,
                    status: status // send the status ('viewed' or 'completed')
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const statusIcon = item.querySelector('.material-status i');
                    if (statusIcon) {
                        statusIcon.className = 'fas fa-check-circle';
                        statusIcon.style.color = '#00AA66';
                    }
                    // Disable the button and change text
                    btn.textContent = status === 'completed' ? 'Completed' : 'Viewed';
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                    btn.style.cursor = 'not-allowed';

                    // For lesson materials, update progress bar
                    if (contentType === 'App\\Models\\ContentItem') {
                        updateLessonProgress(classId);
                    }

                    // Check intervention completion
                    const isIntervention = contentType === 'App\\Models\\InterventionMaterial' || contentType === 'App\\Models\\InterventionVideo';
                    if (isIntervention) {
                        fetch('{{ route("student.intervention.progress") }}?class_id=' + classId)
                            .then(res => res.json())
                            .then(progressData => {
                                if (progressData.complete && progressData.total > 0) {
                                    showSuccessMessage('✅ All intervention materials completed! Proceeding to Mini Quiz...');
                                    setTimeout(() => goToStep(5), 1500);
                                }
                            })
                            .catch(err => console.error(err));
                    }
                } else {
                    alert(data.message || 'Error marking material.');
                }
            })
            .catch(err => console.error(err));
    }
    // ---------- Update Lesson Progress ----------
    function updateLessonProgress() {
        fetch('{{ route("student.content.progress") }}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('lessonProgress').textContent = data.percentage + '%';
                document.getElementById('lessonProgressBar').style.width = data.percentage + '%';
            })
            .catch(err => console.error(err));
    }

    // ---------- Submit Assessment ----------
    function submitAssessment(step, autoSubmit = false) {
        const containerId = step === 'pre' ? 'preAssessmentContainer' :
            (step === 'post' ? 'postAssessmentContainer' : 'interventionQuizContainer');
        const container = document.getElementById(containerId);
        if (!container) return;

        const answers = gatherAnswers(container);
        const contentTypeMap = {
            pre: preAssessmentClass,
            post: postAssessmentClass,
            quiz: quizAssessmentClass
        };
        const contentIdMap = {
            pre: preAssessmentId,
            post: postAssessmentId,
            quiz: quizAssessmentId
        };
        const contentType = contentTypeMap[step];
        const contentId = contentIdMap[step];

        if (!contentType || !contentId) {
            alert('No assessment found.');
            return;
        }

        const formData = new FormData();
        formData.append('content_type', contentType);
        formData.append('content_id', contentId);
        formData.append('answers', JSON.stringify(answers));
        formData.append('auto_submit', autoSubmit ? '1' : '0');

        const videoBlob = getVideoBlob();
        if (videoBlob) {
            formData.append('video', videoBlob, 'recording.webm');
        }

        fetch('{{ route("student.assessment.submit") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 1. Mark current step as completed
                    const btnMap = {
                        pre: 2,
                        post: 3,
                        quiz: 5
                    };
                    const btn = document.getElementById(`btn-step-${btnMap[step]}`);
                    if (btn) {
                        btn.classList.remove('active');
                        btn.classList.add('completed');
                        btn.innerHTML = `<i class="fas fa-check-circle"></i> Completed`;
                        const badge = btn.querySelector('.lock-badge');
                        if (badge) badge.remove();
                    }

                    // 2. Determine next step number
                    let nextStepNumber = null;
                    if (step === 'pre') nextStepNumber = 3;
                    else if (step === 'post') nextStepNumber = 4;
                    else if (step === 'quiz') nextStepNumber = 6; // done

                    // 3. ENABLE next step – aggressively remove any block
                    if (nextStepNumber && nextStepNumber <= 5) {
                        const nextBtn = document.getElementById(`btn-step-${nextStepNumber}`);
                        if (nextBtn) {
                            // Remove all disabling classes and attributes
                            nextBtn.classList.remove('disabled', 'locked');
                            const lockBadge = nextBtn.querySelector('.lock-badge');
                            if (lockBadge) lockBadge.remove();
                            // Ensure it's clickable
                            nextBtn.style.pointerEvents = 'auto';
                            nextBtn.style.opacity = '1';
                            // Add checkmark if not present
                            if (!nextBtn.querySelector('.fa-check-circle')) {
                                const icon = document.createElement('i');
                                icon.className = 'fas fa-check-circle';
                                icon.style.color = '#00AA66';
                                icon.style.marginLeft = '5px';
                                nextBtn.appendChild(icon);
                            }
                        }
                    }
                    if (step === 'quiz') {
                        const btn4 = document.getElementById('btn-step-4');
                        if (btn4) {
                            btn4.classList.add('completed');
                            btn4.classList.remove('disabled');
                            // Optionally remove lock badge
                            const lockBadge = btn4.querySelector('.lock-badge');
                            if (lockBadge) lockBadge.remove();
                            // Add checkmark if not present
                            if (!btn4.querySelector('.fa-check-circle')) {
                                const icon = document.createElement('i');
                                icon.className = 'fas fa-check-circle';
                                icon.style.color = '#00AA66';
                                icon.style.marginLeft = '5px';
                                btn4.appendChild(icon);
                            }
                        }
                    }

                    // 4. Show score modal
                    showScoreResultModal(data.score, data.total, step);

                    // 5. Stop camera & timer
                    stopFloatingCamera();
                    clearInterval(timerInterval);
                    examActive = false;
                } else {
                    alert('Error submitting assessment.');
                }
            })
            .catch(err => console.error(err));
    }
    // ---------- Gather Answers ----------
    function gatherAnswers(container) {
        const questions = container.querySelectorAll('.question-item');
        const answers = {};
        questions.forEach((q, index) => {
            const type = q.dataset.type;
            if (type === 'multipleChoice' || type === 'trueFalse') {
                const selected = q.querySelector('input[type="radio"]:checked');
                answers[index] = selected ? selected.value : null;
            } else if (type === 'matchingType') {
                const selects = q.querySelectorAll('select');
                const pairAnswers = [];
                selects.forEach(sel => pairAnswers.push(sel.value));
                answers[index] = pairAnswers;
            }
        });
        return answers;
    }

    // ---------- Score Result Modal ----------
    function showScoreResultModal(score, total, step) {
        const overlay = document.getElementById('scoreResultOverlay');
        const title = document.getElementById('scoreResultTitle');
        const scoreDisplay = document.getElementById('scoreResultScore');
        const message = document.getElementById('scoreResultMessage');

        const names = {
            pre: 'Pre-Assessment',
            post: 'Post-Assessment',
            quiz: 'Mini Quiz'
        };
        title.textContent = names[step] + ' Complete!';
        scoreDisplay.textContent = `${score}/${total}`;
        const percentage = Math.round((score / total) * 100);
        message.textContent = `You scored ${percentage}%. Great job!`;

        overlay.classList.add('active');
    }

    function closeScoreResultModal() {
        document.getElementById('scoreResultOverlay').classList.remove('active');
        const stepMap = {
            2: 3,
            3: 4,
            5: 6
        };
        let next = stepMap[currentStep];
        // Skip steps that are marked as completed (meaning no content)
        while (next && next <= 5) {
            const btn = document.getElementById(`btn-step-${next}`);
            if (btn && btn.classList.contains('completed')) {
                // This step has no content (e.g., no assessment), skip it
                next = stepMap[next] || 6;
            } else {
                break;
            }
        }
        if (next && next <= 5) {
            // For assessment steps, show start modal; for others, just proceed
            if ([2, 3, 5].includes(next)) {
                goToStep(next);
            } else {
                proceedToStep(next);
            }
        } else if (next === 6) {
            showCongratsModal();
        }
    }
    // ---------- Complete Lesson ----------
    // ---------- Complete Lesson ----------
    function completeStep(step) {
        if (step !== 1) return;

        fetch('{{ route("student.lesson.complete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    class_id: classId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update UI (lesson materials, progress, buttons)
                    document.querySelectorAll('#lessonMaterialsList .material-item').forEach(item => {
                        const statusIcon = item.querySelector('.material-status i');
                        if (statusIcon) {
                            statusIcon.className = 'fas fa-check-circle';
                            statusIcon.style.color = '#00AA66';
                        }
                        const btn = item.querySelector('.btn-mark-viewed');
                        if (btn) {
                            btn.textContent = 'Completed';
                            btn.disabled = true;
                        }
                    });

                    updateLessonProgress();

                    const btnStep1 = document.getElementById('btn-step-1');
                    btnStep1.classList.remove('active');
                    btnStep1.classList.add('completed', 'disabled');
                    btnStep1.innerHTML = `<i class="fas fa-check-circle"></i> Lesson Materials`;

                    const btnStep2 = document.getElementById('btn-step-2');
                    if (btnStep2) {
                        btnStep2.classList.remove('disabled');
                        const lockBadge = btnStep2.querySelector('.lock-badge');
                        if (lockBadge) lockBadge.remove();
                        if (!btnStep2.querySelector('.fa-check-circle')) {
                            const icon = document.createElement('i');
                            icon.className = 'fas fa-check-circle';
                            icon.style.color = '#00AA66';
                            icon.style.marginLeft = '5px';
                            btnStep2.appendChild(icon);
                        }
                    }

                    // Show success toast
                    showSuccessMessage('✅ Lesson Completed! Proceeding to Pre-Assessment...');

                    // Navigate to step 2 after 2 seconds (to show the toast first)
                    setTimeout(() => goToStep(2), 2000);

                } else {
                    alert('Error completing lesson.');
                }
            })
            .catch(err => console.error(err));
    }

    // Toast helper
    function showSuccessMessage(message) {
        const alert = document.createElement('div');
        alert.className = 'proctoring-alert';
        alert.style.background = '#00AA66';
        alert.style.color = 'white';
        alert.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }
    // ---------- Close Camera Modal ----------
    function closeCameraModal() {
        document.getElementById('cameraModal').classList.remove('active');
    }

    function requestCameraAccess() {
        closeCameraModal();
        startFloatingCamera();
    }

    // ---------- Make Camera Draggable ----------
    const floatingCamera = document.getElementById('floatingCamera');
    let isDragging = false;
    let initialX, initialY;

    floatingCamera.addEventListener('mousedown', (e) => {
        if (e.target.closest('.floating-camera-header')) {
            isDragging = true;
            initialX = e.clientX - floatingCamera.offsetLeft;
            initialY = e.clientY - floatingCamera.offsetTop;
        }
    });

    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            floatingCamera.style.right = 'auto';
            floatingCamera.style.bottom = 'auto';
            floatingCamera.style.left = (e.clientX - initialX) + 'px';
            floatingCamera.style.top = (e.clientY - initialY) + 'px';
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });

    // ---------- Initialise ----------
    document.addEventListener('DOMContentLoaded', () => {
    const firstAvailableStep = getFirstAvailableStep();
    if (firstAvailableStep) {
        goToStep(firstAvailableStep);
    } else {
        showCongratsModal();
    }
});

    /**
     * Returns the number of the first step that is not disabled and not completed.
     * Steps are 1..5. Returns null if all are completed.
     */
    function getFirstAvailableStep() {
        for (let step = 1; step <= 5; step++) {
            const btn = document.getElementById(`btn-step-${step}`);
            if (!btn) continue;
            // If it's not disabled and not marked as completed, it's available
            if (!btn.classList.contains('disabled') && !btn.classList.contains('completed')) {
                return step;
            }
        }
        return null; // all steps are either disabled or completed
    }

    function showCompletionMessage() {
        // Display a nice message instead of an empty page
        const container = document.querySelector('.step-content');
        if (container) {
            container.innerHTML = `
            <div class="lesson-card" style="text-align: center; padding: 40px;">
                <div style="font-size: 48px; color: #00AA66; margin-bottom: 20px;">
                    <i class="fas fa-trophy"></i>
                </div>
                <h2 style="color: #333; margin-bottom: 10px;">🎉 Congratulations!</h2>
                <p style="color: #666; font-size: 18px;">
                    You have successfully completed all assessments and materials for this subject.
                </p>
                <p style="color: #999; margin-top: 20px;">
                    Your teacher will review your progress. Keep up the great work!
                </p>
            </div>
        `;
            // Hide all filter buttons or disable them
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
            });
            // Hide the "Due Date" section if you want
            const dueDateSection = document.getElementById('dueDateSection');
            if (dueDateSection) dueDateSection.style.display = 'none';
        }
    }
</script>
@endpush