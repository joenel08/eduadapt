(function () {
    'use strict';

    let currentConfigItem = null;

    window.openContentConfigModal = function (ref) {
        const item = window.getContentItemByRef(ref);
        if (!item) {
            alert('Content item not found.');
            return;
        }

        const id = item.serverId || item.id;
        if (!id) {
            alert('Content ID not found.');
            return;
        }

        const state = window.__contentState;
        const grade = state.currentGrade;
        const term = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        const type = item.itemType;

        currentConfigItem = { type, id, grade, term, subject, week };

        // Build the content info
        const infoHtml = `
            <div style="margin-bottom:20px; padding:12px; background:#f8f9fa; border-radius:6px;">
                <strong>Content:</strong> ${item.title || 'Untitled'}
                <span style="margin-left:15px;"><strong>Type:</strong> ${item.badgeType || type}</span>
            </div>
        `;
        const infoEl = document.getElementById('configContentInfo');
        if (infoEl) infoEl.innerHTML = infoHtml;

        // Fetch existing configuration
        const url = `/teacher/content-library/${encodeURIComponent(grade)}/${encodeURIComponent(term)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}/config/${encodeURIComponent(type)}/${encodeURIComponent(id)}`;
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderConfigForm(data.classes, data.releases);
                    window.openModal('contentConfigModal');
                } else {
                    alert('Failed to load configuration.');
                }
            })
            .catch(() => alert('Network error.'));
    };

    function renderConfigForm(classes, releases) {
        const container = document.getElementById('configClassList');
        if (!container) return;

        if (!classes || !classes.length) {
            container.innerHTML = '<p class="text-muted">No classes available for this subject and grade.</p>';
            return;
        }

        let html = `
            <div style="margin-top:15px;">
                <h4>Assign to Classes</h4>
                <table class="table table-bordered" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="padding:8px; border:1px solid #ddd;">Class</th>
                            <th style="padding:8px; border:1px solid #ddd;">Release Date & Time</th>
                            <th style="padding:8px; border:1px solid #ddd;">Due Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        classes.forEach(cls => {
            const release = releases[cls.id] || {};
            const rDate = release.release_date ? new Date(release.release_date).toISOString().slice(0, 16) : '';
            const dDate = release.due_date ? new Date(release.due_date).toISOString().slice(0, 16) : '';
            html += `
                <tr>
                    <td style="padding:8px; border:1px solid #ddd;">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" class="config-class-checkbox" data-class-id="${cls.id}" ${release.id ? 'checked' : ''}>
                            ${cls.name}
                        </label>
                    </td>
                    <td style="padding:8px; border:1px solid #ddd;">
                        <input type="datetime-local" class="config-release-date" data-class-id="${cls.id}" value="${rDate}" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                    <td style="padding:8px; border:1px solid #ddd;">
                        <input type="datetime-local" class="config-due-date" data-class-id="${cls.id}" value="${dDate}" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px;">
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
        container.innerHTML = html;
    }

    window.saveContentConfig = function () {
        if (!currentConfigItem) {
            alert('No configuration item selected.');
            return;
        }

        const assignments = [];
        const checkboxes = document.querySelectorAll('.config-class-checkbox:checked');
        if (!checkboxes.length) {
            alert('Please select at least one class.');
            return;
        }

        checkboxes.forEach(cb => {
            const classId = parseInt(cb.dataset.classId);
            const releaseInput = document.querySelector(`.config-release-date[data-class-id="${classId}"]`);
            const dueInput = document.querySelector(`.config-due-date[data-class-id="${classId}"]`);

            // Convert datetime-local value to format expected by server (Y-m-d H:i:s)
            // Convert datetime-local value to format expected by server (Y-m-d H:i:s)
            let releaseDate = releaseInput ? releaseInput.value : '';
            let dueDate = dueInput ? dueInput.value : '';

            // Replace 'T' with space and add seconds
            if (releaseDate) {
                releaseDate = releaseDate.replace('T', ' ') + ':00';
            }
            if (dueDate) {
                dueDate = dueDate.replace('T', ' ') + ':00';
            }

            assignments.push({
                class_id: classId,
                release_date: releaseDate || null,
                due_date: dueDate || null,
            });
        });

        const state = window.__contentState;
        const grade = state.currentGrade;
        const term = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;

        const data = {
            content_type: currentConfigItem.type,
            content_id: currentConfigItem.id,
            assignments: assignments,
        };

        const url = `/teacher/content-library/${encodeURIComponent(grade)}/${encodeURIComponent(term)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}/config`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert('Configuration saved.');
                    window.closeModal('contentConfigModal');
                    if (typeof window.loadContentFromServer === 'function') {
                        window.loadContentFromServer();
                    }
                } else {
                    console.error('Save error:', res);
                    alert('Save failed: ' + (res.message || JSON.stringify(res.errors || {})));
                }
            })
            .catch(err => {
                console.error('Network error:', err);
                alert('Network error.');
            });
    };
})();