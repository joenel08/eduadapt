@extends('layouts.app')

@section('page_title', 'Report Per School Year')
@section('page', 'report-per-school-year')

@section('content')

<style>
    .rp-header { margin-bottom: 24px; }
    .rp-header h1 {
        font-size: 24px; font-weight: 700; color: #1a202c;
        margin: 0; display: flex; align-items: center; gap: 10px;
    }
    .rp-header h1 i { color: #0066CC; }
    .rp-header p { margin: 4px 0 0; color: #718096; font-size: 14px; }

    .rp-filter {
        background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 18px 22px; display: flex; flex-wrap: wrap;
        align-items: center; gap: 16px; margin-bottom: 26px;
    }
    .rp-filter label { font-weight: 600; color: #2d3748; font-size: 14px; }
    .rp-filter select {
        padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px;
        font-size: 14px; font-family: inherit; color: #1a202c; background: #fff;
        min-width: 220px; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .rp-filter select:focus {
        border-color: #0066CC; box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.12);
    }
    .active-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; background: #e6f7ef; color: #008C52;
        border-radius: 999px; font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
    }

    .rp-empty {
        background: #fff; border-radius: 14px; padding: 60px 30px;
        text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .rp-empty i { font-size: 42px; color: #cbd5e0; margin-bottom: 12px; display: block; }
    .rp-empty h3 { margin: 0 0 6px; color: #2d3748; font-size: 18px; }
    .rp-empty p { margin: 0; color: #718096; font-size: 14px; }

    .rp-grade {
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 22px; overflow: hidden;
    }
    .rp-grade-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 24px;
        background: linear-gradient(135deg, #0066CC 0%, #004D99 100%);
        color: #fff; cursor: pointer; user-select: none;
    }
    .rp-grade-header h2 {
        margin: 0; font-size: 18px; font-weight: 700;
        display: flex; align-items: center; gap: 10px;
    }
    .rp-grade-meta {
        display: flex; align-items: center; gap: 18px;
        font-size: 13px; opacity: 0.95;
    }
    .rp-grade-meta span { display: inline-flex; align-items: center; gap: 6px; }
    .rp-grade-toggle { font-size: 14px; transition: transform 0.3s ease; }
    .rp-grade.collapsed .rp-grade-toggle { transform: rotate(-90deg); }
    .rp-grade.collapsed .rp-grade-body { display: none; }
    .rp-grade-body { padding: 22px 24px; }

    .rp-class {
        border: 1px solid #e2e8f0; border-radius: 12px;
        margin-bottom: 18px; overflow: hidden;
    }
    .rp-class:last-child { margin-bottom: 0; }
    .rp-class-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; background: #f7fafc;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer; user-select: none;
    }
    .rp-class-title {
        display: flex; align-items: center; gap: 12px;
        font-weight: 700; color: #1a202c; font-size: 15px;
    }
    .rp-class-title i { color: #0066CC; }
    .rp-class-toggle { color: #a0aec0; font-size: 14px; transition: transform 0.3s ease; }
    .rp-class.collapsed .rp-class-toggle { transform: rotate(-90deg); }
    .rp-class.collapsed .rp-class-body { display: none; }
    .rp-class-body { padding: 0; }

    .rp-subject { border-top: 1px solid #edf2f7; }
    .rp-subject:first-child { border-top: none; }
    .rp-subject-header {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 14px; padding: 14px 22px;
        background: #fbfdff;
        cursor: pointer; user-select: none;
        border-bottom: 1px solid #edf2f7;
    }
    .rp-subject-title {
        display: flex; align-items: center; gap: 10px;
        font-weight: 700; color: #1a202c; font-size: 14px;
    }
    .rp-subject-title i { color: #00AA66; }
    .rp-subject-actions {
        display: flex; align-items: center; gap: 10px;
    }
    .rp-teacher-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; background: #e6f2ff; color: #004D99;
        border-radius: 999px; font-size: 12px; font-weight: 600;
    }
    .rp-teacher-chip i { font-size: 11px; }
    .rp-subject-toggle { color: #a0aec0; font-size: 13px; transition: transform 0.3s ease; }
    .rp-subject.collapsed .rp-subject-toggle { transform: rotate(-90deg); }
    .rp-subject.collapsed .rp-subject-body { display: none; }
    .rp-subject-body { padding: 0; }

    /* Per-subject download button */
    .rp-subject-download {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #00AA66 0%, #008C52 100%);
        color: #fff; text-decoration: none;
        border-radius: 8px; font-size: 12px; font-weight: 700;
        box-shadow: 0 2px 8px rgba(0, 170, 102, 0.2);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        white-space: nowrap;
    }
    .rp-subject-download:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(0, 170, 102, 0.35);
        color: #fff;
    }
    .rp-subject-download i { font-size: 11px; }

    .rp-table-wrap { overflow-x: auto; }
    .rp-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .rp-table thead { background: #fff; border-bottom: 2px solid #e2e8f0; }
    .rp-table th {
        padding: 12px 14px; text-align: left;
        font-size: 11px; font-weight: 700; color: #718096;
        text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
    }
    .rp-table td {
        padding: 12px 14px; border-bottom: 1px solid #f0f4f8;
        font-size: 13px; color: #4a5568; vertical-align: middle;
    }
    .rp-table tbody tr:hover { background: #f9fbfd; }
    .rp-table tbody tr:last-child td { border-bottom: none; }
    .rp-student-name { font-weight: 600; color: #1a202c; }
    .rp-student-lrn { display: block; font-size: 11px; color: #a0aec0; margin-top: 2px; }

    .rp-progress {
        display: flex; flex-direction: column; gap: 5px; min-width: 120px;
    }
    .rp-progress-label {
        display: flex; justify-content: space-between;
        font-size: 11px; font-weight: 600; color: #4a5568;
    }
    .rp-progress-bar {
        height: 6px; background: #edf2f7; border-radius: 3px; overflow: hidden;
    }
    .rp-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #0066CC, #00AA66);
        border-radius: 3px; transition: width 0.4s ease;
    }

    .rp-score {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 52px; padding: 5px 10px; border-radius: 8px;
        font-weight: 700; font-size: 12px;
    }
    .rp-score.good  { background: #e0ffe0; color: #00AA66; }
    .rp-score.mid   { background: #fff3cd; color: #FF8800; }
    .rp-score.low   { background: #ffe0e0; color: #e53e3e; }
    .rp-score.empty { background: #f0f0f0; color: #a0aec0; font-weight: 500; }

    .rp-empty-row {
        padding: 20px; text-align: center; color: #a0aec0; font-size: 13px;
    }

    @media (max-width: 640px) {
        .rp-header h1 { font-size: 20px; }
        .rp-grade-header { padding: 14px 16px; }
        .rp-grade-body { padding: 16px; }
        .rp-class-header { padding: 12px 14px; }
        .rp-subject-header { padding: 12px 16px; }
    }
</style>

<div class="rp-header">
    <h1><i class="fas fa-chart-column"></i> Report Per School Year</h1>
    <p>Drill down: school year → grade level → class → subject → students.</p>
</div>

{{-- FILTER --}}
<div class="rp-filter">
    <form method="GET" action="{{ route('admin.report-per-school-year') }}"
          style="display:flex; flex-wrap:wrap; gap:16px; align-items:center; margin:0;">
        <label for="school_year_id"><i class="fas fa-calendar-days"></i> School Year:</label>
        <select name="school_year_id" id="school_year_id" onchange="this.form.submit()">
            @foreach($schoolYears as $sy)
                <option value="{{ $sy->id }}"
                    {{ ($selectedYear && $selectedYear->id === $sy->id) ? 'selected' : '' }}>
                    {{ $sy->year }}
                </option>
            @endforeach
        </select>
        @if($selectedYear && $selectedYear->is_active)
            <span class="active-pill"><i class="fas fa-circle-check"></i> Active</span>
        @endif
    </form>
</div>

{{-- CONTENT --}}
@if(empty($gradeLevels))
    <div class="rp-empty">
        <i class="fas fa-folder-open"></i>
        <h3>No data for this school year</h3>
        <p>There are no classes or students enrolled under {{ $selectedYear->year ?? 'this school year' }}.</p>
    </div>
@else
    @foreach($gradeLevels as $gi => $grade)
        @php
            $totalClasses = count($grade['classes']);
            $uniqueStudents = collect($grade['classes'])
                ->flatMap(fn($c) => collect($c['subjects'])->flatMap(fn($s) => collect($s['students'])->pluck('id')))
                ->unique()
                ->count();
            $totalSubjects = collect($grade['classes'])->sum(fn($c) => count($c['subjects']));
        @endphp

        <div class="rp-grade" id="grade-{{ $gi }}">
            <div class="rp-grade-header" onclick="toggleGrade({{ $gi }})">
                <h2><i class="fas fa-layer-group"></i> Grade {{ $grade['grade_level'] }}</h2>
                <div class="rp-grade-meta">
                    <span><i class="fas fa-school"></i> {{ $totalClasses }} {{ Str::plural('Class', $totalClasses) }}</span>
                    <span><i class="fas fa-book"></i> {{ $totalSubjects }} {{ Str::plural('Subject', $totalSubjects) }}</span>
                    <span><i class="fas fa-users"></i> {{ $uniqueStudents }} {{ Str::plural('Student', $uniqueStudents) }}</span>
                    <i class="fas fa-chevron-down rp-grade-toggle"></i>
                </div>
            </div>

            <div class="rp-grade-body">
                @foreach($grade['classes'] as $ci => $class)
                    <div class="rp-class" id="grade-{{ $gi }}-class-{{ $ci }}">
                        <div class="rp-class-header" onclick="toggleClass({{ $gi }}, {{ $ci }})">
                            <div class="rp-class-title">
                                <i class="fas fa-chalkboard-user"></i>
                                {{ $class['section_name'] }}
                                <span style="color:#a0aec0;font-weight:500;font-size:13px;">
                                    ({{ count($class['subjects']) }} {{ Str::plural('subject', count($class['subjects'])) }})
                                </span>
                            </div>
                            <i class="fas fa-chevron-down rp-class-toggle"></i>
                        </div>

                        <div class="rp-class-body">
                            @forelse($class['subjects'] as $si => $subject)
                                <div class="rp-subject" id="grade-{{ $gi }}-class-{{ $ci }}-subj-{{ $si }}">
                                    <div class="rp-subject-header"
                                         onclick="toggleSubject({{ $gi }}, {{ $ci }}, {{ $si }})">
                                        <div class="rp-subject-title">
                                            <i class="fas fa-book-open"></i>
                                            {{ $subject['subject'] }}
                                            <span style="color:#a0aec0;font-weight:500;font-size:13px;">
                                                ({{ $subject['total_students'] }} {{ Str::plural('student', $subject['total_students']) }})
                                            </span>
                                        </div>

                                        <div class="rp-subject-actions">
                                            <span class="rp-teacher-chip">
                                                <i class="fas fa-user-tie"></i>
                                                {{ $subject['teacher'] }}
                                            </span>

                                            {{-- Download button for this class + subject --}}
                                            <a href="{{ route('admin.report-per-school-year.subject-download', [
                                                    'school_year_id' => $selectedYear->id,
                                                    'class_id'       => $class['id'],
                                                    'subject_id'     => $subject['subject_id'],
                                                ]) }}"
                                               class="rp-subject-download"
                                               onclick="event.stopPropagation();"
                                               title="Download this class and subject as Excel">
                                                <i class="fas fa-file-excel"></i> Excel
                                            </a>

                                            <i class="fas fa-chevron-down rp-subject-toggle"></i>
                                        </div>
                                    </div>

                                    <div class="rp-subject-body">
                                        @if(empty($subject['students']))
                                            <div class="rp-empty-row">No students enrolled in this subject.</div>
                                        @else
                                            <div class="rp-table-wrap">
                                                <table class="rp-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Student</th>
                                                            <th>Materials</th>
                                                            <th>Pre-Test</th>
                                                            <th>Post-Test</th>
                                                            <th>Interventions</th>
                                                            <th>Mini Quiz</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subject['students'] as $student)
                                                            <tr>
                                                                <td>
                                                                    <div class="rp-student-name">{{ $student['name'] }}</div>
                                                                    <span class="rp-student-lrn">LRN: {{ $student['lrn'] }}</span>
                                                                </td>

                                                                <td>
                                                                    <div class="rp-progress">
                                                                        <div class="rp-progress-label">
                                                                            <span>{{ $student['materials']['completed'] }}/{{ $student['materials']['total'] }}</span>
                                                                            <span>{{ $student['materials']['percent'] }}%</span>
                                                                        </div>
                                                                        <div class="rp-progress-bar">
                                                                            <div class="rp-progress-fill" style="width: {{ $student['materials']['percent'] }}%;"></div>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <td>
                                                                    @if($student['pre']['score'] !== null)
                                                                        @php
                                                                            $s = $student['pre']['score'];
                                                                            $cls = $s >= 80 ? 'good' : ($s >= 60 ? 'mid' : 'low');
                                                                        @endphp
                                                                        <span class="rp-score {{ $cls }}">{{ $s }}%</span>
                                                                    @else
                                                                        <span class="rp-score empty">—</span>
                                                                    @endif
                                                                </td>

                                                                <td>
                                                                    @if($student['post']['score'] !== null)
                                                                        @php
                                                                            $s = $student['post']['score'];
                                                                            $cls = $s >= 80 ? 'good' : ($s >= 60 ? 'mid' : 'low');
                                                                        @endphp
                                                                        <span class="rp-score {{ $cls }}">{{ $s }}%</span>
                                                                    @else
                                                                        <span class="rp-score empty">—</span>
                                                                    @endif
                                                                </td>

                                                                <td>
                                                                    <div class="rp-progress">
                                                                        <div class="rp-progress-label">
                                                                            <span>{{ $student['interventions']['completed'] }}/{{ $student['interventions']['total'] }}</span>
                                                                            <span>{{ $student['interventions']['percent'] }}%</span>
                                                                        </div>
                                                                        <div class="rp-progress-bar">
                                                                            <div class="rp-progress-fill" style="width: {{ $student['interventions']['percent'] }}%;"></div>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <td>
                                                                    @if($student['quiz']['score'] !== null)
                                                                        @php
                                                                            $s = $student['quiz']['score'];
                                                                            $cls = $s >= 80 ? 'good' : ($s >= 60 ? 'mid' : 'low');
                                                                        @endphp
                                                                        <span class="rp-score {{ $cls }}">{{ $s }}%</span>
                                                                    @else
                                                                        <span class="rp-score empty">—</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="rp-empty-row">No subjects assigned to this class.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif

@endsection

@push('scripts')
<script>
    function toggleGrade(i) {
        document.getElementById('grade-' + i)?.classList.toggle('collapsed');
    }
    function toggleClass(g, c) {
        document.getElementById('grade-' + g + '-class-' + c)?.classList.toggle('collapsed');
    }
    function toggleSubject(g, c, s) {
        document.getElementById('grade-' + g + '-class-' + c + '-subj-' + s)?.classList.toggle('collapsed');
    }
</script>
@endpush