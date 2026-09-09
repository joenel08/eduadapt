@extends('layouts.teacher-student')

@section('page_title', 'Progress Tracking')
@section('page', 'progress')

@section('content')
<style>
    /* ============ FILTER BUTTONS ============ */
        .filter-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            border-color: #0066CC;
            color: #0066CC;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #0066CC 0%, #004D99 100%);
            color: white;
            border-color: transparent;
        }

        /* ============ TABLE ============ */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9f9f9;
            border-bottom: 2px solid #e0e0e0;
        }

        th {
            padding: 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            color: #666;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .lesson-name {
            font-weight: 600;
            color: #333;
        }

        .lesson-class {
            font-size: 12px;
            color: #999;
            margin-top: 2px;
        }

        /* ============ STATUS BADGES ============ */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed {
            background: #e0ffe0;
            color: #00AA66;
        }

        .status-inprogress {
            background: #fff3cd;
            color: #FF8800;
        }

        .status-locked {
            background: #f0f0f0;
            color: #999;
        }

        .status-badge i {
            font-size: 11px;
        }

        /* ============ PROGRESS BAR ============ */
        .progress-bar-mini {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar-mini-fill {
            height: 100%;
            background: linear-gradient(90deg, #0066CC, #004D99);
        }

        .score-value {
            font-weight: 600;
            color: #333;
        }

        /* ============ PERFORMANCE CATEGORY BADGES ============ */
        .category-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-below-average {
            background: #FFF0F0;
            color: #FF6B6B;
        }

        .badge-average {
            background: #FFF8F0;
            color: #FFB84D;
        }

        .badge-advanced {
            background: #F0FFF4;
            color: #51CF66;
        }
</style>
<div class="content">
    <h1 class="page-title">
        <i class="fas fa-chart-line"></i>
        Progress Tracking
    </h1>
    <p class="page-subtitle">Monitor your learning journey and performance</p>

    <!-- FILTER BUTTONS -->
    <div class="filter-buttons">
        <button class="filter-btn active" onclick="filterProgress('all', this)">
            <i class="fas fa-list"></i> All
        </button>
        <button class="filter-btn" onclick="filterProgress('completed', this)">
            <i class="fas fa-circle-check"></i> Completed
        </button>
        <button class="filter-btn" onclick="filterProgress('in-progress', this)">
            <i class="fas fa-hourglass-half"></i> In Progress
        </button>
        <button class="filter-btn" onclick="filterProgress('pending', this)">
            <i class="fas fa-clock"></i> Pending
        </button>
        <button class="filter-btn" onclick="filterProgress('locked', this)">
            <i class="fas fa-lock"></i> Locked
        </button>
    </div>

    <!-- TABLE -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Lesson</th>
                    <th>Class</th>
                    <th>Status</th>
                    <th>Pre-Test</th>
                    <th>Category</th>
                    <th>Attempts</th>
                    <th>Post-Test</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody id="progressTableBody">
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ============ DATA FROM CONTROLLER ============
    const progressData = @json($items);

    // ============ HELPERS ============
    function determinePerformanceCategory(percentage) {
        if (percentage < 60) return 'below-average';
        if (percentage < 80) return 'average';
        return 'advanced';
    }

    function getCategoryDisplay(postScore) {
        if (postScore === null || postScore === undefined) {
            return { label: '-', badgeClass: '' };
        }
        const category = determinePerformanceCategory(postScore);
        const labels = {
            'below-average': 'Below Average',
            'average': 'Average',
            'advanced': 'Advanced'
        };
        return { label: labels[category], badgeClass: `badge-${category}` };
    }

    let currentFilter = 'all';

    // ============ RENDER ============
    function render() {
        const table = document.getElementById('progressTableBody');
        table.innerHTML = '';

        const filtered = currentFilter === 'all'
            ? progressData
            : progressData.filter(item => {
                if (currentFilter === 'completed') return item.status === 'completed';
                if (currentFilter === 'in-progress') return item.status === 'in-progress' || item.status === 'viewed';
                if (currentFilter === 'pending') return item.status === 'pending';
                if (currentFilter === 'locked') return item.status === 'locked';
                return true;
            });

        filtered.forEach(item => {
            const statusIcons = {
                'completed': '<i class="fas fa-circle-check"></i>',
                'in-progress': '<i class="fas fa-hourglass-half"></i>',
                'viewed': '<i class="fas fa-eye"></i>',
                'pending': '<i class="fas fa-clock"></i>',
                'locked': '<i class="fas fa-lock"></i>'
            };
            const statusLabel = item.status.replace('-', ' ');
            const statusIcon = statusIcons[item.status] || '<i class="fas fa-question"></i>';

            const categoryDisplay = getCategoryDisplay(item.post_score);
            const progress = item.progress_percent ?? 0;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="lesson-name">${escapeHtml(item.title)}</div>
                    <div class="lesson-class" style="font-size:12px; color:#999;">${escapeHtml(item.content_type)}</div>
                </td>
                <td>${escapeHtml(item.class_name)}</td>
                <td><span class="status-badge status-${item.status.replace('-', '')}">${statusIcon} ${statusLabel}</span></td>
                <td><span class="score-value">${item.pre_score !== null ? item.pre_score + '%' : '-'}</span></td>
                <td>${categoryDisplay.badgeClass ? `<span class="category-badge ${categoryDisplay.badgeClass}">${categoryDisplay.label}</span>` : categoryDisplay.label}</td>
                <td>${item.attempts}</td>
                <td><span class="score-value">${item.post_score !== null ? item.post_score + '%' : '-'}</span></td>
                <td>
                    <div style="margin-bottom: 6px; font-weight: 600; color: #333;">${progress}%</div>
                    <div class="progress-bar-mini">
                        <div class="progress-bar-mini-fill" style="width: ${progress}%"></div>
                    </div>
                </td>
            `;
            table.appendChild(row);
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function filterProgress(type, btn) {
        currentFilter = type;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        render();
    }

    // ============ INITIALIZE ============
    document.addEventListener('DOMContentLoaded', function() {
        render();
    });
</script>
@endpush