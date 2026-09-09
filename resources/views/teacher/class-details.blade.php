@extends('layouts.teacher-student')

@section('page_title', 'My Classes')
@section('page', 'classes')

@section('content')
<!-- Content -->
<style>
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 25px;
        font-size: 14px;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0066CC;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .breadcrumb-item:hover {
        color: #004D99;
        gap: 10px;
    }

    .breadcrumb-item i {
        font-size: 16px;
    }

    .breadcrumb-separator {
        color: #ccc;
        font-size: 18px;
    }

    .breadcrumb-item.active {
        color: #666;
        cursor: default;
        font-weight: 600;
    }

    .breadcrumb-item.active:hover {
        color: #666;
        gap: 8px;
    }

    .page-header {
        margin-bottom: 25px;
    }

    .page-header h1 {
        font-size: 28px;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-header p {
        font-size: 14px;
        color: #999;
        margin-top: 5px;
    }

    .category-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .category-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-top: 4px solid;
        cursor: pointer;
    }

    .category-card.below-average {
        border-top-color: #FF6B6B;
    }

    .category-card.average {
        border-top-color: #FFB84D;
    }

    .category-card.advanced {
        border-top-color: #51CF66;
    }

    .category-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
    }

    .category-card.active {
        border: 3px solid #0066CC;
    }

    .category-label {
        font-size: 12px;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 15px;
    }

    .category-count {
        font-size: 36px;
        font-weight: 700;
        color: #333;
    }
</style>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a class="breadcrumb-item" href="{{ route('teacher.classes') }}"><i class="fas fa-home"></i> My Classes</a>
    <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
    <span class="breadcrumb-item active"> {{ optional($class->teacherAssignments->first())->subject->name ?? $class->section_name }}</span>
</div>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-book"></i>{{ optional($class->teacherAssignments->first())->subject->name ?? $class->section_name }}</h1>
    <p> {{ $class->grade_level }} • {{ $class->section_name }} • Class Code: {{ $class->class_code ?? 'N/A' }}</p>
</div>

<!-- Tabs Container -->
<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-btn active" onclick="openTab(event, 'students')"><i class="fas fa-users"></i> Student List</button>
        <button class="tab-btn" onclick="openTab(event, 'analytics')"><i class="fas fa-chart-bar"></i> Analytics</button>
        <button class="tab-btn" onclick="openTab(event, 'examverif')"><i class="fas fa-certificate"></i> Exam Verification</button>
    </div>

    <div class="tabs-content">
        <!-- STUDENT LIST TAB -->
        <div id="students" class="tab-pane active">
            <div style="margin-bottom:20px;">
                <input type="text" id="studentSearch" placeholder="Search by Name or LRN..." class="form-input" onkeyup="filterStudents()">
            </div>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>LRN</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    @forelse ($students as $student)
                    <tr>
                        <td>
                            <div class="student-name">
                                <div class="student-avatar">{{ $student->initials ?? 'NA' }}</div>
                                <span>{{ $student->name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->lrn }}</td>
                        <td>
                            @if ($student->category === 'advanced')
                            <span class="category-badge badge-advanced">Advanced</span>
                            @elseif ($student->category === 'average')
                            <span class="category-badge badge-average">Average</span>
                            @elseif ($student->category === 'below_average')
                            <span class="category-badge badge-below-average">Below Average</span>
                            @else
                            <span class="category-badge badge-not-assessed">Not Assessed</span>
                            @endif
                        </td>
                        <td><button class="btn-remove" onclick="handleRemoveStudent(this)"><i class="fas fa-trash-alt"></i> Remove</button></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">No students enrolled.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ANALYTICS TAB -->
        <div id="analytics" class="tab-pane">
            <!-- Stats -->
            <div class="analytics-stats-grid">
                <div class="analytics-stat-box">
                    <div class="analytics-stat-label">Total Students</div>
                    <div class="analytics-stat-value">{{ $stats['total'] }}</div>
                </div>
                <div class="analytics-stat-box green">
                    <div class="analytics-stat-label">Advanced</div>
                    <div class="analytics-stat-value">{{ $stats['advanced'] }}</div>
                </div>
                <div class="analytics-stat-box orange">
                    <div class="analytics-stat-label">Average</div>
                    <div class="analytics-stat-value">{{ $stats['average'] }}</div>
                </div>
                <div class="analytics-stat-box red">
                    <div class="analytics-stat-label">Below Average</div>
                    <div class="analytics-stat-value">{{ $stats['below_average'] }}</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="analytics-charts-grid">
                <div class="analytics-chart-card">
                    <h3 class="analytics-chart-title"><i class="fas fa-chart-bar"></i> Score Distribution</h3>
                    <div class="chart-container"><canvas id="scoreDistributionChart"></canvas></div>
                </div>
                <div class="analytics-chart-card">
                    <h3 class="analytics-chart-title"><i class="fas fa-chart-pie"></i> Readiness Distribution</h3>
                    <div class="chart-container"><canvas id="readinessChart"></canvas></div>
                </div>
            </div>

            <!-- Pre vs Post Comparison -->
            <div class="analytics-chart-card" style="margin-bottom:30px;">
                <h3 class="analytics-chart-title"><i class="fas fa-arrow-trending-up"></i> Class Overall Pre-test vs Post-test Comparison</h3>
                <div class="chart-container" style="height:350px;"><canvas id="prePostComparisonChart"></canvas></div>
            </div>

            <!-- Performance Table -->
            <div class="analytics-performance-table">
                <div class="analytics-table-header">
                    <h3>Student Performance Details</h3>
                </div>
                <div class="analytics-table-body">
                    <table class="analytics-data-table" id="performanceTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>LRN</th>
                                <th>Pre-test</th>
                                <th>Post-test</th>
                                <th>Intervention</th>
                                <th>Category</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody id="performanceTableBody">
                            @foreach ($students as $student)
                            @php
                            $pre = $student->pre_score ?? null;
                            $post = $student->post_score ?? null;
                            $progress = $post && $pre ? $post - $pre : null;
                            $nameParts = array_filter(explode(' ', trim($student->name ?? '')));
                            $initials = strtoupper(implode('', array_map(fn($part) => $part[0], $nameParts))) ?: 'NA';
                            @endphp
                            <tr>
                                <td>
                                    <div class="student-name">
                                        <div class="student-avatar">{{ $initials }}</div><span>{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $student->lrn }}</td>
                                <td>{{ $pre ?? 'N/A' }}</td>
                                <td>{{ $post ?? 'N/A' }}</td>
                                <td>{{ $student->intervention_score ?? 'N/A' }}</td>
                                <td>
                                    @if ($student->category === 'advanced')
                                    <span class="category-badge badge-advanced">Advanced</span>
                                    @elseif ($student->category === 'average')
                                    <span class="category-badge badge-average">Average</span>
                                    @elseif ($student->category === 'below_average')
                                    <span class="category-badge badge-below-average">Below Average</span>
                                    @else
                                    <span class="category-badge badge-not-assessed">Not Assessed</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($progress !== null)
                                    <div class="progress-bar-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar {{ $progress >= 0 ? 'green' : 'orange' }}" style="width: {{ min(abs($progress), 100) }}%;"></div>
                                        </div>
                                        <span class="progress-text">{{ $progress >= 0 ? '+' : '' }}{{ $progress }}%</span>
                                    </div>
                                    @else
                                    <span>N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div id="emptyTableState" class="empty-state" style="display:none;">
                        <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                        <div class="empty-state-title">No Student Data</div>
                        <div class="empty-state-text">Student performance data will appear here once assessments are completed</div>
                    </div>
                </div>
            </div>

            <!-- WEEKLY BREAKDOWN TABLE -->
            <div class="analytics-performance-table" style="margin-top:30px;">
                <div class="analytics-table-header">
                    <h3>Weekly Assessment Breakdown</h3>
                </div>
                <div class="analytics-table-body">
                    <table class="analytics-data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                @php
                                $weeks = collect();
                                foreach ($weeklyData as $weeksData) {
                                foreach ($weeksData as $week => $scores) {
                                $weeks->push($week);
                                }
                                }
                                $weeks = $weeks->unique()->sort();
                                @endphp
                                @foreach ($weeks as $week)
                                <th colspan="4" style="text-align:center;">{{ $week }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th></th>
                                @foreach ($weeks as $week)
                                <th>Pre</th>
                                <th>Post</th>
                                <th>Interv</th>
                                <th>Category</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($weeklyData as $studentName => $weeksData)
                            <tr>
                                <td>{{ $studentName }}</td>
                                @foreach ($weeks as $week)
                                @php
                                $data = $weeksData[$week] ?? ['pre' => '—', 'post' => '—', 'intervention' => '—', 'category' => 'not_assessed'];
                                $pre = $data['pre'] ?? '—';
                                $post = $data['post'] ?? '—';
                                $intervention = $data['intervention'] ?? '—';
                                $category = $data['category'] ?? 'not_assessed';
                                $categoryLabel = match($category) {
                                'advanced' => 'Advanced',
                                'average' => 'Average',
                                'below_average' => 'Below Average',
                                default => 'Not Assessed',
                                };
                                $badgeClass = match($category) {
                                'advanced' => 'badge-advanced',
                                'average' => 'badge-average',
                                'below_average' => 'badge-below-average',
                                default => 'badge-not-assessed',
                                };
                                @endphp
                                <td>{{ $pre }}</td>
                                <td>{{ $post }}</td>
                                <td>{{ $intervention }}</td>
                                <td><span class="category-badge {{ $badgeClass }}">{{ $categoryLabel }}</span></td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- EXAM VERIFICATION TAB -->
        <div id="examverif" class="tab-pane">
            <div style="margin-bottom:20px;">
                <input type="text" id="examSearch" placeholder="Search by Student Name or Assessment..." class="form-input" onkeyup="filterExamRecords()">
            </div>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Assessment</th>
                        <th>Score</th>
                        <th>Date Taken</th>
                        <th>Recording</th>
                    </tr>
                </thead>
                <tbody id="examTableBody">
                    @forelse ($examRecords as $record)
                    @php
                    // Use the student name map passed from controller
                    $studentName = $studentNameMap[$record->student_id] ?? 'Student (ID: ' . $record->student_id . ')';
                    $nameParts = array_filter(explode(' ', trim($studentName)));
                    $initials = strtoupper(implode('', array_map(fn($part) => $part[0], $nameParts))) ?: 'NA';
                    @endphp
                    <tr>
                        <td>
                            <div class="student-name">
                                <div class="student-avatar">{{ $initials }}</div><span>{{ $studentName }}</span>
                            </div>
                        </td>
                        <td>{{ ucfirst($record->exam_type) }}</td>
                        <td>{{ $record->score ?? '—' }} / 100</td>
                        <td>{{ $record->created_at->format('M d, Y') }}</td>
                        <td>
                            <button class="btn-view"
                                data-student="{{ json_encode($studentName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                data-assessment="{{ json_encode(ucfirst($record->exam_type), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                data-score="{{ json_encode($record->score, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                data-date="{{ json_encode($record->created_at->format('M d, Y'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                data-video="{{ json_encode($record->video_path ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                onclick="openVideoModalFromData(this)">
                                <i class="fas fa-play-circle"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No exam records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="examEmptyState" class="empty-state" style="display:none;">
                <div class="empty-state-icon"><i class="fas fa-file-video"></i></div>
                <div class="empty-state-title">No Exam Records</div>
                <div class="empty-state-text">No recorded exams found. Student assessments will appear here.</div>
            </div>
        </div>
    </div>
</div>

<!-- VIDEO PLAYER MODAL -->
<!-- VIDEO PLAYER MODAL -->
<div class="modal-overlay" id="videoModalOverlay">
    <div class="video-modal">
        <div class="video-modal-header">
            <div class="video-modal-header-content">
                <h2 class="video-modal-title"><i class="fas fa-film"></i> <span id="videoModalTitle">Exam Recording</span></h2>
                <div class="video-modal-info">
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Student</div>
                        <div class="video-modal-info-value" id="videoStudentName">-</div>
                    </div>
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Assessment</div>
                        <div class="video-modal-info-value" id="videoAssessmentType">-</div>
                    </div>
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Score</div>
                        <div class="video-modal-info-value" id="videoScore">-</div>
                    </div>
                    <div class="video-modal-info-item">
                        <div class="video-modal-info-label">Date Taken</div>
                        <div class="video-modal-info-value" id="videoDateTaken">-</div>
                    </div>
                </div>
            </div>
            <button class="video-modal-close" onclick="closeVideoModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="video-modal-body">
            <div class="video-player-wrapper">
                <div class="video-placeholder" id="videoPlaceholder">
                    <div class="video-placeholder-icon"><i class="fas fa-video"></i></div>
                    <div class="video-placeholder-text">No recording available for this exam.</div>
                </div>
                <video id="videoPlayer" class="video-player" style="display: none;" controls>
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <!-- ✅ NEW: Download Button -->
            <div class="video-download-wrapper" id="videoDownloadWrapper" style="display: none; text-align: center; margin-top: 15px;">
                <a id="videoDownloadLink" href="#" download="exam_recording.mp4" class="btn btn-download">
                    <i class="fas fa-download"></i> Download Video
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.openTab = function(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-pane");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    };

    window.filterStudents = function() {
        var input = document.getElementById('studentSearch');
        var filter = input.value.toUpperCase().trim();
        var table = document.getElementById('studentTableBody');
        var tr = table.getElementsByTagName('tr');
        for (var i = 0; i < tr.length; i++) {
            var nameTd = tr[i].getElementsByTagName('td')[0];
            var lrnTd = tr[i].getElementsByTagName('td')[1];
            if (nameTd && lrnTd) {
                var studentSpan = nameTd.querySelector('.student-name span');
                var studentText = studentSpan ? (studentSpan.textContent || studentSpan.innerText) : '';
                var lrnText = lrnTd.textContent || lrnTd.innerText;
                var match = studentText.toUpperCase().indexOf(filter) > -1 || lrnText.toUpperCase().indexOf(filter) > -1;
                tr[i].style.display = match ? '' : 'none';
            }
        }
    };

    window.filterExamRecords = function() {
    var input = document.getElementById('examSearch');
    var filter = input.value.toUpperCase().trim();
    var table = document.getElementById('examTableBody');
    var tr = table.getElementsByTagName('tr');
    for (var i = 0; i < tr.length; i++) {
        var studentTd = tr[i].getElementsByTagName('td')[0];
        var assessmentTd = tr[i].getElementsByTagName('td')[1];
        if (studentTd && assessmentTd) {
            var studentSpan = studentTd.querySelector('.student-name span');
            var studentText = studentSpan ? (studentSpan.textContent || studentSpan.innerText) : '';
            var assessmentText = assessmentTd.textContent || assessmentTd.innerText;
            var match = studentText.toUpperCase().indexOf(filter) > -1 || assessmentText.toUpperCase().indexOf(filter) > -1;
            tr[i].style.display = match ? '' : 'none';
        }
    }
};
    window.handleRemoveStudent = function(btn) {
        if (confirm('Are you sure you want to remove this student?')) {
            var row = btn.closest('tr');
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        }
    };

    window.handleAccept = function(btn) {
        var row = btn.closest('tr');
        var name = row.cells[0].innerText;
        var lrn = row.cells[1].innerText;
        var table = document.getElementById('studentTableBody');
        var newRow = table.insertRow();
        var initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
        newRow.innerHTML = `
            <td><div class="student-name"><div class="student-avatar">${initials}</div><span>${name}</span></div></td>
            <td>${lrn}</td>
            <td>Average</td>
            <td><button class="btn-remove" onclick="handleRemoveStudent(this)"><i class="fas fa-trash-alt"></i> Remove</button></td>
        `;
        row.remove();
        checkEmptyRequests();
    };

    window.handleReject = function(btn) {
        btn.closest('tr').remove();
        checkEmptyRequests();
    };

    function checkEmptyRequests() {
        var rows = document.getElementById('requestTableBody').rows;
        var empty = document.getElementById('requestEmptyState');
        empty.style.display = rows.length === 0 ? 'block' : 'none';
    }

    window.openVideoModal = function(event, studentName, assessment, score, dateTaken, videoPath) {
        event.preventDefault();

        document.getElementById('videoStudentName').textContent = studentName;
        document.getElementById('videoAssessmentType').textContent = assessment;
        document.getElementById('videoScore').textContent = score;
        document.getElementById('videoDateTaken').textContent = dateTaken;
        document.getElementById('videoModalTitle').textContent = assessment + ' - ' + studentName;

        var placeholder = document.getElementById('videoPlaceholder');
        var videoPlayer = document.getElementById('videoPlayer');
        var downloadWrapper = document.getElementById('videoDownloadWrapper');
        var downloadLink = document.getElementById('videoDownloadLink');

        if (videoPath && videoPath !== '') {
            // Show video player
            placeholder.style.display = 'none';
            videoPlayer.style.display = 'block';
            videoPlayer.querySelector('source').src = videoPath;
            videoPlayer.load();

            // Show download button
            downloadWrapper.style.display = 'block';
            // Set download link – use the full URL or relative path
            downloadLink.href = videoPath;
            // Extract filename from path or use a default
            var fileName = videoPath.split('/').pop() || 'exam_recording.mp4';
            downloadLink.download = fileName;
        } else {
            // Show placeholder
            placeholder.style.display = 'flex';
            videoPlayer.style.display = 'none';
            downloadWrapper.style.display = 'none';
        }

        document.getElementById('videoModalOverlay').classList.add('active');
    };

    window.closeVideoModal = function() {
        document.getElementById('videoModalOverlay').classList.remove('active');
        var videoPlayer = document.getElementById('videoPlayer');
        videoPlayer.pause();
    };

    document.getElementById('videoModalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeVideoModal();
    });

    document.getElementById('notificationBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('notificationDropdown').classList.toggle('active');
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-container')) {
            document.getElementById('notificationDropdown').classList.remove('active');
        }
    });

    document.getElementById('hamburgerBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    });
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
        document.getElementById('sidebar').classList.add('collapsed');
        document.getElementById('sidebarOverlay').classList.remove('active');
    });

    const analyticsData = {
        students: [
            @foreach($students as $student) {
                name: @json($student -> name),
                lrn: @json($student -> lrn),
                category: @json($student -> category),
                preScore: @json($student -> pre_score),
                postScore: @json($student -> post_score),
                interventionScore: @json($student -> intervention_score)
            },
            @endforeach
        ]
    };

    function initCharts() {
        const ranges = [{
                label: '0–50',
                min: 0,
                max: 50
            },
            {
                label: '51–70',
                min: 51,
                max: 70
            },
            {
                label: '71–85',
                min: 71,
                max: 85
            },
            {
                label: '86–100',
                min: 86,
                max: 100
            }
        ];
        const scores = analyticsData.students.map(s => s.postScore).filter(s => s !== null);
        const counts = ranges.map(r => scores.filter(s => s >= r.min && s <= r.max).length);

        const ctx1 = document.getElementById('scoreDistributionChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ranges.map(r => r.label),
                datasets: [{
                    label: 'Number of Students',
                    data: counts,
                    backgroundColor: ['rgba(255,107,107,0.8)', 'rgba(255,184,77,0.8)', 'rgba(81,207,102,0.8)', 'rgba(0,102,204,0.8)'],
                    borderColor: ['rgb(255,107,107)', 'rgb(255,184,77)', 'rgb(81,207,102)', 'rgb(0,102,204)'],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        const categories = analyticsData.students.map(s => s.category);
        const advanced = categories.filter(c => c === 'advanced').length;
        const average = categories.filter(c => c === 'average').length;
        const below = categories.filter(c => c === 'below_average').length;

        const ctx2 = document.getElementById('readinessChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Advanced', 'Average', 'Below Average'],
                datasets: [{
                    data: [advanced, average, below],
                    backgroundColor: ['rgba(81,207,102,0.8)', 'rgba(255,184,77,0.8)', 'rgba(255,107,107,0.8)'],
                    borderColor: ['rgb(81,207,102)', 'rgb(255,184,77)', 'rgb(255,107,107)'],
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        const preScores = analyticsData.students.map(s => s.preScore).filter(s => s !== null);
        const postScores = analyticsData.students.map(s => s.postScore).filter(s => s !== null);
        const preAvg = preScores.length ? preScores.reduce((a, b) => a + b, 0) / preScores.length : 0;
        const postAvg = postScores.length ? postScores.reduce((a, b) => a + b, 0) / postScores.length : 0;

        const ctx3 = document.getElementById('prePostComparisonChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: ['Class Average'],
                datasets: [{
                        label: 'Pre-test Average',
                        data: [preAvg],
                        backgroundColor: 'rgba(255,184,77,0.8)',
                        borderColor: 'rgb(255,184,77)',
                        borderWidth: 2,
                        borderRadius: 8
                    },
                    {
                        label: 'Post-test Average',
                        data: [postAvg],
                        backgroundColor: 'rgba(81,207,102,0.8)',
                        borderColor: 'rgb(81,207,102)',
                        borderWidth: 2,
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    }
    window.openVideoModalFromData = function(button) {
        var studentName = button.dataset.student;
        var assessment = button.dataset.assessment;
        var score = button.dataset.score;
        var dateTaken = button.dataset.date;
        var videoPath = button.dataset.video;

        // Call the existing openVideoModal with the extracted data
        openVideoModal(event, studentName, assessment, score, dateTaken, videoPath);
    };
    document.addEventListener('DOMContentLoaded', initCharts);
</script>
@endpush