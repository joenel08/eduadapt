@extends('layouts.teacher-student')

@section('page_title', 'Student Dashboard')
@section('page', 'dashboard')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
    <div class="page-title">Welcome, {{ auth()->user()->full_name }}</div>
    <div class=""><p>Current School Year: <span style="font-weight: 700;">{{ $activeSchoolYear->year }}</span> </p> </div>
</div>
<!-- Enrolled Classes List -->
<!-- @if($classes->count())
<div style="margin-top:30px;">
    <h3 class="section-title">Your Enrolled Classes</h3>
    <div class="grid-4">
        @foreach($classes as $class)
        <article class="card">
            <div class="card-header">
                <span class="card-title">{{ $class->grade_level }} - {{ $class->section_name }}</span>
                <span class="tag">{{ $class->student_count }} students</span>
            </div>
            <div class="card-value" style="font-size:18px;">{{ $class->school_year }}</div>
            
        </article>
        @endforeach
    </div>
</div>
@else -->
<div class="panel" style="margin-top:20px;">
    <p style="color:var(--muted);">You are not enrolled in any classes yet. Contact the administrator.</p>
</div>
@endif
<!-- <p class="page-subtitle">Your learning progress and tasks.</p> -->
<hr style="margin: 20px 0 20px 0">

<!-- Overview Cards -->
<div class="overview-grid">
    <div class="overview-card">
        <div class="card-icon blue">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="card-label">Enrolled Subjects</div>
        <div class="card-value">{{ $enrolledSubjects }}</div>
        <div class="card-detail">Active subjects</div>
    </div>
    <div class="overview-card">
        <div class="card-icon orange">
            <i class="fas fa-list-check"></i>
        </div>
        <div class="card-label">Pending Tasks</div>
        <div class="card-value">{{ $pendingTasks }}</div>
        <div class="card-detail">Not started</div>
    </div>
    <div class="overview-card">
        <div class="card-icon green">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="card-label">Completed</div>
        <div class="card-value">{{ $completedCount }}</div>
        <div class="card-detail">Finished tasks</div>
    </div>
    <!-- <div class="overview-card">
        <div class="card-icon red">
            <i class="fas fa-star"></i>
        </div>
        <div class="card-label">Average Score</div>
        <div class="card-value">{{ $averageScore }}</div>
        <div class="card-detail">Performance</div>
    </div> -->
</div>

<!-- Notifications Section -->
<div class="notifications-section">
    <h3 class="section-title">
        <i class="fas fa-bell"></i>
        Pending Tasks by Subject
    </h3>
    <div class="notification-list">
        @forelse($notifications as $note)
        <div class="notification-item-card" style="border-left: 6px solid {{ $note['color'] }}; padding-left: 20px;">
            <div class="notification-item-content">
                <div class="notification-item-title" style="font-weight: 700; font-size: 16px;">
                    {{ $note['class_name'] }} • {{ $note['subject_name'] }}
                </div>
                <ul style="list-style: none; padding: 0; margin: 8px 0 0 0;">
                    @foreach($note['items'] as $item)
                    <li style="padding: 4px 0; display: flex; align-items: center; flex-wrap: wrap;">
                        <i class="fas {{ $item['icon'] }}" style="margin-right: 10px; color: {{ $note['color'] }}; width: 18px;"></i>
                        <span style="font-weight: 500;">{{ $item['type_name'] }}:</span>
                        <span style="margin-left: 6px;">{{ $item['title'] }}</span>
                        <a href="{{ $item['link'] }}" style="margin-left: auto; color: #0066CC; text-decoration: none; font-weight: 600; font-size: 13px; white-space: nowrap;">
                            Go to Class →
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @empty
        <div class="notification-item-card">
            <div class="notification-item-content">
                <div class="notification-item-desc">🎉 No pending tasks! Great job!</div>
            </div>
        </div>
        @endforelse
    </div>
</div>

@endsection