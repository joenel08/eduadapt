@extends('layouts.app')

@section('page_title', 'User Management')
@section('page', 'user-management')

@section('content')
<div class="page-title">User Management</div>
<p class="page-subtitle">View and edit student & teacher information, and reset passwords.</p>

<!-- Tabs -->
<div class="section-tabs" style="margin-bottom:20px;">
    <button class="section-tab {{ $tab === 'students' ? 'active' : '' }}" onclick="switchTab('students')" id="tab-students">
        <i class="fas fa-user-graduate"></i> Students ({{ $students->count() }})
    </button>
    <button class="section-tab {{ $tab === 'teachers' ? 'active' : '' }}" onclick="switchTab('teachers')" id="tab-teachers">
        <i class="fas fa-chalkboard-user"></i> Teachers ({{ $teachers->count() }})
    </button>
</div>

<!-- Search -->
<div class="input-group" style="max-width:400px; margin-bottom:20px;">
    <input type="text" id="searchInput" placeholder="Search by name or ID..."
           oninput="filterRows(this.value)">
</div>

<!-- Students Table -->
<div class="table-card" id="panel-students" style="{{ $tab === 'students' ? '' : 'display:none;' }}">
    <table>
        <thead>
            <tr>
                <th>LRN</th>
                <th>Full Name</th>
                <th>Classes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr class="data-row"
                data-search="{{ strtolower($student->first_name . ' ' . $student->last_name . ' ' . $student->lrn) }}"
                data-info="{{ json_encode([
                    'id'          => $student->id,
                    'lrn'         => $student->lrn,
                    'first_name'  => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name'   => $student->last_name,
                    'suffix_name' => $student->suffix_name,
                    'birth_date'  => $student->birth_date,
                    'sex'         => $student->sex,
                    'contact_no'  => $student->contact_no,
                    'address'     => $student->address,
                ]) }}">
                <td>{{ $student->lrn }}</td>
                <td>{{ trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name . ' ' . ($student->suffix_name ?? '')) }}</td>
                <td>
                    @php
                        $classNames = $student->studentClassRecords
                            ->map(fn($r) => $r->class->grade_level . ' - ' . $r->class->section_name)
                            ->implode(', ');
                    @endphp
                    {{ $classNames ?: '—' }}
                </td>
                <td>
                    <button class="action-button" onclick="editStudentFromRow(this)">
                        <i class="fas fa-pen"></i> Edit
                    </button>
                    <button class="action-button danger"
                        onclick="resetStudentPassword({{ $student->id }}, {{ json_encode($student->first_name . ' ' . $student->last_name) }})">
                        <i class="fas fa-key"></i> Reset PW
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#64748b;">No students found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Teachers Table -->
<div class="table-card" id="panel-teachers" style="{{ $tab === 'teachers' ? '' : 'display:none;' }}">
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
            <tr class="data-row"
                data-search="{{ strtolower($teacher->first_name . ' ' . $teacher->last_name . ' ' . $teacher->employee_id) }}"
                data-info="{{ json_encode([
                    'id'          => $teacher->id,
                    'employee_id' => $teacher->employee_id,
                    'prefix_name' => $teacher->prefix_name,
                    'first_name'  => $teacher->first_name,
                    'middle_name' => $teacher->middle_name,
                    'last_name'   => $teacher->last_name,
                    'suffix_name' => $teacher->suffix_name,
                    'contact_no'  => $teacher->contact_no,
                    'address'     => $teacher->address,
                ]) }}">
                <td>{{ $teacher->employee_id }}</td>
                <td>{{ trim(($teacher->prefix_name ?? '') . ' ' . $teacher->first_name . ' ' . ($teacher->middle_name ?? '') . ' ' . $teacher->last_name . ' ' . ($teacher->suffix_name ?? '')) }}</td>
                <td>
                    @php
                        $assigned = $teacher->teacherClassAssignments->map(fn($a) =>
                            $a->class->section_name . ' (' . $a->subject->name . ')'
                        )->implode(', ');
                    @endphp
                    {{ $assigned ?: '—' }}
                </td>
                <td>
                    <button class="action-button" onclick="editTeacherFromRow(this)">
                        <i class="fas fa-pen"></i> Edit
                    </button>
                    <button class="action-button danger"
                        onclick="resetTeacherPassword({{ $teacher->id }}, {{ json_encode($teacher->first_name . ' ' . $teacher->last_name) }})">
                        <i class="fas fa-key"></i> Reset PW
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#64748b;">No teachers found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Edit Student Modal -->
<div class="modal-backdrop" id="editStudentModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-user-edit"></i> Edit Student</h3>
            <button class="modal-close" onclick="closeAppModal('editStudentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editStudentForm" onsubmit="submitEditStudent(event)">
                @csrf
                <input type="hidden" id="editStudentId">

                <div class="input-group">
                    <label>LRN</label>
                    <input type="text" id="editStudentLrn" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 90px;gap:8px;margin-bottom:15px;">
                    <div class="input-group" style="margin:0;">
                        <label>First</label>
                        <input type="text" id="editStudentFirstName" required>
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Middle</label>
                        <input type="text" id="editStudentMiddleName">
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Last</label>
                        <input type="text" id="editStudentLastName" required>
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Suffix</label>
                        <input type="text" id="editStudentSuffix" placeholder="Jr./III">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:15px;">
                    <div class="input-group" style="margin:0;">
                        <label>Birth Date</label>
                        <input type="date" id="editStudentBirthDate">
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Sex</label>
                        <select id="editStudentSex">
                            <option value="">—</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label>Contact Number</label>
                    <input type="text" id="editStudentContact">
                </div>

                <div class="input-group">
                    <label>Address</label>
                    <input type="text" id="editStudentAddress">
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                    <button type="button" class="secondary-button" onclick="closeAppModal('editStudentModal')">Cancel</button>
                    <button type="submit" class="primary-button" id="saveStudentBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal-backdrop" id="editTeacherModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-chalkboard-teacher"></i> Edit Teacher</h3>
            <button class="modal-close" onclick="closeAppModal('editTeacherModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editTeacherForm" onsubmit="submitEditTeacher(event)">
                @csrf
                <input type="hidden" id="editTeacherId">

                <div class="input-group">
                    <label>Employee ID</label>
                    <input type="text" id="editTeacherEmployeeId" required>
                </div>

                <div style="display:grid;grid-template-columns:90px 1fr 1fr 1fr 90px;gap:8px;margin-bottom:15px;">
                    <div class="input-group" style="margin:0;">
                        <label>Prefix</label>
                        <input type="text" id="editTeacherPrefix" placeholder="Mr./Ms.">
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>First</label>
                        <input type="text" id="editTeacherFirstName" required>
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Middle</label>
                        <input type="text" id="editTeacherMiddleName">
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Last</label>
                        <input type="text" id="editTeacherLastName" required>
                    </div>
                    <div class="input-group" style="margin:0;">
                        <label>Suffix</label>
                        <input type="text" id="editTeacherSuffix" placeholder="Jr./III">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contact Number</label>
                    <input type="text" id="editTeacherContact">
                </div>

                <div class="input-group">
                    <label>Address</label>
                    <input type="text" id="editTeacherAddress">
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                    <button type="button" class="secondary-button" onclick="closeAppModal('editTeacherModal')">Cancel</button>
                    <button type="submit" class="primary-button" id="saveTeacherBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ---------- Modal helpers ----------
    function openAppModal(id) { document.getElementById(id).classList.add('active'); }
    function closeAppModal(id) { document.getElementById(id).classList.remove('active'); }

    // ---------- Tabs ----------
    function switchTab(tab) {
        document.getElementById('panel-students').style.display = (tab === 'students') ? '' : 'none';
        document.getElementById('panel-teachers').style.display = (tab === 'teachers') ? '' : 'none';
        document.getElementById('tab-students').classList.toggle('active', tab === 'students');
        document.getElementById('tab-teachers').classList.toggle('active', tab === 'teachers');
    }

    // ---------- Search filter ----------
    function filterRows(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.data-row').forEach(row => {
            row.style.display = row.dataset.search.includes(q) ? '' : 'none';
        });
    }

    // ---------- Edit Student ----------
    function editStudentFromRow(btn) {
        const row = btn.closest('tr');
        const data = JSON.parse(row.dataset.info);
        openEditStudent(data);
    }

    function openEditStudent(data) {
        document.getElementById('editStudentId').value         = data.id;
        document.getElementById('editStudentLrn').value        = data.lrn || '';
        document.getElementById('editStudentFirstName').value  = data.first_name || '';
        document.getElementById('editStudentMiddleName').value = data.middle_name || '';
        document.getElementById('editStudentLastName').value   = data.last_name || '';
        document.getElementById('editStudentSuffix').value     = data.suffix_name || '';
        document.getElementById('editStudentBirthDate').value  = data.birth_date || '';
        document.getElementById('editStudentSex').value        = data.sex || '';
        document.getElementById('editStudentContact').value    = data.contact_no || '';
        document.getElementById('editStudentAddress').value    = data.address || '';
        openAppModal('editStudentModal');
    }

    function submitEditStudent(e) {
        e.preventDefault();
        const id = document.getElementById('editStudentId').value;
        const btn = document.getElementById('saveStudentBtn');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const data = {
            lrn:         document.getElementById('editStudentLrn').value,
            first_name:  document.getElementById('editStudentFirstName').value,
            middle_name: document.getElementById('editStudentMiddleName').value,
            last_name:   document.getElementById('editStudentLastName').value,
            suffix_name: document.getElementById('editStudentSuffix').value,
            birth_date:  document.getElementById('editStudentBirthDate').value || null,
            sex:         document.getElementById('editStudentSex').value,
            contact_no:  document.getElementById('editStudentContact').value,
            address:     document.getElementById('editStudentAddress').value,
            _method:     'PUT',
        };

        fetch(`/admin/user-management/student/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = original;
            if (data.success) {
                closeAppModal('editStudentModal');
                showToast(data.message || 'Student updated.');
                setTimeout(() => location.reload(), 1200);
            } else {
                let err = data.message || 'Update failed.';
                if (data.errors) err = Object.values(data.errors).flat().join(' • ');
                showToast(err, true);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = original;
            showToast('Network error.', true);
        });
    }

    // ---------- Edit Teacher ----------
    function editTeacherFromRow(btn) {
        const row = btn.closest('tr');
        const data = JSON.parse(row.dataset.info);
        openEditTeacher(data);
    }

    function openEditTeacher(data) {
        document.getElementById('editTeacherId').value         = data.id;
        document.getElementById('editTeacherEmployeeId').value = data.employee_id || '';
        document.getElementById('editTeacherPrefix').value     = data.prefix_name || '';
        document.getElementById('editTeacherFirstName').value  = data.first_name || '';
        document.getElementById('editTeacherMiddleName').value = data.middle_name || '';
        document.getElementById('editTeacherLastName').value   = data.last_name || '';
        document.getElementById('editTeacherSuffix').value     = data.suffix_name || '';
        document.getElementById('editTeacherContact').value    = data.contact_no || '';
        document.getElementById('editTeacherAddress').value    = data.address || '';
        openAppModal('editTeacherModal');
    }

    function submitEditTeacher(e) {
        e.preventDefault();
        const id = document.getElementById('editTeacherId').value;
        const btn = document.getElementById('saveTeacherBtn');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const data = {
            employee_id: document.getElementById('editTeacherEmployeeId').value,
            prefix_name: document.getElementById('editTeacherPrefix').value,
            first_name:  document.getElementById('editTeacherFirstName').value,
            middle_name: document.getElementById('editTeacherMiddleName').value,
            last_name:   document.getElementById('editTeacherLastName').value,
            suffix_name: document.getElementById('editTeacherSuffix').value,
            contact_no:  document.getElementById('editTeacherContact').value,
            address:     document.getElementById('editTeacherAddress').value,
            _method:     'PUT',
        };

        fetch(`/admin/user-management/teacher/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = original;
            if (data.success) {
                closeAppModal('editTeacherModal');
                showToast(data.message || 'Teacher updated.');
                setTimeout(() => location.reload(), 1200);
            } else {
                let err = data.message || 'Update failed.';
                if (data.errors) err = Object.values(data.errors).flat().join(' • ');
                showToast(err, true);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = original;
            showToast('Network error.', true);
        });
    }

    // ---------- Reset Student Password ----------
    function resetStudentPassword(id, name) {
        showConfirm(
            `Reset ${name}'s password to their LRN? The student will need to use their LRN to log in.`,
            () => {
                fetch(`/admin/user-management/student/${id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Password reset successfully.');
                    } else {
                        showToast(data.message || 'Reset failed.', true);
                    }
                })
                .catch(() => showToast('Network error.', true));
            },
            { title: 'Reset Student Password', okText: 'Yes, Reset', danger: true }
        );
    }

    // ---------- Reset Teacher Password ----------
    function resetTeacherPassword(id, name) {
        showConfirm(
            `Reset ${name}'s password to their Employee ID? The teacher will need to use their Employee ID to log in.`,
            () => {
                fetch(`/admin/user-management/teacher/${id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Password reset successfully.');
                    } else {
                        showToast(data.message || 'Reset failed.', true);
                    }
                })
                .catch(() => showToast('Network error.', true));
            },
            { title: 'Reset Teacher Password', okText: 'Yes, Reset', danger: true }
        );
    }
</script>
@endpush