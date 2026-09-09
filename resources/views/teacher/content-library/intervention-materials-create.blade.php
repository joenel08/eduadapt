@extends('layouts.teacher-student')

@section('page_title', 'Add Intervention Materials - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>

<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-book-reader"></i> Add Intervention Materials
    </div>
    <div class="week-subheader">Upload materials for a specific intervention level</div>

    <div class="card">
        <div class="card-body">
            <form id="materialsForm" action="{{ route('teacher.content-library.intervention.materials.store', [$grade, $term, $subject, $week]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Level selector --}}
                <div class="form-group">
                    <label for="level">Select Level:</label>
                    <select name="level" id="level" class="form-control" required style="margin-bottom: 20px;">
                        <option value="">-- Select Level --</option>
                        <option value="basic">🔹 Basic (Below Average)</option>
                        <option value="standard" selected>🔹 Standard (Average)</option>
                        <option value="advanced">🔹 Advanced (Above Average)</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="file-upload-area" onclick="document.getElementById('materialFileInput').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload</div>
                        <div class="upload-subtext">PDF / PPT (Max 20MB)</div>
                    </div>


                    <input type="file" id="materialFileInput" style="display:none;" accept=".pdf,.ppt,.pptx">
                    <button type="button" class="btn add-more-btn" onclick="addMaterial()">
                        <i class="fas fa-plus"></i> Add This Material
                    </button>
                </div>



                <div class="items-list" id="materialsList" style="max-height:250px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; padding:8px; background:#f9f9f9;">
                    <div class="item-card">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="materialsTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" name="materials_json" id="materialsJsonInput" value="">

                <div class="modal-buttons" style="margin-top: 20px;">
                    <button type="submit" class="btn  btn-save">Save Materials</button>
                    <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let materials = [];

    function addMaterial() {
        const fileInput = document.getElementById('materialFileInput');
        if (!fileInput.files.length) {
            alert('Please select a file.');
            return;
        }
        const file = fileInput.files[0];
        materials.push({
            file_name: file.name
        });
        renderMaterials();
        fileInput.value = '';
        document.getElementById('materialsJsonInput').value = JSON.stringify(materials);
    }

    function renderMaterials() {
        const container = document.getElementById('materialsList');
        if (materials.length === 0) {
            container.innerHTML = `<div style="padding:12px; color:#888; text-align:center;">No materials uploaded yet.</div>`;
            return;
        }
        container.innerHTML = materials.map((m, i) => `
        <div class="item-card" style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; margin-bottom:6px; background:#fff; border-radius:6px; border:1px solid #eee;">
            <div class="item-name" style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-file-pdf" style="color:#e74c3c;"></i>
                <span>${escapeHtml(m.file_name)}</span>
            </div>
            <button type="button" class="btn-remove" onclick="removeMaterial(${i})" style="background:transparent; border:none; color:#dc3545; cursor:pointer;">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    `).join('');
    }

    // Helper to escape HTML (prevent XSS)
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function removeMaterial(index) {
        materials.splice(index, 1);
        renderMaterials();
        document.getElementById('materialsJsonInput').value = JSON.stringify(materials);
    }

    document.getElementById('materialsForm').addEventListener('submit', function() {
        document.getElementById('materialsJsonInput').value = JSON.stringify(materials);
    });
</script>
@endpush