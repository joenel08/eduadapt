@extends('layouts.teacher-student')

@section('page_title', 'View ' . ucfirst($type) . ' - ' . $subject . ' - ' . $week)
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
        View {{ ucfirst(str_replace('learningMaterial', 'Learning Material', $type)) }}
    </div>
    <div class="week-subheader">Full details</div>

    <div class="card">
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-row">
                    <div class="detail-label">Title</div>
                    <div class="detail-value">{{ $item->title ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Type</div>
                    <div class="detail-value">{{ ucfirst(str_replace('learningMaterial', 'Learning Material', $type)) }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Uploaded</div>
                    <div class="detail-value">{{ $item->created_at->format('F j, Y g:i A') }}</div>
                </div>

                @if($type === 'learningMaterial')
                <div class="detail-row">
                    <div class="detail-label">Description</div>
                    <div class="detail-value">{{ $item->description ?? 'No description' }}</div>
                </div>
                @if($item->file_path)
                <div class="detail-row">
                    <div class="detail-label">File</div>
                    <div class="detail-value">
                        <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
                @endif
                @elseif(in_array($type, ['preAssessment', 'postAssessment']))
                <div class="detail-row">
                    <div class="detail-label">Exam Type</div>
                    <div class="detail-value">{{ $item->exam_type }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Input Method</div>
                    <div class="detail-value">{{ $item->input_method }}</div>
                </div>
                @if($item->file_name)
                <div class="detail-row">
                    <div class="detail-label">Uploaded File</div>
                    <div class="detail-value">{{ $item->file_name }}</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-label">Questions</div>
                    <div class="detail-value">
                        @if($item->questions)
                        @php $questions = is_string($item->questions) ? json_decode($item->questions, true) : $item->questions; @endphp
                        @if($questions)
                        <div class="questions-container">
                            @foreach($questions as $idx => $q)
                            <div class="question-item">
                                <strong>Q{{ $idx+1 }}:</strong> {{ $q['question'] ?? 'N/A' }}
                                @if(isset($q['type']) && $q['type'] === 'multipleChoice' && isset($q['choices']))
                                <ul>
                                    @foreach($q['choices'] as $ci => $choice)
                                    <li>{{ $choice }} @if(isset($q['correctAnswer']) && $q['correctAnswer'] === $choice) <span class="badge badge-success">✓ Correct</span> @endif</li>
                                    @endforeach
                                </ul>
                                @elseif(isset($q['type']) && $q['type'] === 'trueFalse')
                                <p>Correct Answer: {{ $q['correctAnswer'] ?? 'N/A' }}</p>
                                @elseif(isset($q['type']) && $q['type'] === 'matchingType' && isset($q['pairs']))
                                <ul>
                                    @foreach($q['pairs'] as $pair)
                                    <li>{{ $pair['question'] ?? $pair['left'] ?? '?' }} ↔ {{ $pair['answer'] ?? $pair['right'] ?? '?' }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p>No questions</p>
                        @endif
                        @else
                        <p>No questions</p>
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Timer</div>
                    <div class="detail-value">{{ $item->settings['timer'] ?? '00:00:00' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Due Date</div>
                    <div class="detail-value">{{ $item->settings['due_date'] ?? 'Not set' }}</div>
                </div>
                @elseif($type === 'interventionMaterial')
                <div class="detail-row">
                    <div class="detail-label">File Name</div>
                    <div class="detail-value">{{ $item->file_name ?? 'N/A' }}</div>
                </div>
                @if(isset($item->file_path) && $item->file_path)
                <div class="detail-row">
                    <div class="detail-label">File</div>
                    <div class="detail-value">
                        <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
                @endif

                @elseif($type === 'interventionVideo')
                <div class="detail-row">
                    <div class="detail-label">Video Type</div>
                    <div class="detail-value">{{ ucfirst($item->video_type ?? 'N/A') }}</div>
                </div>
                @if($item->video_type === 'link' && $item->video_url)
                <div class="detail-row">
                    <div class="detail-label">Video URL</div>
                    <div class="detail-value">
                        <a href="{{ $item->video_url }}" target="_blank">{{ $item->video_url }}</a>
                    </div>
                </div>
                @elseif($item->file_name)
                <div class="detail-row">
                    <div class="detail-label">Uploaded File</div>
                    <div class="detail-value">{{ $item->file_name }}</div>
                </div>
                @endif

                @elseif($type === 'interventionQuiz')
                <div class="detail-row">
                    <div class="detail-label">Exam Type</div>
                    <div class="detail-value">{{ $item->exam_type ?? 'N/A' }}</div>
                </div>
                @if($item->file_name)
                <div class="detail-row">
                    <div class="detail-label">Uploaded File</div>
                    <div class="detail-value">{{ $item->file_name }}</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-label">Questions</div>
                    <div class="detail-value">
                        @if($item->questions)
                        @php $questions = is_string($item->questions) ? json_decode($item->questions, true) : $item->questions; @endphp
                        @if($questions)
                        <div class="questions-container">
                            @foreach($questions as $idx => $q)
                            <div class="question-item">
                                <strong>Q{{ $idx+1 }}:</strong> {{ $q['question'] ?? 'N/A' }}
                                @if(isset($q['type']) && $q['type'] === 'multipleChoice' && isset($q['choices']))
                                <ul>
                                    @foreach($q['choices'] as $ci => $choice)
                                    <li>{{ $choice }} @if(isset($q['correctAnswer']) && $q['correctAnswer'] === $choice) <span class="badge badge-success">✓ Correct</span> @endif</li>
                                    @endforeach
                                </ul>
                                @elseif(isset($q['type']) && $q['type'] === 'trueFalse')
                                <p>Correct Answer: {{ $q['correctAnswer'] ?? 'N/A' }}</p>
                                @elseif(isset($q['type']) && $q['type'] === 'matchingType' && isset($q['pairs']))
                                <ul>
                                    @foreach($q['pairs'] as $pair)
                                    <li>{{ $pair['question'] ?? $pair['left'] ?? '?' }} ↔ {{ $pair['answer'] ?? $pair['right'] ?? '?' }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p>No questions</p>
                        @endif
                        @else
                        <p>No questions</p>
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Settings</div>
                    <div class="detail-value">
                        @php $settings = is_string($item->settings) ? json_decode($item->settings, true) : $item->settings; @endphp
                        @if($settings)
                        Timer: {{ $settings['timer'] ?? '00:00:00' }}<br>
                        Due Date: {{ $settings['due_date'] ?? 'Not set' }}
                        @else
                        No additional settings
                        @endif
                    </div>
                </div>
                @endif
            </div>
            <hr style="margin: 10px 0 20px 0;">
            <div class="form-group" style="margin-top: 10px;">

                <a href="{{ route('teacher.content-library.edit', [$grade, $term, $subject, $week, $type, $item->id]) }}" class="btn btn-edit ">Edit</a>
            </div>
        </div>
    </div>
</div>
@endsection