@extends('layouts.app')

@section('page_title', 'Manage School Years')
@section('page', 'school-years')

@section('content')
<div class="page-title">School Years</div>
<p class="page-subtitle">Manage academic years and set the active one.</p>

@if(session('success'))
    <div class="message-box" style="background:#def7ec;color:#0f6f5d;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="message-box" style="background:#ffe4e6;color:#991b1b;">{{ session('error') }}</div>
@endif

<div class="panel" style="max-width:600px;">
    <h3>Add New School Year</h3>
    <form method="POST" action="{{ route('admin.school-years.store') }}">
        @csrf
        <div class="input-group">
            <label>Year (e.g., 2026-2027)</label>
            <input type="text" name="year" placeholder="2026-2027" required>
        </div>
        <button type="submit" class="primary-button">Add School Year</button>
    </form>
</div>
<br>
<div class="table-card">
    <table>
        <thead><tr><th>School Year</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($schoolYears as $sy)
            <tr>
                <td>{{ $sy->year }}</td>
                <td>
                    @if($sy->is_active)
                        <span class="status-badge approved">Active</span>
                    @else
                        <span class="status-badge pending">Inactive</span>
                    @endif
                </td>
                <td>
                    @if(!$sy->is_active)
                        <form method="POST" action="{{ route('admin.school-years.set-active', $sy) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="action-button success">Set Active</button>
                        </form>
                        <form method="POST" action="{{ route('admin.school-years.destroy', $sy) }}" style="display:inline;" onsubmit="return confirm('Delete this school year?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-button danger">Delete</button>
                        </form>
                    @else
                        <span style="color:#64748b;font-size:0.875rem;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#64748b;">No school years added yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection