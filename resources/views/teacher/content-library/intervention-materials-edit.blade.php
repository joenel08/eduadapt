@extends('layouts.teacher-student')

@section('page_title', 'Edit Intervention Materials - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>
<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-book-reader"></i> Edit Intervention Materials
    </div>
    <div class="week-subheader">Update materials for this intervention</div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('teacher.content-library.update', [$grade, $term, $subject, $week, 'interventionMaterial', $item->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>File Name:</label>
                    <input type="text" name="file_name" class="form-control" value="{{ old('file_name', $item->file_name) }}" required>
                </div>
                <div class="form-group mt-3">
                    <div class="modal-buttons">
                        <button type="submit" class="btn-save">Update Material</button>
                        <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection