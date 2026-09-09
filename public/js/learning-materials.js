// ============ LEARNING MATERIALS ============
let currentLearningMaterials = [];

function loadLearningMaterials() {
    // Called when modal opens
    const url = `/teacher/content-library/fetch/${currentGrade}/${currentQuarter}/${currentSubject}/${currentWeek}`;
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentLearningMaterials = data.items;
            renderLearningMaterials();
        }
    })
    .catch(err => console.error('Error fetching materials:', err));
}

function renderLearningMaterials() {
    const list = document.getElementById('learningMaterialsList');
    if (!list) return;

    if (currentLearningMaterials.length === 0) {
        list.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No materials added yet</p>';
        return;
    }

    let html = '';
    currentLearningMaterials.forEach((item, index) => {
        html += `
            <div class="item-card">
                <div>
                    <div class="item-name" style="margin-bottom: 5px;">
                        <i class="fas fa-file"></i> ${escapeHtml(item.title)}
                    </div>
                    <div style="font-size: 12px; color: #666;">${escapeHtml(item.file_name)}</div>
                    <div style="font-size: 12px; color: #999;">${escapeHtml(item.description || 'No description')}</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <a href="${item.file_url}" target="_blank" class="btn-view" style="padding:6px 12px;background:#0066CC;color:white;border-radius:4px;text-decoration:none;font-size:12px;">View</a>
                    <button class="btn-remove" type="button" onclick="deleteLearningMaterial(${item.id})">Remove</button>
                </div>
            </div>
        `;
    });
    list.innerHTML = html;
}

function addLearningMaterial() {
    const fileInput = document.getElementById('learningMaterialFile');
    const title = document.getElementById('learningMaterialTitle').value.trim();
    const description = document.getElementById('learningMaterialDescription').value.trim();

    if (!title) {
        alert('Please enter a material title');
        return;
    }

    const file = fileInput.files && fileInput.files[0];
    if (!file) {
        alert('Please choose a file to upload');
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);
    formData.append('file', file);
    formData.append('grade_level', currentGrade);
    formData.append('term', currentQuarter);
    formData.append('subject', currentSubject);
    formData.append('week', currentWeek);

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    btn.disabled = true;

    fetch('{{ route('teacher.content-library.upload-material') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        if (data.success) {
            // Add to list and refresh
            currentLearningMaterials.push(data.item);
            renderLearningMaterials();
            // Clear form
            document.getElementById('learningMaterialTitle').value = '';
            document.getElementById('learningMaterialDescription').value = '';
            fileInput.value = '';
            document.getElementById('learningMaterialFileName').textContent = '';
        } else {
            alert('Upload failed: ' + data.message);
        }
    })
    .catch(error => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Network error. Please try again.');
    });
}

function deleteLearningMaterial(id) {
    if (!confirm('Delete this material?')) return;

    fetch(`/teacher/content-library/delete/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentLearningMaterials = currentLearningMaterials.filter(item => item.id !== id);
            renderLearningMaterials();
        } else {
            alert('Delete failed.');
        }
    })
    .catch(() => alert('Network error.'));
}

// Update the modal open function to load materials
function openLearningMaterialsModal() {
    loadLearningMaterials();
    openModal('learningMaterialsModal');
}
