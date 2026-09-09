@extends('layouts.app')

@section('page_title', 'Student Profile')
@section('page', 'student-profile')

@section('content')
<div class="page-title">Student Profile</div>
<div class="panel" style="max-width:800px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3>{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</h3>
        <div>
            <a href="{{ route('admin.student.edit', $student->lrn) }}" class="primary-button">Edit</a>
            <a href="{{ route('admin.class-students', $student->classRecords->first()->class_id ?? '') }}" class="secondary-button">← Back</a>
        </div>
    </div>

    <div class="form-grid">
        <div><strong>LRN:</strong> {{ $student->lrn }}</div>
        <div><strong>Sex:</strong> {{ $student->sex ?? 'N/A' }}</div>
        <div><strong>Birth Date:</strong> {{ $student->birth_date ? date('M d, Y', strtotime($student->birth_date)) : 'N/A' }}</div>
        <div><strong>Mother Tongue:</strong> {{ $student->mother_tongue ?? 'N/A' }}</div>
        <div><strong>IP/Ethnic Group:</strong> {{ $student->ip_ethnic_group ?? 'N/A' }}</div>
        <div><strong>Religion:</strong> {{ $student->religion ?? 'N/A' }}</div>
        <div><strong>Address:</strong> {{ $student->address_house ?? '' }} {{ $student->address_barangay ?? '' }} {{ $student->address_municipality ?? '' }} {{ $student->address_province ?? '' }}</div>
        <div><strong>Father:</strong> {{ $student->father_name ?? 'N/A' }}</div>
        <div><strong>Mother:</strong> {{ $student->mother_maiden_name ?? 'N/A' }}</div>
        <div><strong>Guardian:</strong> {{ $student->guardian_name ?? 'N/A' }}</div>
        <div><strong>Guardian Relationship:</strong> {{ $student->guardian_relationship ?? 'N/A' }}</div>
        <div><strong>Contact Number:</strong> {{ $student->contact_number ?? 'N/A' }}</div>
        <div><strong>Learning Modality:</strong> {{ $student->learning_modality ?? 'N/A' }}</div>
        <div><strong>Remarks:</strong> {{ $student->remarks ?? 'N/A' }}</div>
    </div>

    <div style="margin-top:20px;">
        <h4>Enrolled Classes</h4>
        <ul>
            @foreach($student->classRecords as $record)
                <li>{{ $record->class->grade_level }} - {{ $record->class->section_name }} ({{ $record->class->schoolYear->year }})</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection