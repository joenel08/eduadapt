@extends('layouts.teacher-student')

@section('page_title', 'Content Library')
@section('page', 'content-library')

@section('content')
<div class="landing-page-container">
    @php
        $terms = ['Term 1', 'Term 2', 'Term 3'];
    @endphp

    @foreach($grades as $grade)
        <div class="grade-container">
            <div class="grade-header">
                <i class="fas fa-book"></i> {{ $grade }}
            </div>
            <div class="quarters-grid">
                @foreach($terms as $term)
                    <div class="quarter-item" onclick="window.location.href='{{ route('teacher.content-library.subjects', [$grade, $term]) }}'">
                        <div class="quarter-icon"><i class="fas fa-folder"></i></div>
                        <div class="quarter-name">{{ $term }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection