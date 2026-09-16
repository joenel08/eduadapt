@extends('layouts.teacher-student')

@section('page_title', 'Global Analytics Dashboard')
@section('page', 'global-analytics')

@section('content')

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1>
            <i class="fas fa-chart-pie"></i> Global Analytics Dashboard
        </h1>
        <p>Overview of all sections and subjects you teach</p>
    </div>

    <!-- SECTION 1: SUMMARY CARDS -->
    <!-- <div style="margin-bottom: 40px;">
        <h2 class="section-header">Summary Metrics</h2>
        <div class="summary-cards">
    
            <div class="summary-card score">
                <div class="summary-card-icon"><i class="fas fa-star"></i></div>
                <div class="summary-card-label">Overall Average Score</div>
                <div class="summary-card-value" id="overallScore">{{ $summary->overallScore }}</div>
                <div class="summary-card-change positive">
                    <i class="fas fa-arrow-up"></i> <span id="scoreChange">+{{ $summary->improvement }}%</span> improvement
                </div>
            </div>
          
            <div class="summary-card completion">
                <div class="summary-card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="summary-card-label">Completion Rate</div>
                <div class="summary-card-value" id="completionRate">{{ $summary->completionRate }}%</div>
                <div class="summary-card-change positive">
                    <i class="fas fa-arrow-up"></i> <span id="completionChange">{{ $summary->completionRate }}%</span> of students assessed
                </div>
            </div>
          
            <div class="summary-card improvement">
                <div class="summary-card-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="summary-card-label">Average Improvement</div>
                <div class="summary-card-value" id="improvementScore">+{{ $summary->improvement }}</div>
                <div class="summary-card-change positive">
                    <span id="improvementText">points gained</span> from pre to post
                </div>
            </div>
        </div>
    </div> -->

    <!-- SECTION 2: VISUAL REPORTS -->
    <div>
        <h2 class="section-header">Visual Reports</h2>
        <div class="charts-grid">
            <!-- 1. Readiness Distribution -->
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-chart-pie"></i> Readiness Distribution</div>
                <div class="chart-container-wrapper">
                    <canvas id="readinessChart"></canvas>
                </div>
            </div>
            <!-- 2. Performance by Subject -->
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-book"></i> Performance by Subject</div>
                <div class="chart-container-wrapper">
                    <canvas id="performanceSubjectChart"></canvas>
                </div>
            </div>
            <!-- 3. Progress Tracking -->
            <div class="chart-card chart-full-width">
                <div class="chart-title"><i class="fas fa-line-chart"></i> Progress Tracking - Weekly Improvement Trends</div>
                <div class="chart-container-wrapper">
                    <canvas id="progressTrackingChart"></canvas>
                </div>
            </div>
            <!-- 4. Pre-test vs Post-test Comparison -->
            <div class="chart-card chart-full-width">
                <div class="chart-title"><i class="fas fa-exchange-alt"></i> Pre-test vs Post-test System-wide Comparison</div>
                <div class="chart-container-wrapper">
                    <canvas id="prePostComparisonChart"></canvas>
                </div>
            </div>
            <!-- 5. Section Performance Comparison -->
            <div class="chart-card chart-full-width">
                <div class="chart-title"><i class="fas fa-columns"></i> Section Performance Comparison</div>
                <div class="chart-container-wrapper">
                    <canvas id="sectionComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ PASS DATA FROM CONTROLLER ============
    const analyticsData = {
        overallScore: {{ $summary->overallScore }},
        completionRate: {{ $summary->completionRate }},
        improvement: {{ $summary->improvement }},
        subjects: @json($chartData->subjects),
        weeklyProgress: @json($chartData->weeklyProgress),
        sections: @json($chartData->sections),
        readiness: @json($chartData->readiness),
        prePost: @json($chartData->prePost),
    };

    // ============ UPDATE SUMMARY CARDS ============
    function updateSummaryCards() {
        document.getElementById('overallScore').textContent = analyticsData.overallScore;
        document.getElementById('completionRate').textContent = analyticsData.completionRate + '%';
        document.getElementById('improvementScore').textContent = '+' + analyticsData.improvement;
        document.getElementById('scoreChange').textContent = '+' + analyticsData.improvement + '%';
        document.getElementById('completionChange').textContent = analyticsData.completionRate + '%';
    }

    // ============ CHART INSTANCES ============
    let charts = {
        readiness: null,
        performanceSubject: null,
        progressTracking: null,
        prePostComparison: null,
        sectionComparison: null
    };

    // ============ INITIALIZE CHARTS ============
    function initializeCharts() {
        // 1. Readiness Distribution
        const readinessCtx = document.getElementById('readinessChart').getContext('2d');
        charts.readiness = new Chart(readinessCtx, {
            type: 'doughnut',
            data: {
                labels: ['Advanced', 'Average', 'Below Average'],
                datasets: [{
                    data: [
                        analyticsData.readiness.advanced,
                        analyticsData.readiness.average,
                        analyticsData.readiness.belowAverage
                    ],
                    backgroundColor: ['#51CF66', '#FFB84D', '#FF6B6B'],
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, font: { size: 12, weight: '600' } }
                    }
                }
            }
        });

        // 2. Performance by Subject
        const subjectLabels = analyticsData.subjects.map(s => s.name);
        const subjectScores = analyticsData.subjects.map(s => s.avgScore);
        const perfSubjectCtx = document.getElementById('performanceSubjectChart').getContext('2d');
        charts.performanceSubject = new Chart(perfSubjectCtx, {
            type: 'bar',
            data: {
                labels: subjectLabels,
                datasets: [{
                    label: 'Average Score',
                    data: subjectScores,
                    backgroundColor: ['#0066CC', '#004D99', '#00AA66', '#008C52', '#FFB84D', '#FF9500'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } }
                },
                plugins: {
                    legend: { display: true, labels: { font: { size: 12, weight: '600' } } }
                }
            }
        });

        // 3. Progress Tracking
        const progressLabels = analyticsData.weeklyProgress.map(w => w.week);
        const progressData = analyticsData.weeklyProgress.map(w => w.avg);
        const progressCtx = document.getElementById('progressTrackingChart').getContext('2d');
        charts.progressTracking = new Chart(progressCtx, {
            type: 'line',
            data: {
                labels: progressLabels,
                datasets: [{
                    label: 'Average Score Trend',
                    data: progressData,
                    borderColor: '#0066CC',
                    backgroundColor: 'rgba(0, 102, 204, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0066CC',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } }
                },
                plugins: {
                    legend: { display: true, labels: { font: { size: 12, weight: '600' } } }
                }
            }
        });

        // 4. Pre-test vs Post-test
        const prePostLabels = analyticsData.prePost.map(p => p.subject);
        const preData = analyticsData.prePost.map(p => p.pre);
        const postData = analyticsData.prePost.map(p => p.post);
        const prePostCtx = document.getElementById('prePostComparisonChart').getContext('2d');
        charts.prePostComparison = new Chart(prePostCtx, {
            type: 'bar',
            data: {
                labels: prePostLabels,
                datasets: [
                    {
                        label: 'Pre-test Average',
                        data: preData,
                        backgroundColor: '#FFB84D',
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Post-test Average',
                        data: postData,
                        backgroundColor: '#51CF66',
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } }
                },
                plugins: {
                    legend: { display: true, labels: { font: { size: 12, weight: '600' } } }
                }
            }
        });

        // 5. Section Comparison
        const sectionLabels = analyticsData.sections.map(s => s.section);
        const sectionScores = analyticsData.sections.map(s => s.avgScore);
        const sectionCtx = document.getElementById('sectionComparisonChart').getContext('2d');
        charts.sectionComparison = new Chart(sectionCtx, {
            type: 'bar',
            data: {
                labels: sectionLabels,
                datasets: [{
                    label: 'Average Score',
                    data: sectionScores,
                    backgroundColor: ['#0066CC', '#004D99', '#00AA66', '#008C52', '#FFB84D', '#FF9500'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } }
                },
                plugins: {
                    legend: { display: true, labels: { font: { size: 12, weight: '600' } } }
                }
            }
        });
    }

    // ============ PAGE INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        // updateSummaryCards();
        initializeCharts();
    });

    // Refresh charts on resize
    window.addEventListener('resize', function() {
        setTimeout(() => {
            Object.values(charts).forEach(chart => {
                if (chart) chart.resize();
            });
        }, 100);
    });
</script>
@endpush