@extends('layouts.app')

@section('page_title', 'Students in ' . $class->grade_level . ' - ' . $class->section_name)
@section('page', 'class-students')

@section('content')
<div class="page-title">Students in {{ $class->grade_level }} - {{ $class->section_name }}</div>
<p class="page-subtitle">{{ $class->schoolYear->year }}</p>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>LRN</th>
                <th>Full Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($studentRecords as $record)
            <tr>
                <td>{{ $record->studentProfile->lrn }}</td>
                <td>{{ $record->studentProfile->first_name }} {{ $record->studentProfile->last_name }}</td>
                <td>
                    <a href="{{ route('admin.student.show', $record->studentProfile->lrn) }}" class="action-button">View</a>
                    <a href="{{ route('admin.student.edit', $record->studentProfile->lrn) }}" class="action-button">Edit</a>
                    <form action="{{ route('admin.master-data.delete-student', $record->studentProfile->lrn) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-button danger" onclick="return confirm('Remove this student from this class?')">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#64748b;">No students enrolled in this class.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div style="margin-top:20px;">
    {{ $studentRecords->links() }}
</div>

<div style="margin-top:20px;">
    <a href="{{ route('admin.master-data') }}" class="secondary-button">← Back to Master Data</a>
</div>
@endsection