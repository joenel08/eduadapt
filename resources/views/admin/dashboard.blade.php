@extends('layouts.app')

@section('page_title', 'Dashboard')
@section('page', 'dashboard')

@section('content')
    <div class="page-title">Admin Dashboard</div>
    <p class="page-subtitle">Overview of master data and system statistics.</p>

    <div class="grid-4">
        <article class="card">
            <div class="card-header"><span class="card-title">School Year</span><span class="tag"><a href="{{ route('admin.school-years') }}">Change</a></span></div>
            <div class="card-value"> {{ $activeSchoolYear->year }}</div>
        </article>
        <article class="card">
            <div class="card-header"><span class="card-title">Total Students</span><span class="tag"><a href="{{ route('admin.master-data') }}">View</a></span></div>
            <div class="card-value">{{ $studentCount }}</div>
        </article>
        <article class="card">
            <div class="card-header"><span class="card-title">Total Teachers</span><span class="tag"><a href="{{ route('admin.master-data') }}">View</a></span></div>
            <div class="card-value">{{ $teacherCount }}</div>
        </article>
        <article class="card">
            <div class="card-header"><span class="card-title">Total Users</span></div>
            <div class="card-value">{{ $totalUsers }}</div>
        </article>
        
    </div>

    <div class="section-row">
        <div class="panel">
            <h3>Master Data Overview</h3>
            <p>All registration checks depend on the official student and teacher master lists. Upload files before approving accounts to ensure only verified IDs are permitted.</p>
            <div class="panel-grid">
                <div class="dashboard-stat-card"><div class="card-title">Student Records</div><div class="card-value">{{ $studentCount }}</div></div>
                <div class="dashboard-stat-card"><div class="card-title">Teacher Records</div><div class="card-value">{{ $teacherCount }}</div></div>
            </div>
        </div>
        <div class="panel">
            <h3>Quick Actions</h3>
            <div class="panel-actions">
                  </div>
        </div>
    </div>
@endsection