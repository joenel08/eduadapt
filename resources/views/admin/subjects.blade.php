@extends('layouts.app')

@section('page_title', 'Manage Subjects')
@section('page', 'subjects')

@section('content')
<div class="page-title">Subjects</div>
<p class="page-subtitle">Manage subjects per grade level.</p>

@if(session('success'))
    <div class="message-box" style="background:#def7ec;color:#0f6f5d;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="message-box" style="background:#ffe4e6;color:#991b1b;">{{ session('error') }}</div>
@endif
<div class="panel" style="max-width:900px;">
    <h3>Add Subject</h3>

    <form method="POST" action="{{ route('admin.subjects.store') }}">
        @csrf

        <div style="display:grid; grid-template-columns:180px 1fr 130px; column-gap:15px; align-items:end;">

            <div>
                <label style="display:block; margin-bottom:6px;">Grade Level</label>
                <select name="grade_level" required style="width:100%; height:50px;">
                    <option value="Grade 5">Grade 5</option>
                    <option value="Grade 6">Grade 6</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px;">Subject Name</label>
                <input type="text" name="name" placeholder="e.g. Mathematics" required style="width:100%; height:50px;">
            </div>

            <button type="submit" class="primary-button" style="width:100%; height:50px;">
                Add Subject
            </button>

        </div>
    </form>
</div>
<br>
<div class="table-card">
    <table>
        <thead><tr><th>Grade Level</th><th>Subject</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($subjects as $subject)
            <tr>
                <td>{{ $subject->grade_level }}</td>
                <td>{{ $subject->name }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Delete subject?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-button danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#64748b;">No subjects defined yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection