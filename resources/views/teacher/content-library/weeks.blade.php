@extends('layouts.teacher-student')

@section('page_title', 'Weeks - ' . $subject)
@section('page', 'content-library')

@section('content')
<div class="back-button show" id="backButton">
    <button class="back-btn" id="backBtn">
        <i class="fas fa-arrow-left"></i>
        <span id="backButtonText"><a href="{{ route('teacher.content-library.subjects', [$grade, $term]) }}" class="btn btn-secondary">Back to Subjects</a></span>
    </button>
</div>
<div class="grade-container">
    <div class="page-header">
        <div class="page-title">
            <i class="fas fa-book"></i>
            {{ $subject }} - Weeks
        </div>
        
    </div>
    <div class="folders-grid">
        @foreach($weeks as $week)
            <div class="folder-item" onclick="window.location.href='{{ route('teacher.content-library.content', [$grade, $term, $subject, $week]) }}'">
                <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
                <div class="folder-name">{{ $week }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection