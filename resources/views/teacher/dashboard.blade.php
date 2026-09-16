@extends('layouts.teacher-student')

@section('page_title', 'Teacher Dashboard')
@section('page', 'dashboard')

@push('styles')
<style>
/* ===== OVERVIEW GRID ===== */
.overview-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
}

/* ===== CHARTS ===== */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
    align-items: stretch;
}

.chart-container {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);

    width: 100%;
    min-width: 0;
    box-sizing: border-box;

    /* KEY */
    display: flex;
    flex-direction: column;
    min-height: 450px;

    overflow: hidden;
}

.chart-title {
    flex-shrink: 0;

    font-size: 16px;
    font-weight: 700;
    color: #333;

    margin: 0 0 20px 0;

    display: flex;
    align-items: center;
    gap: 10px;

    line-height: 1.4;
}

.chart-title i {
    color: #0066CC;
    font-size: 18px;
    flex-shrink: 0;
}

/* Chart takes the remaining card space */
.chart-wrapper {
    position: relative;

    width: 100%;

    /* KEY */
    flex: 1;
    min-height: 350px;
}

/* Canvas fills chart wrapper */
.chart-wrapper canvas {
    display: block !important;

    width: 100% !important;
    height: 100% !important;

    max-width: 100%;
}


/* ================================
   TABLET
================================ */

@media (max-width: 1200px) {

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .chart-container {
        min-height: 450px;
    }

    .chart-wrapper {
        min-height: 350px;
    }
}


/* ================================
   MOBILE
================================ */

@media (max-width: 768px) {

    .overview-grid {
        grid-template-columns: 1fr;
    }

    .chart-container {
        padding: 18px;
        min-height: 430px;
    }

    .chart-title {
        font-size: 15px;
        margin-bottom: 15px;
    }

    .chart-wrapper {
        min-height: 350px;
    }
}


/* ================================
   SMALL MOBILE
================================ */

@media (max-width: 480px) {

    .chart-container {
        padding: 15px;
        min-height: 400px;
    }

    .chart-title {
        font-size: 14px;
        margin-bottom: 12px;
    }

    .chart-title i {
        font-size: 16px;
    }

    .chart-wrapper {
        min-height: 330px;
    }
}
</style>
@endpush

@section('content')
    <!-- OVERVIEW CARDS -->
    <div class="overview-grid">
        <div class="overview-card">
            <div class="card-icon blue"><i class="fas fa-book"></i></div>
            <div class="card-label">Total Classes</div>
            <div class="card-value">{{ $classes->count() }}</div>
        </div>
        <div class="overview-card">
            <div class="card-icon green"><i class="fas fa-users"></i></div>
            <div class="card-label">Total Students</div>
            <div class="card-value">{{ $totalStudents }}</div>
        </div>
        <!-- <div class="overview-card">
            <div class="card-icon orange"><i class="fas fa-chart-line"></i></div>
            <div class="card-label">Average Score</div>
            <div class="card-value">
                @php
                    $total = array_sum($readinessData['data']);
                    $count = count(array_filter($readinessData['data']));
                    $avg = $count > 0 ? round($total / $count) : 0;
                @endphp
                {{ $avg }}%
            </div>
        </div> -->
        <!-- <div class="overview-card">
            <div class="card-icon red"><i class="fas fa-hourglass-end"></i></div>
            <div class="card-label">Pending Requests</div>
            <div class="card-value">0</div>
        </div> -->
    </div>

    <!-- CHARTS SECTION -->
    <div class="charts-grid">
        <div class="chart-container">
            <h3 class="chart-title">
                <i class="fas fa-chart-pie"></i>
                Readiness Distribution
            </h3>
           <div class="chart-wrapper">
    <canvas id="readinessChart"></canvas>
</div>
        </div>
        <div class="chart-container">
            <h3 class="chart-title">
                <i class="fas fa-chart-bar"></i>
                Average Score by Class
            </h3>
          <div class="chart-wrapper">
    <canvas id="scoreChart"></canvas>
</div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

   // ==========================================
// READINESS DISTRIBUTION CHART
// ==========================================

const readinessCanvas = document.getElementById('readinessChart');

if (readinessCanvas) {

    new Chart(readinessCanvas, {

        type: 'doughnut',

        data: {
            labels: @json($readinessData['labels']),

            datasets: [{
                data: @json($readinessData['data']),

                backgroundColor: @json($readinessData['colors']),

                borderColor: @json($readinessData['borderColors']),

                borderWidth: 2,

                hoverOffset: 5
            }]
        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '60%',

            layout: {
                padding: {
                    top: 5,
                    bottom: 5,
                    left: 5,
                    right: 5
                }
            },

            plugins: {

                legend: {

                    display: true,

                    position: 'bottom',

                    labels: {

                        padding: 12,

                        usePointStyle: true,

                        pointStyle: 'circle',

                        boxWidth: 10,

                        boxHeight: 10,

                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },

                tooltip: {

                    callbacks: {

                        label: function (context) {

                            const label = context.label || '';
                            const value = context.parsed || 0;

                            return ' ' + label + ': ' + value;
                        }
                    }
                }
            }
        }
    });
}
   // ==========================================
// AVERAGE SCORE BY CLASS
// ==========================================

const scoreCanvas = document.getElementById('scoreChart');

if (scoreCanvas) {

    new Chart(scoreCanvas, {

        type: 'bar',

        data: {

            labels: @json($classScoreData['labels']),

            datasets: [{

                label: 'Average Score (%)',

                data: @json($classScoreData['data']),

                backgroundColor: @json($classScoreData['colors']),

                borderRadius: 6,

                borderSkipped: false,

                barPercentage: 0.65,

                categoryPercentage: 0.75
            }]
        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            layout: {

                padding: {
                    top: 10,
                    right: 10,
                    bottom: 10,
                    left: 5
                }
            },

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    callbacks: {

                        label: function (context) {

                            return ' Average Score: ' +
                                context.parsed.y + '%';
                        }
                    }
                }
            },

            scales: {

                x: {

                    grid: {
                        display: false
                    },

                    ticks: {

                        autoSkip: false,

                        maxRotation: 45,

                        minRotation: 0,

                        padding: 8,

                        font: {
                            size: 11
                        }
                    }
                },

                y: {

                    beginAtZero: true,

                    max: 100,

                    ticks: {

                        stepSize: 20,

                        padding: 8,

                        callback: function (value) {

                            return value + '%';
                        },

                        font: {
                            size: 11
                        }
                    },

                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
}

});
</script>
@endpush