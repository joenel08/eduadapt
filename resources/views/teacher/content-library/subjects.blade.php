@extends('layouts.teacher-student')

@section('page_title', 'Subjects - ' . $grade . ' ' . $term)
@section('page', 'content-library')

@section('content')
<div class="back-button show" id="backButton">
    <button class="back-btn" id="backBtn">
        <i class="fas fa-arrow-left"></i>
        <span id="backButtonText">  <a href="{{ route('teacher.content-library') }}" class="btn btn-secondary">Back to Grades</a></span>
    </button>
</div>
<div class="grade-container">
    <div class="page-header">
        <div class="page-title">
            <i class="fas fa-book-open"></i>
            {{ $grade }} - {{ $term }} - Subjects
        </div>
      
    </div>
    <div class="folders-grid">
        @foreach($subjects as $subject)
            <div class="folder-item" onclick="window.location.href='{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}'">
                <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
                <div class="folder-name">{{ $subject }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection