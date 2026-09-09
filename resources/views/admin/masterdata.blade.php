@extends('layouts.app')

@section('page_title', 'Master Data Upload')
@section('page', 'masterdata')

@section('content')
<div class="page-title">Master List Upload</div>
<p class="page-subtitle">Upload student lists per class and assign teachers.</p>

@if(session('success'))
<div class="message-box" style="background:#def7ec;color:#0f6f5d;">{{ session('success') }}</div>
@endif

@if($activeSchoolYear)
<div class="message-box" style="background:#eef2ff;color:#1d4ed8;border:1px solid #c7d2fe;">
    <strong>Active School Year:</strong> {{ $activeSchoolYear->year }}
    <span style="margin-left:15px;font-size:14px;">You can change this in <a href="{{ route('admin.school-years') }}">School Years</a>.</span>
</div>
@else
<div class="message-box" style="background:#ffe4e6;color:#991b1b;border:1px solid #fecaca;">
    <strong>⚠️ No active school year set.</strong> Please <a href="{{ route('admin.school-years') }}">set one</a> before uploading student lists.
</div>
@endif

<div class="section-row">
    <!-- Student Master List Panel -->
    <!-- Student Master List Panel -->
    <div class="panel">
        <div class="panel-header">
            <div>
                <h3>Student Master List by Section</h3>
                <p>Organize student master data by Grade 5 and Grade 6 sections. Upload a file per section or add students manually.</p>
            </div>
            <button class="secondary-button" type="button" onclick="openAddClassModal()">Add Section</button>
        </div>

        <div class="section-tabs">
            <button class="section-tab active" data-grade="Grade 5" onclick="switchSectionGrade(event)">Grade 5</button>
            <button class="section-tab" data-grade="Grade 6" onclick="switchSectionGrade(event)">Grade 6</button>
        </div>

        <div class="section-manager">
            <div class="section-list-panel">
                <div class="section-list-title">Sections</div>
                <div id="sectionGrid" class="section-list">
                    @php
                    $allClasses = $classes->flatten();
                    @endphp
                    @forelse($allClasses as $class)
                    <div class="section-list-item" data-grade="{{ $class->grade_level }}">
                        <div>
                            <div class="section-label">{{ $class->grade_level }}</div>
                            <h4>{{ $class->section_name }}</h4>
                            <div class="section-list-meta">{{ $class->studentClassRecords->count() }} students</div>
                        </div>
                        <div class="section-list-actions">
                            <button class="action-button" onclick="openUploadModal({{ $class->id }}, '{{ $class->section_name }}')">Upload Students</button>
                            <button class="action-button" onclick="viewStudents({{ $class->id }})">View</button>
                            <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this class? All student enrollments will be removed.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-button danger">Delete</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="section-empty">
                        <p>No classes created yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Panel -->
    <div class="panel">
        <div class="panel-header">
            <div>
                <h3>Teacher Master List</h3>
                <p>Upload and manage teachers, then assign them to classes.</p>
            </div>
            <button class="secondary-button" type="button" onclick="document.getElementById('teacherUploadInput').click()">Upload Teachers</button>
            <input type="file" id="teacherUploadInput" accept=".xlsx,.xls" style="display:none;" onchange="uploadTeacherFile(this)">
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Full Name</th>
                        <th>Assigned Classes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                    <tr>
                        <td>{{ $teacher->employee_id }}</td>
                        <td>{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
                        <td>
                            @php
                            $assigned = $teacher->teacherClassAssignments->map(function($assignment) {
                            return $assignment->class->section_name . ' (' . $assignment->subject->name . ')';
                            })->implode(', ');
                            @endphp
                            {{ $assigned ?: 'None' }}
                        </td>
                        <td>
                            <button class="action-button" onclick="assignTeacher({{ $teacher->id }})">Assign</button>
                            <form action="{{ route('admin.master-data.delete-teacher', $teacher->employee_id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-button danger" onclick="return confirm('Remove teacher?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;color:#64748b;">No teachers uploaded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Add Class Modal -->
<div class="modal-backdrop" id="addClassModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Class</h3>
            <button class="modal-close" onclick="closeModal('addClassModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addClassForm" onsubmit="submitAddClass(event)">
                @csrf
                <div class="input-group">
                    <label>Grade Level</label>
                    <select name="grade_level" required>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Section Name</label>
                    <input type="text" name="section_name" placeholder="e.g. DIAMOND" required>
                </div>
                <input type="hidden" name="school_year_id" value="{{ $activeSchoolYear->id ?? '' }}">
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" class="secondary-button" onclick="closeModal('addClassModal')">Cancel</button>
                    <button type="submit" class="primary-button">Create Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Students Modal -->
<div class="modal-backdrop" id="uploadStudentModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Upload Students for <span id="uploadClassName"></span></h3>
            <button class="modal-close" onclick="closeModal('uploadStudentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="uploadStudentForm" action="{{ route('admin.master-data.upload-student') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="class_id" id="uploadClassId">
                <div class="input-group">
                    <label>Excel File (.xlsx, .xls)</label>
                    <input type="file" name="file" accept=".xlsx,.xls" required>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" class="secondary-button" onclick="closeModal('uploadStudentModal')">Cancel</button>
                    <button type="submit" class="primary-button">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Teacher Assignment Modal -->
<div class="modal-backdrop" id="assignTeacherModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Assign Teacher to Class</h3>
            <button class="modal-close" onclick="closeModal('assignTeacherModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="assignTeacherForm" action="{{ route('admin.teacher-assign') }}" method="POST">
                @csrf
                <input type="hidden" name="teacher_profile_id" id="assignTeacherId">
                <div class="input-group">
                    <label>Select Class</label>
                    <select name="class_id" id="assignClassId" required onchange="updateSubjects()">
                        @foreach($classes->flatten() as $class)
                        <option value="{{ $class->id }}" data-grade="{{ $class->grade_level }}">{{ $class->grade_level }} - {{ $class->section_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <label>Select Subject</label>
                    <select name="subject_id" id="assignSubjectId" required>
                        <!-- populated dynamically -->
                    </select>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" class="secondary-button" onclick="closeModal('assignTeacherModal')">Cancel</button>
                    <button type="submit" class="primary-button">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Switch sections by grade (for the new section manager)
    function switchSectionGrade(e) {
        const grade = e.target.dataset.grade;
        const items = document.querySelectorAll('#sectionGrid .section-list-item');
        items.forEach(item => {
            item.style.display = (item.dataset.grade === grade) ? '' : 'none';
        });
        // Update active tab
        document.querySelectorAll('.section-tab').forEach(tab => tab.classList.remove('active'));
        e.target.classList.add('active');
    }

    // Modal functions
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openAddClassModal() {
        openModal('addClassModal');
    }

    function submitAddClass(e) {
        e.preventDefault();
        const form = document.getElementById('addClassForm');
        const formData = new FormData(form);

        fetch('{{ route("admin.classes.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json().then(data => ({
                status: response.status,
                data
            })))
            .then(({
                status,
                data
            }) => {
                if (status >= 200 && status < 300 && data.success) {
                    closeModal('addClassModal');
                    location.reload();
                } else {
                    let errorMsg = data.message || 'Unknown error.';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join('\n');
                        errorMsg += '\n\n' + errorList;
                    }
                    alert('Error creating class:\n' + errorMsg);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Network error. Please check your connection.');
            });
    }

    function uploadTeacherFile(input) {
        const file = input.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('file', file);
        fetch('{{ route("admin.master-data.upload-teacher") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Upload failed.');
                }
            })
            .catch(() => alert('Error uploading.'));
        input.value = '';
    }

    function viewStudents(classId) {
        window.location.href = '{{ route("admin.class-students", "") }}' + '/' + classId;
    }

    function openUploadModal(classId, className) {
        document.getElementById('uploadClassId').value = classId;
        document.getElementById('uploadClassName').textContent = className;
        openModal('uploadStudentModal');
    }


    function assignTeacher(teacherId) {
        document.getElementById('assignTeacherId').value = teacherId;
        openModal('assignTeacherModal');
    }

    function viewStudents(classId) {
        window.location.href = '{{ route("admin.class-students","")}}/' + classId;
    }

    // Initialize – show the active grade on page load
    document.addEventListener('DOMContentLoaded', function() {
        const activeTab = document.querySelector('.section-tab.active');
        if (activeTab) {
            activeTab.click(); // triggers switchSectionGrade
        } else {
            // Fallback: click Grade 5 tab
            const grade5Tab = document.querySelector('.section-tab[data-grade="Grade 5"]');
            if (grade5Tab) grade5Tab.click();
        }
    });

    function updateSubjects() {
        const classId = document.getElementById('assignClassId').value;
        const selectedOption = document.querySelector('#assignClassId option:checked');
        const grade = selectedOption ? selectedOption.dataset.grade : '';
        const subjectSelect = document.getElementById('assignSubjectId');
        // Clear options
        subjectSelect.innerHTML = '';
        // Fetch subjects for this grade from a pre-populated JS object
        const subjectsByGrade = @json($subjectsByGrade); // we need to pass this as JSON
        const subjects = subjectsByGrade[grade] || [];
        subjects.forEach(subj => {
            const opt = document.createElement('option');
            opt.value = subj.id;
            opt.textContent = subj.name;
            subjectSelect.appendChild(opt);
        });
    }

    // On modal open, call updateSubjects
    function assignTeacher(teacherId) {
        document.getElementById('assignTeacherId').value = teacherId;
        openModal('assignTeacherModal');
        updateSubjects(); // populate subjects for the default class
    }
</script>
@endpush