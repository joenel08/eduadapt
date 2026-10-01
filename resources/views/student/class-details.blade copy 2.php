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

{{-- Compute completion flags once --}}
@php
    $allDone = $access['materials_done'] && $access['pre_done'] && $access['post_done'] && $access['intervention_materials_done'] && $access['quiz_done'];
@endphp

<div class="due-date-section" id="dueDateSection">
    <div class="due-date-text">
        <i class="fas fa-calendar-check"></i> Due Date:
    </div>
    <div class="due-date-value" id="dueDateValue">—</div>
</div>

<!-- Step Navigation Buttons -->
<div class="filter-buttons" id="filterButtons">
    <button class="filter-btn {{ $access['materials_done'] ? 'completed' : 'active' }}" onclick="goToStep(1)" id="btn-step-1">
        <i class="fas fa-book"></i> Lesson Materials
        @if($access['materials_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ $access['pre_done'] ? 'completed' : ($access['pre_assessment'] ? '' : 'disabled') }}" onclick="goToStep(2)" id="btn-step-2">
        <i class="fas fa-question-circle"></i> Pre-Assessment
        @if(!$access['pre_assessment'] && !$access['pre_done'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['pre_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ $access['post_done'] ? 'completed' : ($access['post_assessment'] ? '' : 'disabled') }}" onclick="goToStep(3)" id="btn-step-3">
        <i class="fas fa-star"></i> Post-Assessment
        @if(!$access['post_assessment'] && !$access['post_done'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['post_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ ($access['intervention_materials_done'] && $access['quiz_done']) ? 'completed' : ($access['intervention'] ? '' : 'disabled') }}" onclick="goToStep(4)" id="btn-step-4">
        <i class="fas fa-lightbulb"></i> Learning Intervention
        @if(!$access['intervention'] && !$access['intervention_materials_done'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
        @if($access['intervention_materials_done'] && $access['quiz_done'])<i class="fas fa-check-circle" style="color:#00AA66; margin-left:5px;"></i>@endif
    </button>
    <button class="filter-btn {{ $access['quiz_done'] ? 'completed' : ($access['intervention_quiz'] ? '' : 'disabled') }}" onclick="goToStep(5)" id="btn-step-5">
        <i class="fas fa-list-check"></i> Mini Quiz
        @if(!$access['intervention_quiz'] && !$access['quiz_done'])<div class="lock-badge"><i class="fas fa-lock"></i></div>@endif
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
                <div class="material-icon"><i class="fas fa-file-alt"></i></div>
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
                    <a href="{{ $material->file_url }}" target="_blank" class="download-btn"><i class="fas fa-download"></i></a>
                    @endif
                </div>
                @php $isDone = in_array($material->progress, ['viewed', 'completed']); @endphp
                @if(!$materialsLocked && !$access['materials_done'])
                    @if(!$isDone)
                    <button class="btn-mark-viewed" onclick="markMaterialViewed(this, {{ json_encode(get_class($material)) }}, {{ $material->id }}, {{ $class->id }})">Mark as Viewed</button>
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
@php
    $preProgress = $preAssessment ? $studentProgress->get(get_class($preAssessment) . '|' . $preAssessment->id) : null;
    $preCompleted = $preProgress && $preProgress->status === 'completed';
    $preAnswers = $preProgress && $preProgress->answers ? $preProgress->answers : [];
@endphp
<div class="step-content" id="step-2" style="display: none;">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-question-circle"></i>
            <span>Pre-Assessment: {{ $selectedSubject->name ?? 'General' }}</span>
        </div>

        @if($preCompleted)
        <div class="score-section" style="margin-bottom:20px;">
            <div class="score-text">Your Score</div>
            <div class="score-display">{{ $preProgress->score }} / {{ count($preAssessment->questions) }}</div>
            <div class="score-label">{{ round(($preProgress->score / count($preAssessment->questions)) * 100) }}%</div>
        </div>

        @foreach($preAssessment->questions as $index => $q)
            @php
                $userAnswer = $preAnswers[$index] ?? null;
                $correctAnswer = $q['correctAnswer'] ?? null;
                $isCorrect = false;
                if ($q['type'] === 'multipleChoice' || $q['type'] === 'trueFalse') {
                    $isCorrect = $userAnswer === $correctAnswer;
                } elseif ($q['type'] === 'matchingType') {
                    $isCorrect = true;
                    foreach ($q['pairs'] as $pi => $pair) {
                        if (($userAnswer[$pi] ?? null) !== $pair['answer']) { $isCorrect = false; break; }
                    }
                }
            @endphp
            <div class="acc-question {{ $isCorrect ? 'correct' : 'wrong' }}">
                <div class="acc-q-title">
                    <span>Q{{ $index + 1 }}. {{ $q['question'] }}</span>
                    <span class="acc-badge {{ $isCorrect ? 'correct' : 'wrong' }}">
                        <i class="fas {{ $isCorrect ? 'fa-check' : 'fa-times' }}"></i>
                        {{ $isCorrect ? 'Correct' : 'Wrong' }}
                    </span>
                </div>
                <div class="acc-answer">
                    <strong>Your Answer:</strong>
                    @if($q['type'] === 'matchingType' && is_array($userAnswer))
                        <ul style="margin:5px 0 0 20px;">
                            @foreach($q['pairs'] as $pi => $pair)
                                <li>{{ $pair['question'] }} → <em>{{ $userAnswer[$pi] ?? '—' }}</em></li>
                            @endforeach
                        </ul>
                    @else
                        <em>{{ $userAnswer ?? 'No answer' }}</em>
                    @endif
                </div>
                @if(!$isCorrect)
                <div class="acc-answer correct-answer">
                    <strong>Correct Answer:</strong>
                    @if($q['type'] === 'matchingType')
                        <ul style="margin:5px 0 0 20px;">
                            @foreach($q['pairs'] as $pair)
                                <li>{{ $pair['question'] }} → <em>{{ $pair['answer'] }}</em></li>
                            @endforeach
                        </ul>
                    @else
                        <em>{{ $correctAnswer }}</em>
                    @endif
                </div>
                @endif
            </div>
        @endforeach
        @else
            <div class="section-subtitle">
                @if($preAssessment) Time remaining: <span id="pre-timer">--:--</span>
                @else No pre-assessment assigned.
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
        @endif
    </div>
</div>

<!-- STEP 3: POST-ASSESSMENT -->
@php
    $postProgress = $postAssessment ? $studentProgress->get(get_class($postAssessment) . '|' . $postAssessment->id) : null;
    $postCompleted = $postProgress && $postProgress->status === 'completed';
    $postAnswers = $postProgress && $postProgress->answers ? $postProgress->answers : [];
@endphp
<div class="step-content" id="step-3" style="display: none;">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-star"></i>
            <span>Post-Assessment: {{ $selectedSubject->name ?? 'General' }}</span>
        </div>

        @if($postCompleted)
        <div class="score-section" style="margin-bottom:20px;">
            <div class="score-text">Your Score</div>
            <div class="score-display">{{ $postProgress->score }} / {{ count($postAssessment->questions) }}</div>
            <div class="score-label">{{ round(($postProgress->score / count($postAssessment->questions)) * 100) }}%</div>
        </div>

        @foreach($postAssessment->questions as $index => $q)
            @php
                $userAnswer = $postAnswers[$index] ?? null;
                $correctAnswer = $q['correctAnswer'] ?? null;
                $isCorrect = false;
                if ($q['type'] === 'multipleChoice' || $q['type'] === 'trueFalse') {
                    $isCorrect = $userAnswer === $correctAnswer;
                } elseif ($q['type'] === 'matchingType') {
                    $isCorrect = true;
                    foreach ($q['pairs'] as $pi => $pair) {
                        if (($userAnswer[$pi] ?? null) !== $pair['answer']) { $isCorrect = false; break; }
                    }
                }
            @endphp
            <div class="acc-question {{ $isCorrect ? 'correct' : 'wrong' }}">
                <div class="acc-q-title">
                    <span>Q{{ $index + 1 }}. {{ $q['question'] }}</span>
                    <span class="acc-badge {{ $isCorrect ? 'correct' : 'wrong' }}">
                        <i class="fas {{ $isCorrect ? 'fa-check' : 'fa-times' }}"></i>
                        {{ $isCorrect ? 'Correct' : 'Wrong' }}
                    </span>
                </div>
                <div class="acc-answer">
                    <strong>Your Answer:</strong>
                    @if($q['type'] === 'matchingType' && is_array($userAnswer))
                        <ul style="margin:5px 0 0 20px;">
                            @foreach($q['pairs'] as $pi => $pair)
                                <li>{{ $pair['question'] }} → <em>{{ $userAnswer[$pi] ?? '—' }}</em></li>
                            @endforeach
                        </ul>
                    @else
                        <em>{{ $userAnswer ?? 'No answer' }}</em>
                    @endif
                </div>
                @if(!$isCorrect)
                <div class="acc-answer correct-answer">
                    <strong>Correct Answer:</strong>
                    @if($q['type'] === 'matchingType')
                        <ul style="margin:5px 0 0 20px;">
                            @foreach($q['pairs'] as $pair)
                                <li>{{ $pair['question'] }} → <em>{{ $pair['answer'] }}</em></li>
                            @endforeach
                        </ul>
                    @else
                        <em>{{ $correctAnswer }}</em>
                    @endif
                </div>
                @endif
            </div>
        @endforeach
        @else
            <div class="section-subtitle">
                @if($postAssessment) Time remaining: <span id="post-timer">--:--</span>
                @else No post-assessment assigned.
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
                @php $isDone = in_array($material->progress, ['viewed', 'completed']); @endphp
                @if(!$materialsLocked && !$access['intervention_materials_done'])
                    @if(!$isDone)
                    <button class="btn-mark-viewed" onclick="markMaterialViewed(this, '{{ get_class($material) }}', {{ $material->id }}, {{ $class->id }}, 'completed')">Mark as Completed</button>
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
                @php $isDone = in_array($video->progress, ['viewed', 'completed']); @endphp
                @if(!$materialsLocked && !$access['intervention_materials_done'])
                    @if(!$isDone)
                    <button class="btn-mark-viewed" onclick="markMaterialViewed(this, '{{ get_class($video) }}', {{ $video->id }}, {{ $class->id }}, 'completed')">Mark as Completed</button>
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
        </div>
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
@php
    $quizProgress = $interventionQuiz ? $studentProgress->get(get_class($interventionQuiz) . '|' . $interventionQuiz->id) : null;
    $quizCompleted = $quizProgress && $quizProgress->status === 'completed';
    $quizAnswers = $quizProgress && $quizProgress->answers ? $quizProgress->answers : [];
@endphp
<div class="step-content" id="step-5" style="display: none;">
    <div class="lesson-card">
        <div class="section-title">
            <i class="fas fa-list-check"></i>
            <span>Mini Quiz: {{ $selectedSubject->name ?? 'General' }}</span>
        </div>

        @if($quizCompleted)
        <div class="score-section" style="margin-bottom:20px;">
            <div class="score-text">Your Score</div>
            <div class="score-display">{{ $quizProgress->score }} / {{ count($interventionQuiz->questions) }}</div>
            <div class="score-label">{{ round(($quizProgress->score / count($interventionQuiz->questions)) * 100) }}%</div>
        </div>

        @foreach($interventionQuiz->questions as $index => $q)
            @php
                $userAnswer = $quizAnswers[$index] ?? null;
                $correctAnswer = $q['correctAnswer'] ?? null;
                $isCorrect = false;
                if ($q['type'] === 'multipleChoice' || $q['type'] === 'trueFalse') {
                    $isCorrect = $userAnswer === $correctAnswer;
                } elseif ($q['type'] === 'matchingType') {
                    $isCorrect = true;
                    foreach ($q['pairs'] as $pi => $pair) {
                        if (($userAnswer[$pi] ?? null) !== $pair['answer']) { $isCorrect = false; break; }
                    }
                }
            @endphp
            <div class="acc-question {{ $isCorrect ? 'correct' : 'wrong' }}">
                <div class="acc-q-title">
                    <span>Q{{ $index + 1 }}. {{ $q['question'] }}</span>
                    <span class="acc-badge {{ $isCorrect ? 'correct' : 'wrong' }}">
                        <i class="fas {{ $isCorrect ? 'fa-check' : 'fa-times' }}"></i>
                        {{ $isCorrect ? 'Correct' : 'Wrong' }}
                    </span>
                </div>
                <div class="acc-answer">
                    <strong>Your Answer:</strong>
                    @if($q['type'] === 'matchingType' && is_array($userAnswer))
                        <ul style="margin:5px 0 0 20px;">
                            @foreach($q['pairs'] as $pi => $pair)
                                <li>{{ $pair['question'] }} → <em>{{ $userAnswer[$pi] ?? '—' }}</em></li>
                            @endforeach
                        </ul>
                    @else
                        <em>{{ $userAnswer ?? 'No answer' }}</em>
                    @endif
                </div>
                @if(!$isCorrect)
                <div class="acc-answer correct-answer">
                    <strong>Correct Answer:</strong>
                    <em>{{ $correctAnswer }}</em>
                </div>
                @endif
            </div>
        @endforeach
        @else
            <div class="section-subtitle">
                @if($interventionQuiz) Time remaining: <span id="quiz-timer">--:--</span>
                @else No mini quiz assigned.
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
        <button class="floating-camera-close" onclick="closeFloatingCamera()"><i class="fas fa-times"></i></button>
    </div>
    <video id="floatingCameraVideo" autoplay playsinline muted></video>
</div>

<!-- EXAM START WARNING MODAL -->
<div class="exam-start-modal" id="examStartModal">
    <div class="exam-start-content">
        <div class="exam-start-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="exam-start-title">Ready to Start Assessment?</div>
        <div class="exam-start-subtitle" id="examStartSubtitle">Pre-Assessment</div>
        <div class="warning-label"><i class="fas fa-exclamation-triangle"></i> Important Notice</div>
        <div class="warning-list">
    <div class="warning-item"><i class="fas fa-times-circle"></i><span><strong>Do NOT open new tabs</strong> – 3 Strikes Rule: 1st &amp; 2nd = Warning, 3rd = Auto-Submit</span></div>
    <div class="warning-item"><i class="fas fa-times-circle"></i><span><strong>Do NOT minimize the window</strong> – 3 Strikes Rule: 1st &amp; 2nd = Warning, 3rd = Auto-Submit</span></div>
    <div class="warning-item"><i class="fas fa-times-circle"></i><span><strong>Do NOT switch windows</strong> – 3 Strikes Rule: 1st &amp; 2nd = Warning, 3rd = Auto-Submit</span></div>
    <div class="warning-item"><i class="fas fa-times-circle"></i><span><strong>Camera must be active</strong> – Required for proctoring.</span></div>
</div>
        <div class="requirements-list">
            <div class="warning-label" style="color: #0066CC; margin-bottom: 15px;"><i class="fas fa-check-circle"></i> Requirements</div>
            <div class="requirement-item"><i class="fas fa-video"></i><span>Stable internet connection and working camera</span></div>
            <div class="requirement-item"><i class="fas fa-desktop"></i><span>Keep this window in focus throughout the exam</span></div>
            <div class="requirement-item"><i class="fas fa-clock"></i><span id="examTimeRequirement">You have <strong><span id="examTimeDisplay">--</span> minutes</strong> to complete this assessment</span></div>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="understandWarning">
           <label for="understandWarning">I understand the 3-strike rule. Violations will trigger warnings on the 1st and 2nd offense. The 3rd offense will automatically submit my exam.</label>
        </div>
        <div class="exam-start-buttons">
            <button class="exam-start-btn cancel" onclick="closeExamStartModal()"><i class="fas fa-times"></i> Cancel</button>
            <button class="exam-start-btn start" onclick="startExamAssessment()" id="startExamBtn" disabled><i class="fas fa-play"></i> Start Assessment</button>
        </div>
    </div>
</div>

<!-- WARNING POPUP -->
<div class="warning-popup-overlay" id="warningPopupOverlay"></div>
<div class="warning-popup" id="warningPopup">
    <div class="warning-popup-icon" id="warningPopupIcon"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="warning-popup-title" id="warningPopupTitle">WARNING</div>
    <div class="warning-popup-text" id="warningPopupText">You have switched tabs or minimized the window.</div>
<div class="warning-popup-remaining" id="warningPopupRemaining">Remaining Violations: 2</div>
<button class="warning-popup-btn" onclick="closeWarningPopup()">Continue Exam</button>
</div>

<!-- SCORE RESULT MODAL -->
<div class="score-result-overlay" id="scoreResultOverlay">
    <div class="score-result-modal">
        <div class="score-result-icon" id="scoreResultIcon"><i class="fas fa-check-circle"></i></div>
        <div class="score-result-title" id="scoreResultTitle">Assessment Complete!</div>
        <div class="score-result-score" id="scoreResultScore">85%</div>
        <div class="score-result-message" id="scoreResultMessage">Great job! You've completed your assessment.</div>
        <button class="score-result-btn" onclick="closeScoreResultModal()">Continue</button>
    </div>
</div>

<!-- CAMERA REQUEST MODAL -->
<div class="modal-overlay" id="cameraModal">
    <div class="modal-content">
        <div class="modal-icon"><i class="fas fa-camera"></i></div>
        <div class="modal-title">Camera Required</div>
        <div class="modal-text">Your camera is required for proctoring. Please allow access to your camera to proceed.</div>
        <div class="modal-buttons">
            <button class="modal-btn secondary" onclick="closeCameraModal()"><i class="fas fa-times"></i> Cancel</button>
            <button class="modal-btn primary" onclick="requestCameraAccess()"><i class="fas fa-check"></i> Allow Camera</button>
        </div>
    </div>
</div>

@endif

<!-- CONGRATULATIONS MODAL -->
<div class="congrats-overlay" id="congratsOverlay">
    <div class="congrats-modal">
        <button class="congrats-close" onclick="closeCongratsModal()" title="Close">
            <i class="fas fa-times"></i>
        </button>
        <div class="congrats-icon"><i class="fas fa-trophy"></i></div>
        <h2 class="congrats-title">🎉 Congratulations!</h2>
        <p class="congrats-subtitle">
            You have completed <span class="congrats-highlight">all tasks</span> for this subject.
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
        <div style="display:flex; gap:12px; justify-content:center; margin-top:20px;">
            <button class="congrats-btn" onclick="closeCongratsModal()">
                <i class="fas fa-eye"></i> Review My Answers
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #333; }

.breadcrumb { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; font-size: 13px; color: #666; }
.breadcrumb a { color: #0066CC; text-decoration: none; cursor: pointer; }
.page-title { font-size: 24px; font-weight: 700; color: #333; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
.page-title i { color: #0066CC; font-size: 28px; }
.page-subtitle { font-size: 14px; color: #999; margin-bottom: 30px; }
.due-date-section { background: linear-gradient(135deg, rgba(0, 102, 204, 0.05), rgba(0, 102, 204, 0.02)); border-left: 4px solid #0066CC; padding: 12px 16px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; }
.due-date-value { font-weight: 600; color: #0066CC; }
.filter-buttons { display: flex; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; }
.filter-btn { padding: 10px 18px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: #666; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; position: relative; }
.filter-btn:hover:not(.disabled) { border-color: #0066CC; color: #0066CC; }
.filter-btn.active { background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); color: white; border-color: transparent; }
.filter-btn.completed { background: #e0ffe0; color: #00AA66; border-color: #00AA66; }
.filter-btn.disabled { opacity: 0.5; cursor: not-allowed; color: #999; }
.lock-badge { display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; background: #FF6B6B; color: white; border-radius: 50%; font-size: 10px; margin-left: auto; }
.lesson-card { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 30px; margin-bottom: 20px; }
.lesson-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; background: #f9f9f9; border-radius: 8px; margin-bottom: 20px; }
.lesson-placeholder i { font-size: 48px; color: #0066CC; margin-bottom: 15px; }
.lesson-placeholder p { font-size: 14px; color: #666; }
.question-item { margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #e0e0e0; }
.question-item:last-child { border-bottom: none; }
.question-text { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 15px; }
.option { display: flex; align-items: center; padding: 12px; margin-bottom: 10px; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
.option:hover { background: #f9f9f9; border-color: #0066CC; }
.option input[type="radio"] { margin-right: 12px; cursor: pointer; }
.option label { cursor: pointer; flex: 1; }
.btn-continue { width: 100%; padding: 12px 20px; background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 20px; }
.btn-continue:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,102,204,0.3); }
.btn-continue:disabled { opacity: 0.5; cursor: not-allowed; }
.section-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
.section-subtitle { font-size: 13px; color: #999; margin-bottom: 20px; }
.score-section { background: linear-gradient(135deg, rgba(0,170,102,0.05), rgba(0,170,102,0.02)); border-left: 4px solid #00AA66; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
.score-text { font-size: 13px; color: #666; margin-bottom: 5px; }
.score-display { font-size: 24px; font-weight: 700; color: #00AA66; }
.score-label { font-size: 12px; color: #999; margin-top: 5px; }
.material-item { display: flex; align-items: center; gap: 12px; padding: 15px; background: #f9f9f9; border-radius: 8px; margin-bottom: 10px; transition: all 0.3s ease; flex-wrap: wrap; }
.material-item:hover { background: #f0f7ff; box-shadow: 0 2px 8px rgba(0,102,204,0.1); }
.material-icon { font-size: 24px; color: #0066CC; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(0,102,204,0.1); border-radius: 8px; flex-shrink: 0; }
.material-info { flex: 1; }
.material-name { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 2px; }
.material-type { font-size: 12px; color: #999; }
.material-status { margin-right: 10px; }
.material-download { color: #0066CC; font-size: 18px; cursor: pointer; }
.btn-mark-viewed { background: #0066CC; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-left: 10px; }
.btn-mark-viewed:hover { background: #004d99; }
.btn-mark-viewed:disabled { opacity: 0.5; cursor: default; }
.text-muted { color: #999; font-size: 13px; margin-left: 10px; }
.floating-camera { position: fixed; bottom: 30px; right: 30px; width: 300px; height: 250px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 5000; border: 3px solid #0066CC; overflow: hidden; background: #000; cursor: move; display: none; }
.floating-camera.active { display: block; }
.floating-camera video { width: 100%; height: 100%; object-fit: cover; display: block; }
.floating-camera-header { position: absolute; top: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; }
.floating-camera-title { color: white; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.floating-camera-status { width: 8px; height: 8px; background: #00FF00; border-radius: 50%; animation: pulse 1s infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
.floating-camera-close { background: none; border: none; color: white; cursor: pointer; font-size: 16px; padding: 4px 8px; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; z-index: 10001; justify-content: center; align-items: center; }
.modal-overlay.active { display: flex; }
.modal-content { background: white; border-radius: 12px; padding: 30px; max-width: 450px; text-align: center; box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
.modal-icon { font-size: 64px; color: #0066CC; margin-bottom: 20px; }
.modal-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 10px; }
.modal-text { font-size: 14px; color: #666; margin-bottom: 25px; line-height: 1.6; }
.modal-buttons { display: flex; gap: 12px; justify-content: center; }
.modal-btn { padding: 12px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.modal-btn.primary { background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); color: white; }
.modal-btn.secondary { background: #f0f0f0; color: #333; border: 1px solid #e0e0e0; }
.exam-start-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; z-index: 10002; justify-content: center; align-items: center; padding: 20px; }
.exam-start-modal.active { display: flex; }
.exam-start-content { background: white; border-radius: 12px; padding: 40px; max-width: 550px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; }
.exam-start-icon { font-size: 64px; color: #FF6B6B; margin-bottom: 20px; text-align: center; }
.exam-start-title { font-size: 24px; font-weight: 700; color: #333; margin-bottom: 15px; text-align: center; }
.exam-start-subtitle { font-size: 16px; font-weight: 600; color: #666; margin-bottom: 20px; text-align: center; }
.warning-list { background: linear-gradient(135deg, rgba(255,107,107,0.05), rgba(255,107,107,0.02)); border: 2px solid #FF6B6B; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
.warning-item { display: flex; gap: 12px; margin-bottom: 12px; font-size: 14px; color: #333; line-height: 1.5; }
.warning-item:last-child { margin-bottom: 0; }
.warning-item i { color: #FF6B6B; font-weight: bold; min-width: 20px; flex-shrink: 0; margin-top: 2px; }
.warning-label { font-weight: 600; color: #FF6B6B; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
.requirements-list { background: #f9f9f9; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
.requirement-item { display: flex; gap: 12px; margin-bottom: 12px; font-size: 14px; color: #666; }
.requirement-item:last-child { margin-bottom: 0; }
.requirement-item i { color: #0066CC; min-width: 20px; flex-shrink: 0; }
.exam-start-buttons { display: flex; gap: 12px; justify-content: center; }
.exam-start-btn { padding: 14px 32px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; flex: 1; }
.exam-start-btn.cancel { background: #f0f0f0; color: #333; border: 1px solid #e0e0e0; }
.exam-start-btn.start { background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); color: white; }
.exam-start-btn.start:disabled { opacity: 0.6; cursor: not-allowed; }
.checkbox-group { display: flex; align-items: center; gap: 12px; margin: 20px 0; padding: 12px; background: #f9f9f9; border-radius: 8px; }
.checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
.checkbox-group label { cursor: pointer; font-size: 14px; color: #333; margin: 0; }
.warning-popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10002; display: none; }
.warning-popup { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); background: white; border-radius: 12px; padding: 40px; max-width: 450px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.3); z-index: 10003; display: none; }
.warning-popup-icon { font-size: 64px; margin-bottom: 20px; }
.warning-popup-title { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 10px; }
.warning-popup-text { font-size: 14px; color: #666; margin-bottom: 20px; line-height: 1.6; }
.warning-popup-remaining { font-size: 13px; color: #FF6B6B; font-weight: 600; margin-bottom: 25px; padding: 12px; background: rgba(255,107,107,0.1); border-radius: 8px; border-left: 4px solid #FF6B6B; }
.warning-popup-btn { padding: 12px 32px; background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.score-result-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10004; display: none; justify-content: center; align-items: center; }
.score-result-overlay.active { display: flex; }
.score-result-modal { background: white; border-radius: 12px; padding: 40px; max-width: 500px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
.score-result-icon { font-size: 80px; margin-bottom: 20px; display: flex; justify-content: center; align-items: center; height: 100px; color: #0066CC; }
.score-result-title { font-size: 24px; font-weight: 700; color: #333; margin-bottom: 15px; }
.score-result-score { font-size: 48px; font-weight: 700; margin: 20px 0; color: #0066CC; }
.score-result-message { font-size: 14px; color: #666; margin-bottom: 30px; line-height: 1.6; }
.score-result-btn { padding: 14px 40px; background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.proctoring-alert { position: fixed; top: 20px; right: 20px; background: #FF6B6B; color: white; padding: 16px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(255,107,107,0.3); z-index: 10000; animation: slideIn 0.3s ease; max-width: 350px; }
@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.proctoring-alert i { margin-right: 10px; }

/* Congrats Modal */
.congrats-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10005; display: none; justify-content: center; align-items: center; padding: 20px; }
.congrats-overlay.active { display: flex; }
.congrats-modal { background: white; border-radius: 16px; padding: 40px; max-width: 520px; width: 100%; text-align: center; box-shadow: 0 12px 40px rgba(0,0,0,0.3); position: relative; animation: popIn 0.3s ease; }
@keyframes popIn { from { opacity: 0; transform: scale(0.85); } to { opacity: 1; transform: scale(1); } }
.congrats-close { position: absolute; top: 15px; right: 15px; background: #f0f0f0; border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; font-size: 16px; transition: all 0.2s ease; }
.congrats-close:hover { background: #FF6B6B; color: white; transform: rotate(90deg); }
.congrats-icon { font-size: 72px; color: #FFB800; margin-bottom: 15px; }
.congrats-title { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 12px; }
.congrats-subtitle { font-size: 15px; color: #666; margin-bottom: 25px; line-height: 1.6; }
.congrats-highlight { color: #00AA66; font-weight: 700; }
.congrats-stats { display: flex; justify-content: center; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
.congrats-stat { background: #f9f9f9; border-radius: 10px; padding: 15px 20px; min-width: 100px; }
.congrats-stat-icon { font-size: 20px; color: #0066CC; margin-bottom: 6px; }
.congrats-stat-value { font-size: 18px; font-weight: 700; color: #333; }
.congrats-stat-label { font-size: 11px; color: #999; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
.congrats-btn { padding: 12px 32px; background: linear-gradient(135deg, #00AA66, #008C52); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
.congrats-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,170,102,0.3); }

/* Answer review cards */
.acc-question { padding: 12px 15px; background: #f9f9f9; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #ccc; }
.acc-question.correct { border-left-color: #00AA66; background: #f0fff4; }
.acc-question.wrong { border-left-color: #FF6B6B; background: #fff5f5; }
.acc-q-title { font-weight: 600; color: #333; margin-bottom: 8px; display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.acc-badge { font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.acc-badge.correct { background: #d4edda; color: #155724; }
.acc-badge.wrong { background: #f8d7da; color: #721c24; }
.acc-answer { font-size: 13px; color: #555; margin: 4px 0; }
.acc-answer strong { color: #333; }
.acc-answer.correct-answer { padding: 6px 10px; background: #e8f5e9; border-radius: 6px; margin-top: 6px; }
.acc-answer.correct-answer strong { color: #2e7d32; }
</style>
@endpush

@push('scripts')
<script>
window.CLASSES_URL = "{{ route('student.classes') }}";
</script>
<script>
    const classId = {{ $class->id }};
    const preAssessmentClass = @json($preAssessment ? get_class($preAssessment) : null);
    const preAssessmentId = @json($preAssessment?->id);
    const postAssessmentClass = @json($postAssessment ? get_class($postAssessment) : null);
    const postAssessmentId = @json($postAssessment?->id);
    const quizAssessmentClass = @json($interventionQuiz ? get_class($interventionQuiz) : null);
    const quizAssessmentId = @json($interventionQuiz?->id);
    const preTimeLimit = {{ $preTimeLimit }};
    const postTimeLimit = {{ $postTimeLimit }};
    const quizTimeLimit = {{ $quizTimeLimit }};

   let currentStep = 1;
let examActive = false;
let materialsActive = false;
let examStep = null;
let materialsWarningCount = 0;
let examWarningCount = 0;   // <-- ADD THIS
const MAX_WARNINGS = 3;
    let timerInterval = null;
    let mediaRecorder = null;
    let recordedChunks = [];
    let cameraStreams = {};
    let pendingExamStep = null;

    // ---------- Navigation ----------
    function goToStep(step) {
        const btn = document.getElementById(`btn-step-${step}`);

        if (btn && btn.classList.contains('disabled')) {
            alert('This step is not yet available.');
            return;
        }

        if ([2, 3, 5].includes(step)) {
            // If completed, just view (no modal, no camera, no timer)
            if (btn && btn.classList.contains('completed')) {
                proceedToStep(step, true); // true = view-only mode
                return;
            }
            pendingExamStep = step;
            showExamStartModal(step);
            return;
        }

        proceedToStep(step);
    }

    function proceedToStep(step, viewOnly = false) {
        document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
        const target = document.getElementById(`step-${step}`);
        if (target) target.style.display = 'block';

        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        const btn = document.getElementById(`btn-step-${step}`);
        if (btn) btn.classList.add('active');

        currentStep = step;

        // If it's a completed assessment (view-only), just show it — no exam logic
        const isCompleted = btn && btn.classList.contains('completed');

       if ([2, 3, 5].includes(step) && !isCompleted) {
    materialsActive = false;
    examActive = true;
    examStep = step === 2 ? 'pre' : (step === 3 ? 'post' : 'quiz');
    examWarningCount = 0;   // <-- ADD THIS
    startFloatingCamera();
            const timeLimit = step === 2 ? preTimeLimit : (step === 3 ? postTimeLimit : quizTimeLimit);
            if (timeLimit) startTimer(step, timeLimit);
        } else if (step === 1 || step === 4) {
            // Materials — only warn if NOT all done yet
            materialsActive = !$isAllDone;
            examActive = false;
            examStep = null;
            materialsWarningCount = 0;
            stopFloatingCamera();
            clearInterval(timerInterval);
        } else {
            // View-only mode (completed assessment)
            materialsActive = false;
            examActive = false;
            examStep = null;
            stopFloatingCamera();
            clearInterval(timerInterval);
        }
    }

    // Global flag passed from Blade
    const $isAllDone = {{ $allDone ? 'true' : 'false' }};

    function showExamStartModal(step) {
        const names = { 2: 'Pre-Assessment', 3: 'Post-Assessment', 5: 'Mini Quiz' };
        const times = { 2: preTimeLimit || 10, 3: postTimeLimit || 10, 5: quizTimeLimit || 5 };
        document.getElementById('examTimeRequirement').innerHTML = `You have <strong>${times[step]} minutes</strong> to complete this assessment`;
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
        setTimeout(() => proceedToStep(pendingExamStep), 300);
    }

    document.addEventListener('change', function(e) {
        if (e.target.id === 'understandWarning') {
            document.getElementById('startExamBtn').disabled = !e.target.checked;
        }
    });

    function startTimer(step, minutes) {
        let seconds = minutes * 60;
        const timerId = step === 2 ? 'pre-timer' : (step === 3 ? 'post-timer' : 'quiz-timer');
        const display = document.getElementById(timerId);
        if (!display) return;
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
                submitAssessment(examStep, true);
            }
        }, 1000);
    }

    async function startFloatingCamera() {
        try {
            const video = document.getElementById('floatingCameraVideo');
            const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 }, audio: false });
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
        if (video) video.srcObject = null;
        document.getElementById('floatingCamera').classList.remove('active');
        stopRecording();
    }

    function closeFloatingCamera() { stopFloatingCamera(); }

    function startRecording(stream) {
        try {
            mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
            recordedChunks = [];
            mediaRecorder.ondataavailable = event => { if (event.data.size > 0) recordedChunks.push(event.data); };
            mediaRecorder.start();
        } catch (err) { console.warn('Recording error:', err); }
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
    }

    function getVideoBlob() {
        if (recordedChunks.length === 0) return null;
        return new Blob(recordedChunks, { type: 'video/webm' });
    }

    function showWarningPopup(arg1, arg2) {
    const icon      = document.getElementById('warningPopupIcon');
    const title     = document.getElementById('warningPopupTitle');
    const text      = document.getElementById('warningPopupText');
    const remaining = document.getElementById('warningPopupRemaining');

    if (typeof arg1 === 'number') {
        // --- EXAM 3-STRIKE MODE ---
        const offenseNumber = arg1;
        if (offenseNumber === 1) {
            icon.className      = 'warning-popup-icon';
            icon.style.color    = '#FF9800';
            title.textContent   = '⚠️ WARNING - First Offense';
            text.textContent    = 'You have switched tabs or minimized the window. Please keep the exam window focused.';
            remaining.textContent = 'Remaining Violations: 2 (Next violation = Final Warning)';
        } else if (offenseNumber === 2) {
            icon.className      = 'warning-popup-icon';
            icon.style.color    = '#FF6B6B';
            title.textContent   = '🚨 FINAL WARNING - Second Offense';
            text.textContent    = 'This is your FINAL WARNING! You have switched tabs/minimized again. One more violation will automatically submit your exam.';
            remaining.textContent = 'Remaining Violations: 1 (Next violation = Auto-Submit)';
        }
    } else {
        // --- MATERIALS MODE (existing behaviour) ---
        icon.className      = 'warning-popup-icon';
        icon.style.color    = '#FF6B6B';
        title.textContent   = arg1 || 'Warning!';
        text.textContent    = arg2 || 'Please return to the page.';
        remaining.textContent = `Violations: ${materialsWarningCount}/${MAX_WARNINGS}`;
    }

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
        if (!confirm('Mark all intervention materials and videos as completed? This will unlock the Mini Quiz.')) return;

        fetch('{{ route("student.intervention.complete") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({ class_id: classId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('#step-4 .material-item').forEach(item => {
                    const statusIcon = item.querySelector('.material-status i');
                    if (statusIcon) { statusIcon.className = 'fas fa-check-circle'; statusIcon.style.color = '#00AA66'; }
                    const btn = item.querySelector('.btn-mark-viewed');
                    if (btn) { btn.textContent = 'Completed'; btn.disabled = true; btn.style.opacity = '0.6'; }
                });

                const container = document.querySelector('#step-4 .btn-continue')?.closest('div');
                if (container) {
                    container.innerHTML = `<p style="color: #00AA66; text-align: center; margin-top: 20px;"><i class="fas fa-check-circle"></i> All intervention materials completed! Proceeding to Mini Quiz...</p>`;
                }

                const btnStep5 = document.getElementById('btn-step-5');
                if (btnStep5) {
                    btnStep5.classList.remove('disabled');
                    const lockBadge = btnStep5.querySelector('.lock-badge');
                    if (lockBadge) lockBadge.remove();
                }

                showSuccessMessage('✅ All intervention materials completed!');
                setTimeout(() => goToStep(5), 1500);
            } else {
                alert('Error: ' + (data.message || 'Could not complete intervention items.'));
            }
        })
        .catch(err => { console.error(err); alert('Network error.'); });
    }

    // ---------- Visibility/Warning ----------
    // Only trigger warnings if the student is actively working (not reviewing completed content)
   document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Skip all warnings if everything is done (review mode)
        if ($isAllDone) return;

        if (materialsActive) {
            materialsWarningCount++;
            if (materialsWarningCount >= MAX_WARNINGS) {
                fetch('{{ route("student.lock.materials") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ class_id: classId })
                })
                .then(res => res.json())
                .then(data => { if (data.success) window.location.reload(); })
                .catch(err => console.error(err));
            } else {
                showWarningPopup('Warning!', `You have switched tabs while viewing materials (${materialsWarningCount}/${MAX_WARNINGS}). After ${MAX_WARNINGS} attempts, materials will be locked.`);
            }
        } else if (examActive) {
            // --- 3-STRIKE RULE ---
            examWarningCount++;

            if (examWarningCount === 1) {
                showWarningPopup(1);
            } else if (examWarningCount === 2) {
                showWarningPopup(2);
            } else if (examWarningCount >= MAX_WARNINGS) {
                closeWarningPopup();
                showProctoringAlert('❌ Maximum violations exceeded! Exam automatically submitted.');
                submitAssessment(examStep, true);
            }
        }
    }
});

    document.addEventListener('contextmenu', function(e) {
        if (examActive && !$isAllDone) { e.preventDefault(); showProctoringAlert('⚠️ Right-click is disabled during exam'); return false; }
    });

    document.addEventListener('keydown', function(e) {
        if (examActive && !$isAllDone) {
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
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({ content_type: contentType, content_id: contentId, class_id: classId, status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const statusIcon = item.querySelector('.material-status i');
                if (statusIcon) { statusIcon.className = 'fas fa-check-circle'; statusIcon.style.color = '#00AA66'; }
                btn.textContent = status === 'completed' ? 'Completed' : 'Viewed';
                btn.disabled = true;
                btn.style.opacity = '0.6';

                if (contentType === 'App\\Models\\ContentItem') updateLessonProgress(classId);

                const isIntervention = contentType === 'App\\Models\\InterventionMaterial' || contentType === 'App\\Models\\InterventionVideo';
                if (isIntervention) {
                    fetch('{{ route("student.intervention.progress") }}?class_id=' + classId)
                        .then(res => res.json())
                        .then(pd => {
                            if (pd.complete && pd.total > 0) {
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

    function updateLessonProgress() {
        fetch('{{ route("student.content.progress") }}?class_id=' + classId)
            .then(res => res.json())
            .then(data => {
                const el = document.getElementById('lessonProgress');
                const bar = document.getElementById('lessonProgressBar');
                if (el) el.textContent = data.percentage + '%';
                if (bar) bar.style.width = data.percentage + '%';
            })
            .catch(err => console.error(err));
    }

    function submitAssessment(step, autoSubmit = false) {
        const containerId = step === 'pre' ? 'preAssessmentContainer' : (step === 'post' ? 'postAssessmentContainer' : 'interventionQuizContainer');
        const container = document.getElementById(containerId);
        if (!container) return;

        const answers = gatherAnswers(container);
        const contentTypeMap = { pre: preAssessmentClass, post: postAssessmentClass, quiz: quizAssessmentClass };
        const contentIdMap = { pre: preAssessmentId, post: postAssessmentId, quiz: quizAssessmentId };
        const contentType = contentTypeMap[step];
        const contentId = contentIdMap[step];

        if (!contentType || !contentId) { alert('No assessment found.'); return; }

        const formData = new FormData();
        formData.append('content_type', contentType);
        formData.append('content_id', contentId);
        formData.append('answers', JSON.stringify(answers));
        formData.append('auto_submit', autoSubmit ? '1' : '0');

        const videoBlob = getVideoBlob();
        if (videoBlob) formData.append('video', videoBlob, 'recording.webm');

        fetch('{{ route("student.assessment.submit") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const btnMap = { pre: 2, post: 3, quiz: 5 };
                const btn = document.getElementById(`btn-step-${btnMap[step]}`);
                if (btn) {
                    btn.classList.remove('active');
                    btn.classList.add('completed');
                    btn.innerHTML = `<i class="fas fa-check-circle"></i> Completed`;
                    const badge = btn.querySelector('.lock-badge');
                    if (badge) badge.remove();
                }

                let nextStepNumber = null;
                if (step === 'pre') nextStepNumber = 3;
                else if (step === 'post') nextStepNumber = 4;
                else if (step === 'quiz') nextStepNumber = 6;

                if (nextStepNumber && nextStepNumber <= 5) {
                    const nextBtn = document.getElementById(`btn-step-${nextStepNumber}`);
                    if (nextBtn) {
                        nextBtn.classList.remove('disabled', 'locked');
                        const lockBadge = nextBtn.querySelector('.lock-badge');
                        if (lockBadge) lockBadge.remove();
                        nextBtn.style.pointerEvents = 'auto';
                        nextBtn.style.opacity = '1';
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
                    if (btn4) { btn4.classList.add('completed'); btn4.classList.remove('disabled'); }
                }

                showScoreResultModal(data.score, data.total, step);
                stopFloatingCamera();
                clearInterval(timerInterval);
                examActive = false;
            } else {
                alert('Error submitting assessment.');
            }
        })
        .catch(err => console.error(err));
    }

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

    function showScoreResultModal(score, total, step) {
        const overlay = document.getElementById('scoreResultOverlay');
        const names = { pre: 'Pre-Assessment', post: 'Post-Assessment', quiz: 'Mini Quiz' };
        document.getElementById('scoreResultTitle').textContent = names[step] + ' Complete!';
        document.getElementById('scoreResultScore').textContent = `${score}/${total}`;
        document.getElementById('scoreResultMessage').textContent = `You scored ${Math.round((score / total) * 100)}%. Great job!`;
        overlay.classList.add('active');
    }

    function closeScoreResultModal() {
        document.getElementById('scoreResultOverlay').classList.remove('active');
        const stepMap = { 2: 3, 3: 4, 5: 6 };
        let next = stepMap[currentStep];
        while (next && next <= 5) {
            const btn = document.getElementById(`btn-step-${next}`);
            if (btn && btn.classList.contains('completed')) next = stepMap[next] || 6;
            else break;
        }
        if (next && next <= 5) {
            if ([2, 3, 5].includes(next)) goToStep(next);
            else proceedToStep(next);
        } else if (next === 6) {
            window.location.reload();
        }
    }

    function completeStep(step) {
        if (step !== 1) return;
        fetch('{{ route("student.lesson.complete") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({ class_id: classId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('#lessonMaterialsList .material-item').forEach(item => {
                    const statusIcon = item.querySelector('.material-status i');
                    if (statusIcon) { statusIcon.className = 'fas fa-check-circle'; statusIcon.style.color = '#00AA66'; }
                    const btn = item.querySelector('.btn-mark-viewed');
                    if (btn) { btn.textContent = 'Completed'; btn.disabled = true; }
                });

                updateLessonProgress();

                const btnStep1 = document.getElementById('btn-step-1');
                btnStep1.classList.remove('active');
                btnStep1.classList.add('completed');
                btnStep1.innerHTML = `<i class="fas fa-check-circle"></i> Lesson Materials`;

                const btnStep2 = document.getElementById('btn-step-2');
                if (btnStep2) {
                    btnStep2.classList.remove('disabled');
                    const lockBadge = btnStep2.querySelector('.lock-badge');
                    if (lockBadge) lockBadge.remove();
                }

                showSuccessMessage('✅ Lesson Completed! Proceeding to Pre-Assessment...');
                setTimeout(() => goToStep(2), 2000);
            } else { alert('Error completing lesson.'); }
        })
        .catch(err => console.error(err));
    }

    function showSuccessMessage(message) {
        const alert = document.createElement('div');
        alert.className = 'proctoring-alert';
        alert.style.background = '#00AA66';
        alert.style.color = 'white';
        alert.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    function closeCameraModal() { document.getElementById('cameraModal').classList.remove('active'); }
    function requestCameraAccess() { closeCameraModal(); startFloatingCamera(); }

    const floatingCamera = document.getElementById('floatingCamera');
    let isDragging = false;
    let initialX, initialY;

    if (floatingCamera) {
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
        document.addEventListener('mouseup', () => { isDragging = false; });
    }

    // ---------- Congratulations Modal ----------
    function showCongratsModal() {
        const overlay = document.getElementById('congratsOverlay');
        if (!overlay) return;
        overlay.classList.add('active');
        stopFloatingCamera();
        clearInterval(timerInterval);
        examActive = false;
    }

    function closeCongratsModal() {
        const overlay = document.getElementById('congratsOverlay');
        if (overlay) overlay.classList.remove('active');
        // Stay on same page — student can now click any completed tab to review
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('congratsOverlay');
            if (overlay && overlay.classList.contains('active')) closeCongratsModal();
        }
    });

    // ---------- Initialise ----------
    document.addEventListener('DOMContentLoaded', () => {
        const firstAvailableStep = getFirstAvailableStep();
        if (firstAvailableStep) {
            // If everything is done, show congrats
            if ($isAllDone) {
                showCongratsModal();
            }
            proceedToStep(firstAvailableStep);
        } else {
            showCongratsModal();
        }
    });

    function getFirstAvailableStep() {
        for (let step = 1; step <= 5; step++) {
            const btn = document.getElementById(`btn-step-${step}`);
            if (!btn) continue;
            // Available (not disabled, not yet done) → take it
            if (!btn.classList.contains('disabled') && !btn.classList.contains('completed')) return step;
        }
        // Nothing to take → return first completed step for review
        for (let step = 1; step <= 5; step++) {
            const btn = document.getElementById(`btn-step-${step}`);
            if (btn && btn.classList.contains('completed')) return step;
        }
        return null;
    }
</script>
@endpush