/**
 * Learning Materials – Server-side storage
 * All data is saved to the database via Laravel endpoints.
 */

(function() {
    'use strict';

    // Helper to get current path parameters
    function getPathParams() {
        const st = window.__contentState || {};
        return {
            grade: st.currentGrade,
            quarter: st.currentQuarter,
            subject: st.currentSubject,
            week: st.currentWeek
        };
    }

    // ===== Fetch materials from the server =====
    async function fetchMaterials() {
        const { grade, quarter, subject, week } = getPathParams();
        if (!grade || !quarter || !subject || !week) {
            console.warn('Missing path parameters for fetching materials.');
            return [];
        }

        const url = `/teacher/content-library/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
        try {
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error(`HTTP ${res.status}`);
            }
            const data = await res.json();
            if (data.success) {
                return data.items || [];
            }
            console.error('Fetch materials error:', data.message);
            return [];
        } catch (e) {
            console.error('Network error fetching materials:', e);
            return [];
        }
    }

    // ===== Render materials in the modal =====
    async function renderMaterials() {
        const list = document.getElementById('learningMaterialsList');
        if (!list) return;

        const materials = await fetchMaterials();

        if (materials.length === 0) {
            list.innerHTML = '<p style="text-align:center;color:#999;padding:20px;">No materials added yet</p>';
            return;
        }

        list.innerHTML = materials.map((item, index) => `
            <div class="item-card">
                <div>
                    <div class="item-name" style="margin-bottom:5px;">
                        <i class="fas fa-file"></i> ${window.escapeHtml(item.title)}
                    </div>
                    <div style="font-size:12px;color:#666;">${window.escapeHtml(item.file_name)}</div>
                    <div style="font-size:12px;color:#999;">${window.escapeHtml(item.description || 'No description')}</div>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="${item.file_url}" target="_blank" class="btn-view" style="padding:6px 12px;background:#0066CC;color:white;border-radius:4px;text-decoration:none;font-size:12px;">View</a>
                    <button class="btn-remove" type="button" onclick="window.deleteLearningMaterial(${item.id})">Remove</button>
                </div>
            </div>
        `).join('');
    }

    // ===== Open modal and load materials =====
    window.openLearningMaterialsModal = function() {
        const list = document.getElementById('learningMaterialsList');
        if (list) list.innerHTML = '<p style="text-align:center;color:#999;padding:20px;">Loading...</p>';
        window.openModal('learningMaterialsModal');
        renderMaterials();
    };

    // ===== Upload a new material =====
    window.addLearningMaterial = async function() {
        const fileInput = document.getElementById('learningMaterialFile');
        const title = document.getElementById('learningMaterialTitle').value.trim();
        const description = document.getElementById('learningMaterialDescription').value.trim();

        if (!title) { alert('Please enter a title'); return; }
        const file = fileInput.files?.[0];
        if (!file) { alert('Please choose a file'); return; }

        const { grade, quarter, subject, week } = getPathParams();
        if (!grade || !quarter || !subject || !week) {
            alert('Missing folder path information.');
            return;
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
        formData.append('file', file);
        formData.append('grade_level', grade);
        formData.append('term', quarter);
        formData.append('subject', subject);
        formData.append('week', week);

        const btn = event?.target || document.querySelector('.add-more-btn');
        const originalText = btn?.innerHTML || 'Add';
        if (btn) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            btn.disabled = true;
        }

        try {
            const res = await fetch('/teacher/content-library/upload-material', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error(`HTTP ${res.status}`);
            }

            const data = await res.json();
            if (data.success) {
                document.getElementById('learningMaterialTitle').value = '';
                document.getElementById('learningMaterialDescription').value = '';
                fileInput.value = '';
                document.getElementById('learningMaterialFileName').textContent = '';

                await renderMaterials();
                if (typeof window.renderWeekContent === 'function') {
                    const st = window.__contentState;
                    window.renderWeekContent(st.currentGrade, st.currentQuarter, st.currentSubject, st.currentWeek);
                }
                alert('Material uploaded successfully!');
            } else {
                alert('Upload failed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            alert('Network error: ' + e.message);
        } finally {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    };

    // ===== Delete a material =====
    window.deleteLearningMaterial = async function(id) {
        if (!confirm('Delete this material?')) return;

        try {
            const res = await fetch(`/teacher/content-library/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                throw new Error(`HTTP ${res.status}`);
            }

            const data = await res.json();
            if (data.success) {
                await renderMaterials();
                if (typeof window.renderWeekContent === 'function') {
                    const st = window.__contentState;
                    window.renderWeekContent(st.currentGrade, st.currentQuarter, st.currentSubject, st.currentWeek);
                }
            } else {
                alert('Delete failed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            alert('Network error: ' + e.message);
        }
    };

    // ===== File input listener =====
    document.getElementById('learningMaterialFile')?.addEventListener('change', function() {
        const nameEl = document.getElementById('learningMaterialFileName');
        if (nameEl) nameEl.textContent = this.files?.[0]?.name || '';
    });

})();