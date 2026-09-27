@extends('layouts.app')

@section('page_title', 'Dashboard')
@section('page', 'dashboard')

@section('content')
<style>
    .panel-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 12px;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 14px 18px;
        background: #f9f9f9;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        text-decoration: none;
        color: #333;
        transition: all 0.25s ease;
    }

    .quick-action-btn:hover {
        border-color: #0066CC;
        background: #f0f7ff;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.1);
    }

    .quick-action-btn i {
        font-size: 22px;
        color: #0066CC;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 102, 204, 0.1);
        border-radius: 8px;
        flex-shrink: 0;
    }

    .quick-action-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    .quick-action-desc {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }
</style>
    <div class="page-title">Admin Dashboard</div>
    <p class="page-subtitle">Overview of master data and system statistics.</p>

    <div class="grid-4">
        <article class="card">
            <div class="card-header"><span class="card-title">School Year</span><span class="tag"><a href="{{ route('admin.school-years') }}">Add</a></span></div>
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

                <a href="{{ route('admin.school-years') }}" class="quick-action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <div>
                        <div class="quick-action-title">Add School Year</div>
                        <div class="quick-action-desc">Create a new academic year</div>
                    </div>
                </a>

                <a href="{{ route('admin.subjects') }}" class="quick-action-btn">
                    <i class="fas fa-book-medical"></i>
                    <div>
                        <div class="quick-action-title">Add Subject</div>
                        <div class="quick-action-desc">Register a new subject</div>
                    </div>
                </a>

                <a href="{{ route('admin.master-data') }}" class="quick-action-btn">
                    <i class="fas fa-file-upload"></i>
                    <div>
                        <div class="quick-action-title">Upload Master Data</div>
                        <div class="quick-action-desc">Import student & teacher records</div>
                    </div>
                </a>

            </div>
        </div>
    </div>
@endsection

