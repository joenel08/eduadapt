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
                            <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" style="display:inline;"
                                data-confirm="Delete this class? All student enrollments will be removed."
                                data-confirm-title="Delete Section"
                                data-confirm-ok="Yes, Delete">
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
            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.master-data.teacher-template') }}" class="btn-success">
                    <i class="fas fa-download"></i> Template
                </a>
                <button class="secondary-button" type="button" onclick="openTeacherUploadModal()">
                    <i class="fas fa-upload"></i> Upload Teachers
                </button>
            </div>
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
                            $grouped = $teacher->teacherClassAssignments
                            ->groupBy(fn($a) => $a->class_id);
                            @endphp

                            @forelse($grouped as $classId => $rows)
                            @php
                            $class = $rows->first()->class;
                            @endphp
                            <div style="margin-bottom:6px;">
                                <strong>{{ $class->grade_level ?? '' }} - {{ $class->section_name ?? '' }}:</strong>
                                @foreach($rows as $row)
                                <span style="display:inline-block;background:#eef2ff;color:#1d4ed8;font-size:11px;font-weight:600;padding:2px 8px;border-radius:12px;margin:2px 4px 2px 0;">
                                    {{ $row->subject->name ?? 'N/A' }}
                                </span>
                                @endforeach
                            </div>
                            @empty
                            <span style="color:#999;">None</span>
                            @endforelse
                        </td>
                        <td>
                            <button class="action-button" onclick="assignTeacher({{ $teacher->id }})">Assign</button>
                            <form action="{{ route('admin.master-data.delete-teacher', $teacher->employee_id) }}" method="POST" style="display:inline;"
                                data-confirm="Remove this teacher? Their account will be deleted."
                                data-confirm-title="Remove Teacher"
                                data-confirm-ok="Yes, Remove">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-button danger">Delete</button>
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
            <button type="button" class="modal-close" onclick="closeAppModal('addClassModal')">&times;</button>
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
                    <button type="button" class="secondary-button" onclick="closeAppModal('addClassModal')">Cancel</button>
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
            <button type="button" class="modal-close" onclick="closeAppModal('uploadStudentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="uploadStudentForm" onsubmit="submitUploadStudents(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="class_id" id="uploadClassId">
                <div class="input-group">
                    <label>Excel File (.xlsx, .xls)</label>
                    <input type="file" name="file" id="studentFileInput" accept=".xlsx,.xls" required
                        onchange="showFileName(this, 'studentFileName')">
                    <div id="studentFileName" style="font-size:12px;color:#666;margin-top:6px;"></div>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" class="secondary-button" onclick="closeAppModal('uploadStudentModal')">Cancel</button>
                    <button type="submit" class="primary-button" id="uploadStudentBtn">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Teachers Modal -->
<div class="modal-backdrop" id="uploadTeacherModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Upload Teachers</h3>
            <button type="button" class="modal-close" onclick="closeAppModal('uploadTeacherModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="uploadTeacherForm" onsubmit="submitUploadTeachers(event)" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <label>Excel File (.xlsx, .xls)</label>
                    <input type="file" name="file" id="teacherFileInput" accept=".xlsx,.xls" required
                        onchange="showFileName(this, 'teacherFileName')">
                    <div id="teacherFileName" style="font-size:12px;color:#666;margin-top:6px;"></div>
                </div>

                <div class="message-box" style="background:#eef2ff;color:#1d4ed8;border:1px solid #c7d2fe;font-size:13px;">
                    <i class="fas fa-info-circle"></i>
                    Please make sure your file follows the <strong>Template</strong> format.
                    Each new teacher will be given a <strong>default password</strong> (their Employee ID).
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" class="secondary-button" onclick="closeAppModal('uploadTeacherModal')">Cancel</button>
                    <button type="submit" class="primary-button" id="uploadTeacherBtn">
                        <i class="fas fa-upload"></i> Save Teachers
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Teacher Assignment Modal -->
<!-- Teacher Assignment Modal -->
<div class="modal-backdrop" id="assignTeacherModal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-chalkboard-teacher"></i> Assign Teacher
            </h3>
            <button type="button" class="modal-close" onclick="closeAppModal('assignTeacherModal')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="assignTeacherId">

            {{-- Existing assignments --}}
            <div class="input-group">
                <label>Current Assignments</label>
                <div id="existingAssignments" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;min-height:60px;background:#f9fafb;">
                    <div style="color:#999;font-size:13px;">Loading…</div>
                </div>
            </div>

            <hr style="margin:16px 0;">

            {{-- Add new assignment --}}
            <div class="input-group">
                <label>Add New Assignment</label>
            </div>

            <div class="input-group">
                <label>Class</label>
                <select id="assignClassId" required onchange="updateSubjects()">
                    @foreach($classes->flatten() as $class)
                    <option value="{{ $class->id }}" data-grade="{{ $class->grade_level }}">
                        {{ $class->grade_level }} - {{ $class->section_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="input-group">
                <label>Subject</label>
                <select id="assignSubjectId" required></select>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="secondary-button" onclick="closeAppModal('assignTeacherModal')">Close</button>
                <button type="button" class="primary-button" onclick="addAssignment()">
                    <i class="fas fa-plus"></i> Add Assignment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
    // ---------- Section tabs ----------
    function switchSectionGrade(e) {
        const grade = e.currentTarget.dataset.grade;
        document.querySelectorAll('#sectionGrid .section-list-item').forEach(item => {
            item.style.display = (item.dataset.grade === grade) ? '' : 'none';
        });
        document.querySelectorAll('.section-tab').forEach(tab => tab.classList.remove('active'));
        e.currentTarget.classList.add('active');
    }

    // ---------- Modal helpers ----------
    const modalFormMap = {
        addClassModal: 'addClassForm',
        uploadStudentModal: 'uploadStudentForm',
        uploadTeacherModal: 'uploadTeacherForm',
        assignTeacherModal: 'assignTeacherForm',
    };

    function openAppModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('active');
    }

    function closeAppModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('active');

        // Reset the form and clear any "selected file" text so the modal reopens fresh
        const formId = modalFormMap[id];
        if (formId) {
            const form = document.getElementById(formId);
            if (form) form.reset();

            const fileText = el.querySelector('[id$="FileName"]');
            if (fileText) fileText.textContent = '';
        }
    }

    function openAddClassModal() {
        openAppModal('addClassModal');
    }

    // ---------- Show selected file name ----------
    function showFileName(input, targetId) {
        const target = document.getElementById(targetId);
        if (!target) return;
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const sizeKB = (file.size / 1024).toFixed(1);
            target.innerHTML = `<i class="fas fa-file-excel" style="color:#00AA66;"></i> ${file.name} <span style="color:#999;">(${sizeKB} KB)</span>`;
        } else {
            target.textContent = '';
        }
    }

    // ---------- Add class ----------
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
                    closeAppModal('addClassModal');
                    showToast('Class created successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = data.message || 'Unknown error.';
                    if (data.errors) errorMsg = Object.values(data.errors).flat().join(' • ');
                    showToast(errorMsg, true);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('Network error. Please check your connection.', true);
            });
    }

    // ---------- Teacher upload modal ----------
    function openTeacherUploadModal() {
        const form = document.getElementById('uploadTeacherForm');
        form.reset();
        const nameEl = document.getElementById('teacherFileName');
        if (nameEl) nameEl.textContent = '';
        openAppModal('uploadTeacherModal');
    }

    function submitUploadTeachers(e) {
        e.preventDefault();
        const form = document.getElementById('uploadTeacherForm');
        const fileInput = document.getElementById('teacherFileInput');

        if (!fileInput.files.length) {
            showToast('Please select a file to upload.', true);
            return;
        }

        const formData = new FormData(form);
        const btn = document.getElementById('uploadTeacherBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        fetch('{{ route("admin.master-data.upload-teacher") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                if (data.success) {
                    closeAppModal('uploadTeacherModal');
                    showToast(data.message || 'Teachers uploaded successfully!');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(data.message || 'Upload failed.', true);
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast('Error uploading. Please try again.', true);
            });
    }

    // ---------- Student upload modal ----------
    function openUploadModal(classId, className) {
        document.getElementById('uploadClassId').value = classId;
        document.getElementById('uploadClassName').textContent = className;
        const form = document.getElementById('uploadStudentForm');
        form.reset();
        document.getElementById('uploadClassId').value = classId;
        const nameEl = document.getElementById('studentFileName');
        if (nameEl) nameEl.textContent = '';
        openAppModal('uploadStudentModal');
    }

    function submitUploadStudents(e) {
        e.preventDefault();
        const form = document.getElementById('uploadStudentForm');
        const fileInput = document.getElementById('studentFileInput');

        if (!fileInput.files.length) {
            showToast('Please select a file to upload.', true);
            return;
        }

        const formData = new FormData(form);
        const btn = document.getElementById('uploadStudentBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        fetch('{{ route("admin.master-data.upload-student") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                if (data.success) {
                    closeAppModal('uploadStudentModal');
                    showToast(data.message || 'Students uploaded successfully!');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(data.message || 'Upload failed.', true);
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast('Error uploading. Please try again.', true);
            });
    }

    // ---------- View students ----------
    function viewStudents(classId) {
        window.location.href = '{{ route("admin.class-students", "") }}' + '/' + classId;
    }

    // ---------- Assign teacher ----------
    function assignTeacher(teacherId) {
        document.getElementById('assignTeacherId').value = teacherId;
        document.getElementById('existingAssignments').innerHTML =
            '<div style="color:#999;font-size:13px;">Loading…</div>';
        openAppModal('assignTeacherModal');
        updateSubjects();
        loadExistingAssignments(teacherId);
    }

    // function updateSubjects() {
    //     const selectedOption = document.querySelector('#assignClassId option:checked');
    //     const grade = selectedOption ? selectedOption.dataset.grade : '';
    //     const subjectSelect = document.getElementById('assignSubjectId');
    //     subjectSelect.innerHTML = '';
    //     const subjectsByGrade = @json($subjectsByGrade);
    //     const subjects = subjectsByGrade[grade] || [];
    //     subjects.forEach(subj => {
    //         const opt = document.createElement('option');
    //         opt.value = subj.id;
    //         opt.textContent = subj.name;
    //         subjectSelect.appendChild(opt);
    //     });
    // }

    // ---------- Global modal behaviors (backdrop click, Escape key) ----------
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal when the dark backdrop is clicked
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) {
                    closeAppModal(backdrop.id);
                }
            });
        });

        // Close the topmost open modal with the Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal-backdrop.active');
                if (openModals.length) {
                    closeAppModal(openModals[openModals.length - 1].id);
                }
            }
        });

        // Init the section tabs
        const activeTab = document.querySelector('.section-tab.active') ||
            document.querySelector('.section-tab[data-grade="Grade 5"]');
        if (activeTab) activeTab.click();
    });

    function loadExistingAssignments(teacherId) {
    const container = document.getElementById('existingAssignments');
    container.innerHTML = '<div style="color:#999;font-size:13px;">Loading…</div>';

    fetch(`/admin/teachers/${teacherId}/assignments`, {
        headers: { 'Accept': 'application/json' },
    })
    .then(async res => {
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch { throw new Error(`HTTP ${res.status}: ${text.slice(0, 200)}`); }
        if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);
        return data;
    })
    .then(data => {
        const list = data.assignments || data.data || [];
        if (!list.length) {
            container.innerHTML = '<div style="color:#999;font-size:13px;">No assignments yet.</div>';
            return;
        }
        container.innerHTML = list.map(a => `
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #eee;">
                <div style="font-size:13px;font-weight:600;color:#333;">
                    ${a.class_name ?? ''} — ${a.subject ?? ''}
                </div>
                <button type="button" onclick="unassign(${a.id})"
                    style="background:#fee2e2;color:#991b1b;border:none;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
        `).join('');
    })
    .catch(err => {
        console.error('loadExistingAssignments failed:', err);
        container.innerHTML = `<div style="color:#991b1b;font-size:13px;">Failed to load: ${err.message}</div>`;
    });
}

    function addAssignment() {
    const teacherId = document.getElementById('assignTeacherId').value;
    const classId   = document.getElementById('assignClassId').value;
    const subjectId = document.getElementById('assignSubjectId').value;

    if (!classId || !subjectId) {
        showToast('Please select a class and a subject.', true);
        return;
    }

    fetch('{{ route("admin.teacher-assign") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            teacher_profile_id: teacherId,
            class_id:           classId,
            subject_id:         subjectId,
        }),
    })
    .then(res => res.json())
.then(data => {
    if (data.success) {
        closeAppModal('assignTeacherModal');
        showToast(data.message || 'Assignment added.');
        setTimeout(() => location.reload(), 1200);
    } else {
        showToast(data.message || 'Assignment failed.', true);
    }
})
    .catch(() => showToast('Network error.', true));
}

function unassign(id) {
    showConfirm(
        'Remove this assignment?',
        () => {
            fetch(`/admin/teacher-assignments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Assignment removed.');
                    const teacherId = document.getElementById('assignTeacherId').value;
                    loadExistingAssignments(teacherId);
                    setTimeout(() => location.reload(), 2000);

                } else {
                    showToast(data.message || 'Remove failed.', true);
                }
            })
            .catch(() => showToast('Network error.', true));
        },
        { title: 'Remove Assignment', okText: 'Yes, Remove', danger: true }
    );
}

function updateSubjects() {
    const selectedOption = document.querySelector('#assignClassId option:checked');
    const grade = selectedOption ? selectedOption.dataset.grade : '';
    const subjectSelect = document.getElementById('assignSubjectId');
    subjectSelect.innerHTML = '';

    const subjectsByGrade = @json($subjectsByGrade);
    const subjects = subjectsByGrade[grade] || [];
    subjects.forEach(subj => {
        const opt = document.createElement('option');
        opt.value = subj.id;
        opt.textContent = subj.name;
        subjectSelect.appendChild(opt);
    });
}
</script>
@endpush