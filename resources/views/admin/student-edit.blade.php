@extends('layouts.app')

@section('page_title', 'Edit Student')
@section('page', 'student-edit')

@section('content')
<div class="page-title">Edit Student</div>

<div class="panel" style="max-width:800px;">
    <form method="POST" action="{{ route('admin.student.update', $student->lrn) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="input-group">
                <label>LRN</label>
                <input type="text" value="{{ $student->lrn }}" disabled>
            </div>
            <div class="input-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
            </div>
            <div class="input-group">
                <label>Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}">
            </div>
            <div class="input-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
            </div>
            <div class="input-group">
                <label>Suffix</label>
                <input type="text" name="suffix_name" value="{{ old('suffix_name', $student->suffix_name) }}">
            </div>
            <div class="input-group">
                <label>Sex</label>
                <select name="sex">
                    <option value="">Select</option>
                    <option value="M" {{ old('sex', $student->sex) == 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ old('sex', $student->sex) == 'F' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="input-group">
                <label>Birth Date</label>
                <input type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date) }}">
            </div>
            <div class="input-group">
                <label>Mother Tongue</label>
                <input type="text" name="mother_tongue" value="{{ old('mother_tongue', $student->mother_tongue) }}">
            </div>
            <div class="input-group">
                <label>IP/Ethnic Group</label>
                <input type="text" name="ip_ethnic_group" value="{{ old('ip_ethnic_group', $student->ip_ethnic_group) }}">
            </div>
            <div class="input-group">
                <label>Religion</label>
                <input type="text" name="religion" value="{{ old('religion', $student->religion) }}">
            </div>
            <div class="input-group">
                <label>Address House</label>
                <input type="text" name="address_house" value="{{ old('address_house', $student->address_house) }}">
            </div>
            <div class="input-group">
                <label>Barangay</label>
                <input type="text" name="address_barangay" value="{{ old('address_barangay', $student->address_barangay) }}">
            </div>
            <div class="input-group">
                <label>Municipality/City</label>
                <input type="text" name="address_municipality" value="{{ old('address_municipality', $student->address_municipality) }}">
            </div>
            <div class="input-group">
                <label>Province</label>
                <input type="text" name="address_province" value="{{ old('address_province', $student->address_province) }}">
            </div>
            <div class="input-group">
                <label>Father's Name</label>
                <input type="text" name="father_name" value="{{ old('father_name', $student->father_name) }}">
            </div>
            <div class="input-group">
                <label>Mother's Maiden Name</label>
                <input type="text" name="mother_maiden_name" value="{{ old('mother_maiden_name', $student->mother_maiden_name) }}">
            </div>
            <div class="input-group">
                <label>Guardian Name</label>
                <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}">
            </div>
            <div class="input-group">
                <label>Guardian Relationship</label>
                <input type="text" name="guardian_relationship" value="{{ old('guardian_relationship', $student->guardian_relationship) }}">
            </div>
            <div class="input-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $student->contact_number) }}">
            </div>
            <div class="input-group">
                <label>Learning Modality</label>
                <input type="text" name="learning_modality" value="{{ old('learning_modality', $student->learning_modality) }}">
            </div>
            <div class="input-group" style="grid-column: span 2;">
                <label>Remarks</label>
                <textarea name="remarks" rows="3">{{ old('remarks', $student->remarks) }}</textarea>
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:20px;">
            <button type="submit" class="primary-button">Update Profile</button>
            <a href="{{ route('admin.student.show', $student->lrn) }}" class="secondary-button">Cancel</a>
        </div>
    </form>
</div>
@endsection