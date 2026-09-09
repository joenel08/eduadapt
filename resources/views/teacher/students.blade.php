@extends('layouts.teacher-student')

@section('page_title', 'Students in ' . $class->grade_level . ' - ' . $class->section_name)
@section('page', 'classes')

@section('content')
<div class="page-title">{{ $class->grade_level }} - {{ $class->section_name }}</div>
<p class="page-subtitle">List of enrolled students for {{ $class->schoolYear->year }}.</p>

<div class="table-card">
    <table>
        <thead><tr><th>LRN</th><th>Full Name</th></tr></thead>
        <tbody>
            @forelse($students as $record)
            <tr>
                <td>{{ $record->studentProfile->lrn }}</td>
                <td>{{ $record->studentProfile->first_name }} {{ $record->studentProfile->last_name }}</td>
            </tr>
            @empty
            <tr><td colspan="2" style="text-align:center;color:#64748b;">No students enrolled.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:20px;">
    <a href="{{ route('teacher.dashboard') }}" class="secondary-button">← Back to Dashboard</a>
</div>
@endsection