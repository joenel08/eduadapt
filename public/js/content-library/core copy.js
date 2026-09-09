/**
 * EduAdapt Content Library – Core Module
 * All global functions are attached to 'window' for inline onclick handlers.
 */
(function () {
    'use strict';

    // ===== Shared state =====
    const state = {
        currentView: 'grades',
        currentGrade: null,
        currentQuarter: null,
        currentSubject: null,
        currentWeek: null,
        weekContent: {},
        activeContentMenuId: null,
        selectedContentRef: null,
    };

    // Expose state to other modules (optional)
    window.__contentState = state;

    // ===== Modal functions =====
    window.openModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('active');
            el.style.display = 'block';   // ensure it becomes visible
        }
    };

    window.closeModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('active');
            el.style.display = 'none';    // hide it
        }
    };
    // ===== Add Content modal =====
    window.openAddContentModal = function () {
        window.openModal('addContentTypeModal');
    };

    // ===== Content type selection (called from buttons) =====
    window.selectContentType = function (type) {
        window.closeModal('addContentTypeModal');
        switch (type) {
            case 'materials':
                // Open materials modal (will be handled by materials.js)
                if (typeof window.openLearningMaterialsModal === 'function') {
                    window.openLearningMaterialsModal();
                } else {
                    window.openModal('learningMaterialsModal');
                }
                break;
            case 'preAssessment':
                if (typeof window.updateAssessmentUploadVisibility === 'function') {
                    window.updateAssessmentUploadVisibility('preAssessment');
                }
                window.openModal('preAssessmentModal');
                break;
            case 'postAssessment':
                if (typeof window.updateAssessmentUploadVisibility === 'function') {
                    window.updateAssessmentUploadVisibility('postAssessment');
                }
                window.openModal('postAssessmentModal');
                break;
            case 'intervention':
                if (typeof window.hydrateInterventionFromCurrentWeek === 'function') {
                    window.hydrateInterventionFromCurrentWeek();
                }
                if (typeof window.resetInterventionForms === 'function') {
                    window.resetInterventionForms();
                }
                if (typeof window.updateQuizUploadVisibility === 'function') {
                    window.updateQuizUploadVisibility();
                }

                // Open modal first
                window.openModal('interventionModal');

                // Then load data after a short delay (ensures DOM is ready)
                setTimeout(() => {
                    if (typeof window.loadInterventionFromServer === 'function') {
                        window.loadInterventionFromServer();
                    } else {
                        console.error('loadInterventionFromServer not defined.');
                    }
                }, 300);
                break;
                window.openModal('interventionModal');
                break;
            default:
                console.warn('Unknown content type:', type);
        }
    };

    // ===== Navigation (server‑driven) =====
    window.showGrades = function () {
        window.location.href = '/teacher/content-library';
    };

    window.showSubjects = function (grade, quarter) {
        window.location.href = '/teacher/content-library/'
            + encodeURIComponent(grade) + '/' + encodeURIComponent(quarter);
    };

    window.showWeeks = function (grade, quarter, subject) {
        window.location.href = '/teacher/content-library/'
            + encodeURIComponent(grade) + '/' + encodeURIComponent(quarter)
            + '/' + encodeURIComponent(subject);
    };

    window.showWeekContent = function (grade, quarter, subject, week) {
        window.location.href = '/teacher/content-library/'
            + encodeURIComponent(grade) + '/' + encodeURIComponent(quarter)
            + '/' + encodeURIComponent(subject) + '/' + encodeURIComponent(week);
    };

    // ===== Utility =====
    window.escapeHtml = function (text) {
        if (text == null) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };
    window.formatQuestionsDetailHtml = function (questions, examType) {
        if (!questions || !questions.length) return '<p class="text-muted">No questions.</p>';
        let html = `<div class="questions-container" style="margin-top:10px;">`;
        questions.forEach((q, idx) => {
            // Use the same classes as the edit modal
            html += `<div class="question-item" style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:12px;">`;
            html += `<div class="question-item-header" style="font-weight:600;margin-bottom:8px;">Question ${idx + 1}</div>`;
            html += `<div class="question-input-group" style="margin-bottom:8px;">`;
            html += `<input type="text" class="question-input" value="${window.escapeHtml(q.question)}" readonly style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">`;
            html += `</div>`;

            if (q.type === 'multipleChoice' && q.choices) {
                html += `<div class="choice-inputs" style="margin-top:8px;">`;
                q.choices.forEach((c, ci) => {
                    const isCorrect = c === q.correctAnswer;
                    const label = String.fromCharCode(65 + ci);
                    html += `<div class="choice-input-row" style="display:flex;align-items:center;gap:8px;padding:4px 0;">`;
                    html += `<span style="font-weight:500;min-width:20px;">${label}.</span>`;
                    html += `<input type="text" class="choice-input" value="${window.escapeHtml(c)}" readonly style="flex:1;padding:6px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">`;
                    if (isCorrect) {
                        html += `<span style="color:#28a745;font-weight:bold;margin-left:8px;">✓ Correct</span>`;
                    }
                    html += `</div>`;
                });
                html += `</div>`;
            } else if (q.type === 'trueFalse') {
                html += `<div class="truefalse-inputs" style="margin-top:8px;">`;
                html += `<div class="question-answer-box" style="display:flex;align-items:center;gap:12px;">`;
                html += `<label style="font-weight:500;">Answer Key:</label>`;
                html += `<select class="manual-answer-input" disabled style="padding:6px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">`;
                html += `<option value="">Select answer</option>`;
                html += `<option value="True" ${q.correctAnswer === 'True' ? 'selected' : ''}>True</option>`;
                html += `<option value="False" ${q.correctAnswer === 'False' ? 'selected' : ''}>False</option>`;
                html += `</select>`;
                html += `</div>`;
                html += `</div>`;
            } else if (q.type === 'matchingType' && q.pairs) {
                html += `<div class="matching-inputs" style="margin-top:8px;">`;
                html += `<div class="matching-pairs" style="display:flex;flex-direction:column;gap:4px;">`;
                q.pairs.forEach((p, pi) => {
                    const left = window.escapeHtml(p.question || p.left || '');
                    const right = window.escapeHtml(p.answer || p.right || '');
                    html += `<div class="matching-pair" style="display:flex;align-items:center;gap:8px;">`;
                    html += `<input type="text" class="matching-left-input" value="${left}" readonly style="flex:1;padding:6px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">`;
                    html += `<span>↔</span>`;
                    html += `<input type="text" class="matching-right-input" value="${right}" readonly style="flex:1;padding:6px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">`;
                    html += `</div>`;
                });
                html += `</div>`;
                html += `</div>`;
            }
            html += `</div>`;
        });
        html += `</div>`;
        return html;
    };

    window.getCurrentContentKey = function () {
        const { currentGrade, currentQuarter, currentSubject, currentWeek } = state;
        if (!currentGrade || !currentQuarter || !currentSubject || !currentWeek) return null;
        return `${currentGrade}-${currentQuarter}-${currentSubject}-${currentWeek}`;
    };

    // ===== Content rendering =====
    window.getWeekContentItems = function (content) {
        const items = [];

        // Learning materials (unchanged)
        if (content.learningMaterials && Array.isArray(content.learningMaterials)) {
            content.learningMaterials.forEach((material, index) => {
                items.push({
                    itemType: 'learningMaterial',
                    index: index,
                    icon: 'fa-book-open',
                    title: material.title || 'Learning Material',
                    uploadedAt: material.dateUploaded || '',
                    badgeType: 'Learning Material',
                    fileName: material.fileName || '',
                    videoLink: material.videoLink || '',
                    quizContent: material.quizContent || material.description || '',
                    extraDetails: material.fileName
                        ? (material.title ? `Module: ${material.title}` : 'Module') + ' · ' + material.fileName
                        : (material.title ? `Module: ${material.title}` : 'Module details available')
                });
            });
        }

        // Pre-assessment
        if (content.preAssessment) {
            const pa = content.preAssessment;
            const questionsPreview = window.formatQuestionsPreview(pa.questions, pa.examType);
            items.push({
                itemType: 'preAssessment',
                index: null,
                icon: 'fa-clipboard',
                title: pa.title || 'Pre-Assessment',
                uploadedAt: pa.dateUploaded || '',
                badgeType: 'Pre-Assessment',
                fileName: pa.fileName || '',
                videoLink: pa.videoLink || '',
                quizContent: pa.quizContent || '',
                extraDetails: 'Timer: ' + (pa.timer || '00:00:00')
                    + ' | Due: ' + (pa.dueDate || 'Not set')
                    + ' | ' + questionsPreview
            });
        }

        // Post-assessment
        if (content.postAssessment) {
            const po = content.postAssessment;
            const questionsPreview = window.formatQuestionsPreview(po.questions, po.examType);
            items.push({
                itemType: 'postAssessment',
                index: null,
                icon: 'fa-clipboard-check',
                title: po.title || 'Post-Assessment',
                uploadedAt: po.dateUploaded || '',
                badgeType: 'Post-Assessment',
                fileName: po.fileName || '',
                videoLink: po.videoLink || '',
                quizContent: po.quizContent || '',
                extraDetails: 'Timer: ' + (po.timer || '00:00:00')
                    + ' | Due: ' + (po.dueDate || 'Not set')
                    + ' | ' + questionsPreview
            });
        }

        // Intervention – we need to handle quizzes inside intervention
        if (content.intervention) {
            const inter = content.intervention;
            // If intervention has a quiz array, we can format it
            let quizPreview = '';
            if (inter.quizzes && inter.quizzes.length) {
                // Assume each quiz has .questions and .examType
                const allQuestions = inter.quizzes.flatMap(q => q.questions || []);
                if (allQuestions.length) {
                    // Use first quiz's examType or 'mixed'
                    const examType = inter.quizzes[0]?.examType || 'mixed';
                    quizPreview = ' | ' + window.formatQuestionsPreview(allQuestions, examType);
                }
            }
            items.push({
                itemType: 'intervention',
                index: null,
                icon: 'fa-graduation-cap',
                title: inter.title || 'Learning Intervention',
                uploadedAt: inter.dateUploaded || '',
                badgeType: 'Intervention',
                fileName: inter.fileName || '',
                videoLink: inter.videoLink || '',
                quizContent: inter.quizContent || '',
                extraDetails: typeof window.getInterventionStatsLabel === 'function'
                    ? window.getInterventionStatsLabel(inter) + quizPreview
                    : 'Materials, Videos, Quizzes' + quizPreview
            });
        }

        return items;
    };

    window.renderContentItemCard = function (item) {
        const ref = item.itemType + (item.index !== null ? '-' + item.index : '');
        const uploadedLabel = item.uploadedAt ? new Date(item.uploadedAt).toLocaleDateString() : 'Not set';
        const previewText = item.extraDetails || 'Click to view details';

        return `
            <div class="content-item-card" onclick="window.viewContentItem('${ref}')">
                <div class="content-item-header">
                    <div class="content-item-title">
                        <i class="fas ${item.icon}"></i>
                        ${window.escapeHtml(item.title)}
                    </div>
                </div>
                <div class="content-item-meta">
                    <span class="content-meta-pill">${window.escapeHtml(item.badgeType)}</span>
                    <span class="content-meta-pill">${window.escapeHtml(uploadedLabel)}</span>
                </div>
                <div class="content-item-preview">${window.escapeHtml(previewText)}</div>
                <div class="content-menu-wrap">
                    <button class="content-menu-btn" type="button" onclick="window.toggleContentMenu(event, '${ref}')">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="content-menu-dropdown" id="contentMenu-${ref}">
                        <button class="content-menu-option" onclick="window.openEditContent(event, '${ref}')">
                            <i class="fas fa-pen"></i> Edit
                        </button>
                        <button class="content-menu-option danger" onclick="window.deleteContentItem(event, '${ref}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    };

    window.renderWeekContent = function (grade, quarter, subject, week) {
        const container = document.getElementById('contentItemsContainer');
        if (!container) return;

        const contentKey = `${grade}-${quarter}-${subject}-${week}`;
        const content = state.weekContent[contentKey] || {};
        const items = window.getWeekContentItems(content);
        const hasContent = items.length > 0;

        let html = `
            <div class="week-content-page">
                <div class="week-header">
                    <i class="fas fa-folder-open"></i> ${subject} - ${week}
                </div>
                <div class="week-subheader">Manage your lesson content</div>
                <div class="week-actions-row">
                    <button class="add-content-main-btn" type="button" onclick="window.openAddContentModal()">
                        <i class="fas fa-plus"></i> Add Content
                    </button>
                </div>
        `;

        if (!hasContent) {
            html += `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                    <div class="empty-state-title">No content uploaded for this week.</div>
                    <div class="empty-state-text">Start building your lesson by adding:</div>
                    <ul class="empty-state-list">
                        <li>Learning Materials</li>
                        <li>Pre-Assessment</li>
                        <li>Post-Assessment</li>
                        <li>Learning Intervention with Mini Quiz</li>
                    </ul>
                </div>
            `;
        } else {
            html += `<div class="content-and-package-wrap">`;
            html += `<div class="content-items-grid">`;
            items.forEach(item => {
                html += window.renderContentItemCard(item);
            });
            html += `</div>`;
            // Configure Release button (if all components present)
            if (typeof window.hasAllRequiredLearningComponents === 'function' && window.hasAllRequiredLearningComponents(content)) {
                html += `
                    <div class="learning-package-rail">
                        <button class="btn-configure-package" type="button" onclick="window.openLearningPackageModal()">
                            <i class="fas fa-calendar-check"></i>
                            <span>Configure Release</span>
                        </button>
                    </div>
                `;
            }
            html += `</div>`;
        }
        html += `</div>`;
        container.innerHTML = html;
    };

    // ===== Content menu (edit/delete) =====
    window.toggleContentMenu = function (event, ref) {
        event.stopPropagation();
        if (state.activeContentMenuId && state.activeContentMenuId !== ref) {
            const old = document.getElementById('contentMenu-' + state.activeContentMenuId);
            if (old) old.classList.remove('active');
        }
        const menu = document.getElementById('contentMenu-' + ref);
        if (!menu) return;
        const isActive = menu.classList.contains('active');
        menu.classList.toggle('active', !isActive);
        state.activeContentMenuId = !isActive ? ref : null;
    };

    window.closeAllContentMenus = function () {
        document.querySelectorAll('.content-menu-dropdown.active').forEach(el => el.classList.remove('active'));
        state.activeContentMenuId = null;
    };

    // ===== View content details =====
    window.parseContentRef = function (ref) {
        if (ref.startsWith('learningMaterial-')) {
            return { itemType: 'learningMaterial', index: parseInt(ref.split('-')[1], 10) };
        }
        return { itemType: ref, index: null };
    };

    window.getContentItemByRef = function (ref) {
        const key = window.getCurrentContentKey();
        if (!key) return null;
        const content = state.weekContent[key] || {};
        const parsed = window.parseContentRef(ref);

        if (parsed.itemType === 'learningMaterial') {
            const item = content.learningMaterials && content.learningMaterials[parsed.index];
            if (item) {
                return { ...item, itemType: parsed.itemType, index: parsed.index };
            }
        }
        if (parsed.itemType === 'preAssessment' && content.preAssessment) {
            return { ...content.preAssessment, itemType: parsed.itemType, index: null };
        }
        if (parsed.itemType === 'postAssessment' && content.postAssessment) {
            return { ...content.postAssessment, itemType: parsed.itemType, index: null };
        }
        if (parsed.itemType === 'intervention' && content.intervention) {
            const inter = content.intervention;
            let allQuestions = [];
            if (inter.quizzes && inter.quizzes.length) {
                inter.quizzes.forEach(quiz => {
                    if (quiz.questions && quiz.questions.length) {
                        allQuestions = allQuestions.concat(quiz.questions);
                    }
                });
            }
            return {
                ...inter,
                itemType: parsed.itemType,
                index: null,
                questions: allQuestions
            };
        }
        return null;
    };

    window.getLabelByItemType = function (itemType) {
        const map = {
            preAssessment: 'Pre-Assessment',
            postAssessment: 'Post-Assessment',
            intervention: 'Intervention',
            learningMaterial: 'Learning Material'
        };
        return map[itemType] || 'Content';
    };

    window.viewContentItem = function (ref) {
        window.closeAllContentMenus();
        const item = window.getContentItemByRef(ref);
        if (!item) return;

        let rows = [
            { label: 'Title', value: item.title || '-' },
            { label: 'Type', value: window.getLabelByItemType(item.itemType) },
            { label: 'Date Uploaded', value: item.dateUploaded ? new Date(item.dateUploaded).toLocaleString() : 'Not set' },
        ];

        // Add File if exists
        if (item.fileUrl) {
            rows.push({ label: 'File', value: `<a href="${window.escapeHtml(item.fileUrl)}" target="_blank">${window.escapeHtml(item.fileName)}</a>` });
        } else if (item.fileName) {
            rows.push({ label: 'File', value: item.fileName });
        }

        // Add Video Link if exists
        if (item.videoLink) {
            rows.push({ label: 'Video Link', value: `<a href="${window.escapeHtml(item.videoLink)}" target="_blank">${window.escapeHtml(item.videoLink)}</a>` });
        }

        // Add Timer and Due Date for assessments and intervention
        // Inside viewContentItem, after rows initialization:

        if (item.itemType === 'preAssessment' || item.itemType === 'postAssessment' || item.itemType === 'intervention') {
            // Timer
            if (item.timer) {
                rows.push({
                    label: 'Timer (HH:MM:SS):',
                    value: `<div class="time-picker-group">${item.timer}</div>`
                });
            }
            // Due Date
            if (item.dueDate) {
                rows.push({
                    label: 'Due Date:',
                    value: `<input type="date" value="${item.dueDate}" disabled style="padding:6px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">`
                });
            }
            // Exam Type
            if (item.examType) {
                const typeLabel = item.examType === 'mixed' ? 'Mixed (Combined)' :
                    item.examType === 'trueFalse' ? 'True or False' :
                        item.examType === 'matchingType' ? 'Matching Type' : 'Multiple Choice';
                rows.push({
                    label: 'Exam Type:',
                    value: `<select disabled style="padding:6px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;width:100%;"> 
                <option value="${item.examType}" selected>${typeLabel}</option>
            </select>`
                });
            }
            // Questions
            const questions = item.questions || [];
            if (questions.length) {
                rows.push({
                    label: 'Questions:',
                    value: window.formatQuestionsDetailHtml(questions, item.examType)
                });
            } else {
                rows.push({ label: 'Questions:', value: 'No questions available.' });
            }
        } else {
            // For learning materials
            const contentText = item.quizContent || item.description || 'No content';
            rows.push({ label: 'Description', value: window.escapeHtml(contentText).replace(/\n/g, '<br>') });
        }

        const body = document.getElementById('contentDetailsBody');
        if (!body) return;
        body.innerHTML = rows.map(r =>
            `<div class="detail-row"><div class="detail-label">${r.label}</div><div class="detail-value">${r.value}</div></div>`
        ).join('');
        window.openModal('contentDetailsModal');
    };

    // ===== Edit content =====
    window.getEditSchema = function (itemType) {
        if (itemType === 'learningMaterial') {
            return { file: true, video: false, text: true, textLabel: 'Description:', textPlaceholder: 'Enter module description' };
        }
        if (itemType === 'preAssessment' || itemType === 'postAssessment') {
            return { file: true, video: false, text: true, textLabel: 'Quiz Content:', textPlaceholder: 'Enter quiz content' };
        }
        return { file: true, video: true, text: true, textLabel: 'Quiz Content:', textPlaceholder: 'Enter quiz content' };
    };

    window.openEditContent = function (event, ref) {
        event.stopPropagation();
        state.selectedContentRef = ref;
        window.closeAllContentMenus();

        const item = window.getContentItemByRef(ref);
        if (!item) return;

        if (item.itemType === 'preAssessment') {
            if (typeof window.populateAssessmentModal === 'function') {
                // Build data object with all needed fields
                const data = {
                    id: item.serverId || item.id,
                    examType: item.examType || 'multipleChoice',
                    status: item.status || 'open',
                    shuffleQuestions: item.shuffleQuestions || false,
                    shuffleChoices: item.shuffleChoices || false,
                    timer: item.timer || '00:00:00',
                    dueDate: item.dueDate || '',
                    inputMethod: item.inputMethod || 'upload',
                    questions: item.questions || [],
                    fileName: item.fileName || ''
                };
                window.populateAssessmentModal('preAssessment', data);
            }
            return;
        }

        if (item.itemType === 'intervention') {
            if (typeof window.openInterventionEditModal === 'function') {
                window.openInterventionEditModal(item);
            } else {
                // Fallback: open the intervention modal anyway
                window.openModal('interventionModal');
            }
            return;
        }

        const schema = window.getEditSchema(item.itemType);

        document.getElementById('editContentTitle').value = item.title || '';
        document.getElementById('editContentFileName').value = item.fileName || '';
        document.getElementById('editContentVideoLink').value = item.videoLink || '';
        document.getElementById('editContentText').value = item.quizContent || '';
        document.getElementById('editContentFileUpload').value = '';

        document.getElementById('editFileGroup').style.display = schema.file ? 'block' : 'none';
        document.getElementById('editVideoGroup').style.display = schema.video ? 'block' : 'none';
        document.getElementById('editTextGroup').style.display = schema.text ? 'block' : 'none';
        document.getElementById('editContentTextLabel').textContent = schema.textLabel;
        document.getElementById('editContentText').placeholder = schema.textPlaceholder;

        window.openModal('editContentModal');
    };

    window.saveEditedContent = async function () {
        if (!state.selectedContentRef) return;
        const title = document.getElementById('editContentTitle').value.trim();
        if (!title) { alert('Title is required.'); return; }

        const parsed = window.parseContentRef(state.selectedContentRef);
        if (parsed.itemType !== 'learningMaterial') {
            // For other types, fallback to localStorage (you can expand later)
            // ... keep old behavior
            return;
        }

        // Get the item ID from the ref
        const item = window.getContentItemByRef(state.selectedContentRef);
        if (!item) { alert('Item not found.'); return; }
        const itemId = item.serverId || item.id;
        if (!itemId) { alert('Item ID not found.'); return; }

        const description = document.getElementById('editContentText').value.trim();
        const fileInput = document.getElementById('editContentFileUpload');
        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
        if (fileInput.files && fileInput.files[0]) {
            formData.append('file', fileInput.files[0]);
        }

        try {
            const res = await fetch(`/teacher/content-library/update/${itemId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });
            if (!res.ok) throw new Error('Update failed');
            const data = await res.json();
            if (data.success) {
                window.closeModal('editContentModal');
                // Refresh content
                if (typeof window.loadContentFromServer === 'function') {
                    await window.loadContentFromServer();
                }
                // Also refresh modal list if open
                if (typeof window.renderMaterials === 'function') {
                    window.renderMaterials();
                }
                alert('Content updated successfully!');
            } else {
                alert('Update failed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            alert('Network error: ' + e.message);
        }
    };

    window.deleteContentItem = async function (event, ref) {
        event.stopPropagation();
        window.closeAllContentMenus();
        if (!confirm('Delete this content?')) return;

        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        const parsed = window.parseContentRef(ref);

        // Get the full item (includes serverId)
        const item = window.getContentItemByRef(ref);
        if (!item) {
            alert('Item not found.');
            return;
        }

        // ------------------------------------------------
        // 1. Handle server‑side deletion for Pre‑Assessment & Post‑Assessment
        // ------------------------------------------------
        if (parsed.itemType === 'preAssessment' || parsed.itemType === 'postAssessment') {
            const serverId = item.serverId;
            if (!serverId) {
                alert('Server ID not found for this item.');
                return;
            }

            // Build the correct API endpoint
            let url;
            if (parsed.itemType === 'preAssessment') {
                url = `/teacher/content-library/pre-assessment/${serverId}`;
            } else {
                // You can add post-assessment later
                url = `/teacher/content-library/post-assessment/${serverId}`;
            }

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Delete failed');
                const data = await res.json();
                if (data.success) {
                    // Remove from local state
                    if (parsed.itemType === 'preAssessment') {
                        delete content.preAssessment;
                    } else {
                        delete content.postAssessment;
                    }
                    // Update hasContent flag
                    content.hasContent = Boolean(
                        (content.learningMaterials && content.learningMaterials.length) ||
                        content.preAssessment ||
                        content.postAssessment ||
                        content.intervention
                    );
                    state.weekContent[key] = content;
                    localStorage.setItem('weekContent', JSON.stringify(state.weekContent));

                    // Re‑render the week content
                    window.renderWeekContent(
                        state.currentGrade,
                        state.currentQuarter,
                        state.currentSubject,
                        state.currentWeek
                    );

                    // Optional: re‑fetch from server to sync completely
                    if (typeof window.loadContentFromServer === 'function') {
                        await window.loadContentFromServer();
                    }

                    alert('Content deleted successfully.');
                } else {
                    alert('Delete failed: ' + (data.message || 'Unknown error'));
                }
            } catch (e) {
                alert('Network error: ' + e.message);
            }
            return; // Done
        }

        // ------------------------------------------------
        // 2. For Learning Materials (localStorage only for now)
        // ------------------------------------------------
        if (parsed.itemType === 'learningMaterial' && content.learningMaterials) {
            content.learningMaterials.splice(parsed.index, 1);
            if (typeof window.updateLearningMaterialsData === 'function') {
                window.updateLearningMaterialsData(key, content.learningMaterials);
            }
        } else if (parsed.itemType === 'intervention') {
            delete content.intervention;
        }

        // Update hasContent and save
        content.hasContent = Boolean(
            (content.learningMaterials && content.learningMaterials.length) ||
            content.preAssessment ||
            content.postAssessment ||
            content.intervention
        );
        state.weekContent[key] = content;
        localStorage.setItem('weekContent', JSON.stringify(state.weekContent));

        // Re‑render
        window.renderWeekContent(
            state.currentGrade,
            state.currentQuarter,
            state.currentSubject,
            state.currentWeek
        );
    };
    // ===== Learning Package =====
    const LP_ASSIGNED_CLASS_OPTIONS_ALL = ['Grade 5-A', 'Grade 5-B', 'Grade 5-C', 'Grade 6-A', 'Grade 6-B', 'Grade 6-C'];

    window.getAssignedClassOptionsForGrade = function (grade) {
        if (!grade || typeof grade !== 'string') return [];
        const prefix = grade.trim() + '-';
        return LP_ASSIGNED_CLASS_OPTIONS_ALL.filter(cls => cls.startsWith(prefix));
    };

    window.renderAssignedClassCheckboxes = function (selectedClasses) {
        const wrap = document.getElementById('lpAssignedClasses');
        const hint = document.getElementById('lpAssignedClassesHint');
        if (!wrap) return;
        const options = window.getAssignedClassOptionsForGrade(state.currentGrade);
        if (hint) {
            hint.textContent = state.currentGrade
                ? `Only sections for ${state.currentGrade} are shown (same as this folder).`
                : '';
        }
        if (options.length === 0) {
            wrap.innerHTML = '<p style="margin:0;font-size:13px;color:#888;">No sections defined for this grade.</p>';
            return;
        }
        const selected = new Set(selectedClasses || []);
        wrap.innerHTML = options.map(cls => `
            <label>
                <input type="checkbox" name="lpAssignedClass" value="${window.escapeHtml(cls)}">
                <span>${window.escapeHtml(cls)}</span>
            </label>
        `).join('');
        wrap.querySelectorAll('input[name="lpAssignedClass"]').forEach(cb => {
            cb.checked = selected.has(cb.value);
        });
    };

    window.buildLearningPackageFromForm = function () {
        const assigned = [];
        document.querySelectorAll('input[name="lpAssignedClass"]:checked').forEach(cb => assigned.push(cb.value));
        return {
            gradeLevel: state.currentGrade,
            subject: state.currentSubject,
            quarter: state.currentQuarter,
            weekNumber: state.currentWeek,
            assignedClasses: assigned,
            releaseDate: document.getElementById('lpReleaseDate').value,
            dueDate: document.getElementById('lpDueDate').value,
            status: document.getElementById('lpStatus').value
        };
    };

    window.applyLearningPackageToForm = function (pkg) {
        const p = pkg || {};
        document.getElementById('lpReleaseDate').value = p.releaseDate || '';
        document.getElementById('lpDueDate').value = p.dueDate || '';
        document.getElementById('lpStatus').value = (p.status || 'draft').toLowerCase();
        window.renderAssignedClassCheckboxes(p.assignedClasses);
    };

    window.persistLearningPackageRecord = function (base) {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        base.updatedAt = new Date().toISOString();
        content.learningPackage = base;
        state.weekContent[key] = content;
        localStorage.setItem('weekContent', JSON.stringify(state.weekContent));
        document.getElementById('lpStatus').value = base.status;
    };

    window.openLearningPackageModal = function () {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        if (typeof window.hasAllRequiredLearningComponents === 'function' && !window.hasAllRequiredLearningComponents(content)) {
            alert('Please add all required components first:\n- Learning Materials\n- Pre-Assessment\n- Post-Assessment\n- Learning Intervention with at least one Mini Quiz');
            return;
        }
        window.applyLearningPackageToForm(content.learningPackage);
        window.openModal('learningPackageModal');
    };

    window.saveLearningPackageDraft = function () {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        if (typeof window.hasAllRequiredLearningComponents === 'function' && !window.hasAllRequiredLearningComponents(content)) {
            alert('All required components must be present before saving release settings.');
            return;
        }
        const dropdownStatus = document.getElementById('lpStatus').value.toLowerCase();
        if (dropdownStatus === 'published') {
            alert('To publish, use the Publish button. Save Draft keeps it as Draft or Archived.');
            return;
        }
        if (dropdownStatus === 'archived' && !confirm('Archive this package? It will be hidden from students.')) return;
        const base = window.buildLearningPackageFromForm();
        base.status = dropdownStatus;
        window.persistLearningPackageRecord(base);
        alert(dropdownStatus === 'draft' ? 'Draft saved.' : 'Archived.');
        window.closeModal('learningPackageModal');
        window.renderWeekContent(state.currentGrade, state.currentQuarter, state.currentSubject, state.currentWeek);
    };

    window.publishLearningPackage = function () {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        if (typeof window.hasAllRequiredLearningComponents === 'function' && !window.hasAllRequiredLearningComponents(content)) {
            alert('All required components must be present before publishing.');
            return;
        }
        const base = window.buildLearningPackageFromForm();
        if (!base.releaseDate) { alert('Please set a Release Date.'); return; }
        if (!base.assignedClasses.length) { alert('Select at least one assigned class.'); return; }
        base.status = 'published';
        window.persistLearningPackageRecord(base);
        alert('Published. Students will see it on/after the release date.');
        window.closeModal('learningPackageModal');
        window.renderWeekContent(state.currentGrade, state.currentQuarter, state.currentSubject, state.currentWeek);
    };



    // ===== Load all content from server (currently only materials) =====
    window.loadContentFromServer = async function () {
        const grade = state.currentGrade;
        const quarter = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        if (!grade || !quarter || !subject || !week) return;

        const key = `${grade}-${quarter}-${subject}-${week}`;
        if (!state.weekContent[key]) state.weekContent[key] = {};

        try {
            // 1. Fetch learning materials
            const matUrl = `/teacher/content-library/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const matRes = await fetch(matUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!matRes.ok) throw new Error('Failed to fetch materials');
            const matData = await matRes.json();
            if (matData.success) {
                state.weekContent[key].learningMaterials = matData.items.map(item => ({
                    fileName: item.file_name,
                    title: item.title,
                    description: item.description,
                    type: 'Learning Material',
                    videoLink: '',
                    quizContent: '',
                    dateUploaded: item.uploaded_at,
                    serverId: item.id,
                    fileUrl: item.file_url
                }));
            } else {
                state.weekContent[key].learningMaterials = [];
            }

            // 2. Fetch pre‑assessments
            const preUrl = `/teacher/content-library/pre-assessment/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const preRes = await fetch(preUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!preRes.ok) throw new Error('Failed to fetch pre‑assessments');
            const preData = await preRes.json();
            if (preData.success && preData.preAssessments && preData.preAssessments.length) {
                const latest = preData.preAssessments[0];
                state.weekContent[key].preAssessment = {
                    title: 'Pre-Assessment',
                    timer: latest.timer || '00:00:00',
                    dueDate: latest.due_date || '',
                    fileName: latest.file_name || '',
                    videoLink: '',
                    examType: latest.exam_type,
                    inputMethod: latest.input_method,
                    shuffleQuestions: latest.shuffle_questions || false,
                    shuffleChoices: latest.shuffle_choices || false,
                    questions: latest.questions,
                    quizContent: '',
                    dateUploaded: latest.created_at,
                    serverId: latest.id,
                    status: latest.status || 'open',
                };
            } else {
                // 🔥 IMPORTANT: delete the property if no pre-assessment exists
                delete state.weekContent[key].preAssessment;
            }

            // 3. Post‑assessment (to be added later – same pattern)

            // Save to localStorage so it persists across reloads
            localStorage.setItem('weekContent', JSON.stringify(state.weekContent));

            // Re‑render the week content
            window.renderWeekContent(grade, quarter, subject, week);

        } catch (e) {
            console.error('Error loading content from server:', e);
        }
    };

    // Override loadContentForWeek
    window.loadContentForWeek = function (path) {
        state.currentGrade = path.grade;
        state.currentQuarter = path.term;
        state.currentSubject = path.subject;
        state.currentWeek = path.week;
        state.currentView = 'week-content';

        // Load from localStorage for existing content (fallback)
        const saved = localStorage.getItem('weekContent');
        if (saved) {
            try { state.weekContent = JSON.parse(saved); } catch (e) { /* ignore */ }
        }

        // Ensure the key exists
        const key = window.getCurrentContentKey();
        if (key && !state.weekContent[key]) state.weekContent[key] = {};

        // Now fetch from server and merge (this will also re‑render)
        window.loadContentFromServer();
    };

    window.formatQuestionsPreview = function (questions, examType) {
        if (!questions || !questions.length) return 'No questions.';
        const total = questions.length;
        let typeLabel = examType || 'mixed';
        // Count types if mixed
        if (typeLabel === 'mixed') {
            const counts = { multipleChoice: 0, trueFalse: 0, matchingType: 0 };
            questions.forEach(q => {
                const t = q.type || 'multipleChoice';
                if (counts[t] !== undefined) counts[t]++;
            });
            typeLabel = `Mixed (MC:${counts.multipleChoice}, TF:${counts.trueFalse}, MT:${counts.matchingType})`;
        } else {
            typeLabel = typeLabel === 'trueFalse' ? 'True/False' :
                typeLabel === 'matchingType' ? 'Matching' : 'Multiple Choice';
        }

        // Build preview of first 3 questions
        const sample = questions.slice(0, 3).map((q, idx) => {
            let text = `Q${idx + 1}: ${q.question}`;
            if (q.type === 'multipleChoice' && q.choices) {
                const correct = q.correctAnswer || '';
                text += ` [${q.choices.join(', ')}] → ${correct}`;
            } else if (q.type === 'trueFalse') {
                text += ` → ${q.correctAnswer || ''}`;
            } else if (q.type === 'matchingType' && q.pairs) {
                const pairStr = q.pairs.slice(0, 2).map(p => `${p.question || p.left}↔${p.answer || p.right}`).join(', ');
                text += ` (${q.pairs.length} pairs: ${pairStr}${q.pairs.length > 2 ? ' …' : ''})`;
            }
            return text;
        }).join(' | ');

        return `${total} question(s) – ${typeLabel}${sample ? ' · ' + sample : ''}`;
    };

    // ===== Initialize user info (simple) =====
   window.initializeUserInfo = function () {
    let user = null;

    // 1. From window.user (set by backend)
    if (window.user && window.user.fullName) {
        user = window.user;
    }

    // 2. From localStorage (fallback)
    if (!user) {
        const stored = localStorage.getItem('userData');
        if (stored) {
            try {
                user = JSON.parse(stored);
            } catch (e) {
                // silent fail
            }
        }
    }

    if (!user) {
        return; // silently skip
    }

    const fullName = user.fullName || user.name || user.displayName || 'User';
    const displayName = fullName; // show full name
    const initials = fullName.split(' ')
        .map(name => name.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);

    // Find elements – try ID, then class
    const nameEl = document.getElementById('userName') || document.querySelector('.user-name');
    const avatarEl = document.getElementById('userAvatar') || document.querySelector('.user-avatar');

    if (nameEl) {
        nameEl.textContent = displayName; // full name, not just first name
    }
    if (avatarEl) {
        avatarEl.textContent = initials;
    }
};

    // ===== Expose state to console (for debugging) =====
    window.__contentState = state;

    // Initial load
    window.initializeUserInfo();

    // If window.contentPath is set, load immediately (for page load)
    if (window.contentPath) {
        window.loadContentForWeek(window.contentPath);
    }

})();