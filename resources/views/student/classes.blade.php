@extends('layouts.teacher-student')

@section('page_title', 'My Classes')
@section('page', 'my-classes')



@section('content')
<div class="content-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
    <div>
        <h1 class="page-title" style="font-size:28px; font-weight:700;">
            <i class="fas fa-chalkboard-user" style="color:#0066CC;"></i>
            My Classes
        </h1>
        <p class="page-subtitle" style="color:#999;">Explore your enrolled courses</p>
    </div>
</div>

@if($classes->count())
<div class="classes-grid">
    @foreach($classes as $class)
    <div class="class-card">
        <div class="class-header">
            <div class="class-header-info">
                <!-- <div class="class-name">{{ $class->grade_level }} - {{ $class->section_name }}</div> -->
                <div class="class-name">{{ $class->subject_name }}</div>
            </div>
            <div class="class-icon">
                <i class="fas fa-book"></i>
            </div>
        </div>
        <div class="class-body">
            <span class="class-status">Active</span>
            <div class="class-progress-label">
                <span>Progress</span>
                <span>{{ $class->progress }}%</span>
            </div>
            <div class="class-progress-bar">
                <div class="class-progress-fill" style="width: {{ $class->progress }}%"></div>
            </div>
            <div class="class-header-info">
                <div class="class-name">{{ $class->grade_level }} - {{ $class->section_name }}</div>
                <div class="class-teacher">{{ $class->subject_name }}</div> <!-- single subject, not list -->
            </div>

            <button class="class-enter-btn" onclick="window.location.href='{{ route('student.class.details', ['classId' => $class->id, 'subjectId' => $class->subject_id]) }}'">
                <i class="fas fa-arrow-right"></i> Enter Class
            </button>

        </div>
    </div>
    @endforeach
</div>
@else
<div class="panel" style="padding:40px 20px; text-align:center; background:white; border-radius:12px;">
    <i class="fas fa-book-open" style="font-size:48px; color:#ccc; margin-bottom:16px;"></i>
    <p style="color:var(--muted); font-size:16px;">You are not enrolled in any classes yet. Contact the administrator.</p>
</div>
@endif
@endsection