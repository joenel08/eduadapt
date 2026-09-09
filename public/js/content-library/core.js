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
        isLoading: false,
    };



    window.__contentState = state;

    // ===== Modal functions =====
    window.openModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('active');
            el.style.display = 'block';
        }
    };

    window.closeModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('active');
            el.style.display = 'none';
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

        if (content.learningMaterials && Array.isArray(content.learningMaterials)) {
            content.learningMaterials.forEach((material, index) => {
                const serverId = material.serverId;  // get from material
                const releasesMap = content.releases || {};
                const itemReleases = (releasesMap.learningMaterial && releasesMap.learningMaterial[serverId])
                    ? releasesMap.learningMaterial[serverId]
                    : [];
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
                        : (material.title ? `Module: ${material.title}` : 'Module details available'),
                    serverId: serverId,
                    releases: itemReleases
                });
            });
        }

        if (content.preAssessment) {
            const pa = content.preAssessment;
            const serverId = pa.serverId;
            const releasesMap = content.releases || {};
            const itemReleases = (releasesMap.preAssessment && releasesMap.preAssessment[serverId])
                ? releasesMap.preAssessment[serverId]
                : [];
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
                // extraDetails: 'Timer: ' + (pa.timer || '00:00:00') + ' | Due: ' + (pa.dueDate || 'Not set'),
                serverId: serverId,
                releases: itemReleases
            });
        }

        if (content.postAssessment) {
            const po = content.postAssessment;
            const questionsPreview = window.formatQuestionsPreview(po.questions, po.examType);
            const serverId = po.serverId;
            const releasesMap = content.releases || {};
            const itemReleases = (releasesMap.postAssessment && releasesMap.postAssessment[serverId])
                ? releasesMap.postAssessment[serverId]
                : [];
            items.push({
                itemType: 'postAssessment',
                index: null,
                icon: 'fa-clipboard',
                title: po.title || 'Post-Assessment',
                uploadedAt: po.dateUploaded || '',
                badgeType: 'Post-Assessment',
                fileName: po.fileName || '',
                videoLink: po.videoLink || '',
                quizContent: po.quizContent || '',
                // extraDetails: 'Timer: ' + (po.timer || '00:00:00') + ' | Due: ' + (po.dueDate || 'Not set'),
                serverId: serverId,
                releases: itemReleases
            });
        }

        if (content.intervention) {
            const inter = content.intervention;

            // Materials
            if (inter.materials && inter.materials.length) {
                inter.materials.forEach((mat, idx) => {
                    const serverId = mat.id;
                    const releasesMap = content.releases || {};
                    const itemReleases = (releasesMap.interventionMaterial && releasesMap.interventionMaterial[serverId])
                        ? releasesMap.interventionMaterial[serverId]
                        : [];
                    items.push({
                        itemType: 'interventionMaterial',
                        index: idx,
                        icon: 'fa-book-reader',
                        title: mat.file_name || 'Intervention Material',
                        uploadedAt: mat.created_at || '',
                        badgeType: 'Intervention Material',
                        fileName: mat.file_name || '',
                        videoLink: '',
                        quizContent: '',
                        extraDetails: 'Intervention Material',
                        serverId: serverId,
                        subType: 'material',
                        releases: itemReleases
                    });
                });
            }
            // Videos
            if (inter.videos && inter.videos.length) {
                inter.videos.forEach((vid, idx) => {
                    const serverId = vid.id;
                    const releasesMap = content.releases || {};
                    const itemReleases = (releasesMap.interventionVideo && releasesMap.interventionVideo[serverId])
                        ? releasesMap.interventionVideo[serverId]
                        : [];
                    items.push({
                        itemType: 'interventionVideo',
                        index: idx,
                        icon: 'fa-video',
                        title: vid.video_url || vid.file_name || 'Intervention Video',
                        uploadedAt: vid.created_at || '',
                        badgeType: 'Intervention Video',
                        fileName: vid.file_name || '',
                        videoLink: vid.video_url || '',
                        quizContent: '',
                        extraDetails: vid.video_url ? 'Video Link' : 'Uploaded Video',
                        serverId: serverId,
                        subType: 'video',
                        releases: itemReleases   // ✅ ADDED
                    });
                });
            }

            // Quizzes
            if (inter.quizzes && inter.quizzes.length) {
                inter.quizzes.forEach((quiz, idx) => {
                    const serverId = quiz.id;
                    const releasesMap = content.releases || {};
                    const itemReleases = (releasesMap.interventionQuiz && releasesMap.interventionQuiz[serverId])
                        ? releasesMap.interventionQuiz[serverId]
                        : [];
                    const questionsPreview = window.formatQuestionsPreview(quiz.questions, quiz.exam_type);
                    items.push({
                        itemType: 'interventionQuiz',
                        index: idx,
                        icon: 'fa-question-circle',
                        title: 'Intervention Quiz',
                        uploadedAt: quiz.created_at || '',
                        badgeType: 'Intervention Quiz',
                        fileName: quiz.file_name || '',
                        videoLink: '',
                        quizContent: quiz.questions ? JSON.stringify(quiz.questions) : '',
                        serverId: serverId,
                        subType: 'quiz',
                        releases: itemReleases   // ✅ ADDED
                    });
                });
            }
        }

        return items;
    };

    window.renderContentItemCard = function (item) {
        const ref = item.itemType + (item.index !== null ? '-' + item.index : '');
        const uploadedLabel = item.uploadedAt ? new Date(item.uploadedAt).toLocaleDateString() : 'Not set';
        const previewText = item.extraDetails || 'Click to view details';
        // Build release configurations HTML
        let configHtml = '';
        if (item.releases && item.releases.length) {
            configHtml = `
            <div class="content-item-configs">
                <div class="configs-header">Assigned to:</div>
                <ul class="configs-list">
        `;
            item.releases.forEach(rel => {
                const releaseDate = rel.release_date ? new Date(rel.release_date).toLocaleString() : 'N/A';
                const dueDate = rel.due_date ? new Date(rel.due_date).toLocaleString() : 'N/A';
                const gradeLabel = state.currentGrade ? `${state.currentGrade}` : '';
                const className = window.escapeHtml(rel.class_name);
                const displayName = gradeLabel ? `${gradeLabel} - ${className}` : className;
                configHtml += `
    <li>
        <span class="class-name">${displayName}</span>
        <span class="release-date">Release: ${releaseDate}</span>
        <span class="due-date">Due: ${dueDate}</span>
        <button class="delete-release-btn" onclick="event.stopPropagation(); window.deleteRelease(${rel.id}, '${ref}')" title="Remove this assignment">×</button>
    </li>
`;
            });
            configHtml += `</ul></div>`;
        }
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
                  ${configHtml} 
                    <div class="content-menu-wrap">
                       <button class="content-menu-btn" type="button" onclick="event.stopPropagation(); window.toggleContentMenu(event, '${ref}')">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="content-menu-dropdown" id="contentMenu-${ref}">
                            <button class="content-menu-option" onclick="event.stopPropagation(); window.openContentConfigModal('${ref}')">
                                <i class="fas fa-cog"></i> Configure
                            </button>
                            <button class="content-menu-option danger" onclick="event.stopPropagation(); window.deleteContentItem(event, '${ref}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
            </div>
        `;
    };
    window.deleteRelease = async function (releaseId, ref) {
        if (!confirm('Remove this class assignment?')) return;
        const url = `/teacher/content-library/release/${releaseId}`;
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Refresh the week content to update the card
                    window.loadContentFromServer();
                } else {
                    alert('Delete failed: ' + (data.message || 'Unknown error.'));
                }
            } else {
                alert('Network error.');
            }
        } catch (e) {
            alert('Error: ' + e.message);
        }
    };
    // ===== DROPDOWN SETUP =====
    function setupInterventionDropdown() {
        const dropdownBtn = document.getElementById('interventionDropdownBtn');
        const dropdown = document.querySelector('.dropdown-custom');

        if (dropdownBtn && dropdown) {
            dropdownBtn.removeEventListener('click', dropdownToggleHandler);
            dropdownBtn.addEventListener('click', dropdownToggleHandler);

            document.removeEventListener('click', closeDropdownHandler);
            document.addEventListener('click', closeDropdownHandler);
        }
    }

    function dropdownToggleHandler(e) {
        e.stopPropagation();
        const dropdown = this.closest('.dropdown-custom');
        if (dropdown) {
            dropdown.classList.toggle('open');
        }
    }

    function closeDropdownHandler(e) {
        const dropdowns = document.querySelectorAll('.dropdown-custom.open');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }

    // ===== Render Week Content =====
    window.renderWeekContent = function (grade, quarter, subject, week) {
    const container = document.getElementById('contentItemsContainer');
    if (!container) return;

    const contentKey = `${grade}-${quarter}-${subject}-${week}`;
    const content = state.weekContent[contentKey] || {};
    const items = window.getWeekContentItems(content);
    const hasContent = items.length > 0;

    // Action URLs
    const materialsUrl = `/teacher/content-library/${grade}/${quarter}/${subject}/${week}/materials/create`;
    const preUrl = `/teacher/content-library/${grade}/${quarter}/${subject}/${week}/pre-assessment/create`;
    const postUrl = `/teacher/content-library/${grade}/${quarter}/${subject}/${week}/post-assessment/create`;
    const interventionMaterialsUrl = `/teacher/content-library/${grade}/${quarter}/${subject}/${week}/intervention/materials/create`;
    const interventionVideosUrl = `/teacher/content-library/${grade}/${quarter}/${subject}/${week}/intervention/videos/create`;
    const interventionQuizUrl = `/teacher/content-library/${grade}/${quarter}/${subject}/${week}/intervention/quiz/create`;

    // ─── Build header & actions ────────────────────────────────
    let html = `
        <div class="week-content-page">
            <div class="week-header">
                <i class="fas fa-folder-open"></i> ${subject} - ${week}
            </div>
            <div class="week-subheader">Manage your lesson content</div>

            <div class="week-actions-row" style="margin-top: 15px; margin-bottom: 20px;">
                <a href="${materialsUrl}" class="btn add-content-main-btn">
                    <i class="fas fa-book-open"></i> Add Learning Materials
                </a>
                <a href="${preUrl}" class="btn add-content-main-btn">
                    <i class="fas fa-clipboard"></i> Add Pre-Assessment
                </a>
                <a href="${postUrl}" class="btn add-content-main-btn">
                    <i class="fas fa-clipboard-check"></i> Add Post-Assessment
                </a>

                <div class="dropdown-custom" style="display:inline-block; position:relative;">
                    <button class="btn add-content-main-btn dropdown-toggle-custom" type="button" id="interventionDropdownBtn">
                        <i class="fas fa-graduation-cap"></i> Add Learning Intervention
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <ul class="dropdown-menu-custom" id="interventionDropdownMenu">
                        <li><a href="${interventionMaterialsUrl}"><i class="fas fa-book-reader"></i> Add Materials</a></li>
                        <li><a href="${interventionVideosUrl}"><i class="fas fa-video"></i> Add Videos</a></li>
                        <li><a href="${interventionQuizUrl}"><i class="fas fa-question-circle"></i> Add Quiz</a></li>
                    </ul>
                </div>
            </div>
    `;

    // ─── Content area ──────────────────────────────────────────
    if (state.isLoading) {
        // Show loading spinner
        html += `
            <div class="loading-spinner-wrapper">
                <div class="spinner"></div>
                <span class="loading-text">Loading content...</span>
            </div>
        `;
    } else if (!hasContent) {
        // Show empty state
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
        // Show actual content cards
        html += `<div class="content-and-package-wrap">`;
        html += `<div class="content-items-grid">`;
        items.forEach(item => {
            html += window.renderContentItemCard(item);
        });
        html += `</div>`;

        // Package rail if all required components exist
        if (typeof window.hasAllRequiredLearningComponents === 'function' &&
            window.hasAllRequiredLearningComponents(content)) {
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

    html += `</div>`; // .week-content-page

    container.innerHTML = html;
    setupInterventionDropdown();
};
    window.fetchReleasesForWeek = async function (grade, quarter, subject, week) {
        const url = `/teacher/content-library/releases/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                return data.releases; // { content_type: { content_id: [ release objects ] } }
            }
        }
        return {};
    };
    // ===== Content menu =====
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

    // ===== Parse content ref =====
    window.parseContentRef = function (ref) {
        if (ref.startsWith('learningMaterial-')) {
            return { itemType: 'learningMaterial', index: parseInt(ref.split('-')[1], 10) };
        }
        if (ref.startsWith('interventionMaterial-')) {
            return { itemType: 'interventionMaterial', index: parseInt(ref.split('-')[1], 10) };
        }
        if (ref.startsWith('interventionVideo-')) {
            return { itemType: 'interventionVideo', index: parseInt(ref.split('-')[1], 10) };
        }
        if (ref.startsWith('interventionQuiz-')) {
            return { itemType: 'interventionQuiz', index: parseInt(ref.split('-')[1], 10) };
        }
        if (ref.startsWith('intervention-')) {
            return { itemType: 'intervention', index: null };
        }
        return { itemType: ref, index: null };
    };

    window.getContentItemByRef = function (ref) {
        const key = window.getCurrentContentKey();
        if (!key) return null;
        const content = state.weekContent[key] || {};
        const parsed = window.parseContentRef(ref);

        // Learning Materials
        if (parsed.itemType === 'learningMaterial') {
            const item = content.learningMaterials && content.learningMaterials[parsed.index];
            if (item) {
                return { ...item, itemType: parsed.itemType, index: parsed.index };
            }
        }

        // Pre-Assessment
        if (parsed.itemType === 'preAssessment' && content.preAssessment) {
            return { ...content.preAssessment, itemType: parsed.itemType, index: null };
        }

        // Post-Assessment
        if (parsed.itemType === 'postAssessment' && content.postAssessment) {
            return { ...content.postAssessment, itemType: parsed.itemType, index: null };
        }

        // Intervention subtypes
        if (parsed.itemType.startsWith('intervention')) {
            const inter = content.intervention;
            if (!inter) return null;

            if (parsed.itemType === 'interventionMaterial' && inter.materials && inter.materials[parsed.index]) {
                const item = inter.materials[parsed.index];
                return { ...item, itemType: parsed.itemType, index: parsed.index, serverId: item.id };
            }
            if (parsed.itemType === 'interventionVideo' && inter.videos && inter.videos[parsed.index]) {
                const item = inter.videos[parsed.index];
                return { ...item, itemType: parsed.itemType, index: parsed.index, serverId: item.id };
            }
            if (parsed.itemType === 'interventionQuiz' && inter.quizzes && inter.quizzes[parsed.index]) {
                const item = inter.quizzes[parsed.index];
                return { ...item, itemType: parsed.itemType, index: parsed.index, serverId: item.id };
            }
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

        const id = item.serverId || item.id;
        if (!id) {
            alert('Content ID not found.');
            return;
        }

        const grade = state.currentGrade;
        const quarter = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        const type = item.itemType;

        const url = `/teacher/content-library/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}/view/${type}/${id}`;
        window.location.href = url;
    };

    window.openEditContent = function (event, ref) {
        event.stopPropagation();
        window.closeAllContentMenus();

        const item = window.getContentItemByRef(ref);
        if (!item) return;

        const id = item.serverId || item.id;
        if (!id) {
            alert('Content ID not found.');
            return;
        }

        const grade = state.currentGrade;
        const quarter = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        const type = item.itemType;

        const url = `/teacher/content-library/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}/edit/${type}/${id}`;
        window.location.href = url;
    };

    // ===== Learning Package =====
    window.renderAssignedClassCheckboxes = function (availableClasses, selectedClassIds) {
        const wrap = document.getElementById('lpAssignedClasses');
        if (!wrap) return;

        if (!availableClasses || availableClasses.length === 0) {
            wrap.innerHTML = '<p style="margin:0;font-size:13px;color:#888;">No classes available for this grade.</p>';
            return;
        }

        const selected = new Set(selectedClassIds || []);
        wrap.innerHTML = availableClasses.map(cls => `
            <label>
                <input type="checkbox" name="lpAssignedClass" value="${cls.id}" ${selected.has(cls.id) ? 'checked' : ''}>
                <span>${window.escapeHtml(cls.name)}</span>
            </label>
        `).join('');
    };

    window.openLearningPackageModal = function () {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        if (typeof window.hasAllRequiredLearningComponents === 'function' && !window.hasAllRequiredLearningComponents(content)) {
            alert('Please add all required components first:\n- Learning Materials\n- Pre-Assessment\n- Post-Assessment\n- Learning Intervention with at least one Mini Quiz');
            return;
        }

        const grade = state.currentGrade;
        const term = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        const url = `/teacher/content-library/package/${encodeURIComponent(grade)}/${encodeURIComponent(term)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pkg = data.package || {};
                    document.getElementById('lpReleaseDate').value = pkg.release_date || '';
                    document.getElementById('lpDueDate').value = pkg.due_date || '';
                    document.getElementById('lpStatus').value = pkg.status || 'draft';

                    const availableClasses = data.available_classes || [];
                    const assignedClassIds = pkg.assigned_class_ids || [];
                    window.renderAssignedClassCheckboxes(availableClasses, assignedClassIds);

                    window.openModal('learningPackageModal');
                } else {
                    alert('Failed to load package data.');
                }
            })
            .catch(() => alert('Network error.'));
    };

    window.buildLearningPackageFromForm = function () {
        const assigned = [];
        document.querySelectorAll('input[name="lpAssignedClass"]:checked').forEach(cb => assigned.push(parseInt(cb.value)));
        return {
            assigned_classes: assigned,
            releaseDate: document.getElementById('lpReleaseDate').value,
            dueDate: document.getElementById('lpDueDate').value,
            status: document.getElementById('lpStatus').value
        };
    };

    window.saveLearningPackageDraft = function () {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        if (typeof window.hasAllRequiredLearningComponents === 'function' && !window.hasAllRequiredLearningComponents(content)) {
            alert('All required components must be present before saving release settings.');
            return;
        }
        const data = window.buildLearningPackageFromForm();
        if (data.status === 'published') {
            alert('To publish, use the Publish button.');
            return;
        }
        if (data.status === 'archived' && !confirm('Archive this package? It will be hidden from students.')) return;

        const grade = state.currentGrade;
        const term = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        const url = `/teacher/content-library/${encodeURIComponent(grade)}/${encodeURIComponent(term)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}/package`;
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
                    alert('Draft saved.');
                    window.closeModal('learningPackageModal');
                    window.renderWeekContent(state.currentGrade, state.currentQuarter, state.currentSubject, state.currentWeek);
                } else {
                    alert('Save failed.');
                }
            })
            .catch(() => alert('Network error.'));
    };

    window.publishLearningPackage = function () {
        const key = window.getCurrentContentKey();
        if (!key) return;
        const content = state.weekContent[key] || {};
        if (typeof window.hasAllRequiredLearningComponents === 'function' && !window.hasAllRequiredLearningComponents(content)) {
            alert('All required components must be present before publishing.');
            return;
        }
        const data = window.buildLearningPackageFromForm();
        if (!data.releaseDate) { alert('Please set a Release Date.'); return; }
        if (!data.assigned_classes || !data.assigned_classes.length) { alert('Select at least one assigned class.'); return; }

        const grade = state.currentGrade;
        const term = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        const url = `/teacher/content-library/${encodeURIComponent(grade)}/${encodeURIComponent(term)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}/package/publish`;
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
                    alert('Published. Students will see it on/after the release date.');
                    window.closeModal('learningPackageModal');
                    window.renderWeekContent(state.currentGrade, state.currentQuarter, state.currentSubject, state.currentWeek);
                } else {
                    alert('Publish failed.');
                }
            })
            .catch(() => alert('Network error.'));
    };

    window.loadContentFromServer = async function () {
        // 1. Read state values
        const grade = state.currentGrade;
        const quarter = state.currentQuarter;
        const subject = state.currentSubject;
        const week = state.currentWeek;
        if (!grade || !quarter || !subject || !week) return;

        const key = `${grade}-${quarter}-${subject}-${week}`;
        if (!state.weekContent[key]) state.weekContent[key] = {};


        state.isLoading = true;
        window.renderWeekContent(grade, quarter, subject, week);

        try {
            const releases = await window.fetchReleasesForWeek(grade, quarter, subject, week);
            state.weekContent[key].releases = releases;
            // 3. Fetch learning materials
            const matUrl = `/teacher/content-library/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const matRes = await fetch(matUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (matRes.ok) {
                const matData = await matRes.json();
                if (matData.success) {
                    state.weekContent[key].learningMaterials = matData.items.map(item => ({
                        fileName: item.file_name,
                        title: item.title,
                        description: item.description,
                        dateUploaded: item.uploaded_at,
                        serverId: item.id,
                        fileUrl: item.file_url
                    }));
                } else {
                    state.weekContent[key].learningMaterials = [];
                }
            } else {
                state.weekContent[key].learningMaterials = [];
            }

            // 4. Fetch pre‑assessments
            const preUrl = `/teacher/content-library/pre-assessment/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const preRes = await fetch(preUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (preRes.ok) {
                const preData = await preRes.json();
                if (preData.success && preData.preAssessments && preData.preAssessments.length) {
                    const latest = preData.preAssessments[0];
                    state.weekContent[key].preAssessment = {
                        title: 'Pre-Assessment',
                        timer: latest.timer || '00:00:00',
                        // dueDate: latest.due_date || '',
                        fileName: latest.file_name || '',
                        examType: latest.exam_type,
                        inputMethod: latest.input_method,
                        shuffleQuestions: latest.shuffle_questions || false,
                        shuffleChoices: latest.shuffle_choices || false,
                        questions: latest.questions,
                        dateUploaded: latest.created_at,
                        serverId: latest.id,
                        // status: latest.status || 'open',
                    };
                } else {
                    delete state.weekContent[key].preAssessment;
                }
            } else {
                delete state.weekContent[key].preAssessment;
            }

            // 5. Fetch post‑assessments (add when endpoint exists)
            // 5. Fetch post‑assessments
            const postUrl = `/teacher/content-library/post-assessment/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const postRes = await fetch(postUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (postRes.ok) {
                const postData = await postRes.json();
                if (postData.success && postData.postAssessments && postData.postAssessments.length) {
                    const latest = postData.postAssessments[0];
                    state.weekContent[key].postAssessment = {
                        title: 'Post-Assessment',
                        timer: latest.timer || '00:00:00',
                        fileName: latest.file_name || '',
                        examType: latest.exam_type,
                        inputMethod: latest.input_method,
                        shuffleQuestions: latest.shuffle_questions || false,
                        shuffleChoices: latest.shuffle_choices || false,
                        questions: latest.questions,
                        dateUploaded: latest.created_at,
                        serverId: latest.id,
                    };
                } else {
                    delete state.weekContent[key].postAssessment;
                }
            } else {
                delete state.weekContent[key].postAssessment;
            }

            // 6. Fetch intervention materials
            const intMatUrl = `/teacher/content-library/intervention/material/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const intMatRes = await fetch(intMatUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            let materials = [];
            if (intMatRes.ok) {
                const data = await intMatRes.json();
                if (data.success) {
                    materials = data.materials || [];
                }
            }

            // 7. Fetch intervention videos
            const intVidUrl = `/teacher/content-library/intervention/video/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const intVidRes = await fetch(intVidUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            let videos = [];
            if (intVidRes.ok) {
                const data = await intVidRes.json();
                if (data.success) {
                    videos = data.videos || [];
                }
            }

            // 8. Fetch intervention quizzes
            const intQuizUrl = `/teacher/content-library/intervention/quiz/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}`;
            const intQuizRes = await fetch(intQuizUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            let quizzes = [];
            if (intQuizRes.ok) {
                const data = await intQuizRes.json();
                if (data.success) {
                    quizzes = data.quizzes || [];
                }
            }

            // Store intervention data
            state.weekContent[key].intervention = {
                materials: materials,
                videos: videos,
                quizzes: quizzes
            };

            // Save to localStorage
            localStorage.setItem('weekContent', JSON.stringify(state.weekContent));

            // Render
            window.renderWeekContent(grade, quarter, subject, week);

        } catch (e) {
            console.error('Error loading content from server:', e);
            // Fallback to localStorage
            const saved = localStorage.getItem('weekContent');
            if (saved) {
                try {
                    const parsed = JSON.parse(saved);
                    if (parsed[key]) {
                        state.weekContent[key] = parsed[key];
                    }
                } catch (err) { }
            }
            window.renderWeekContent(grade, quarter, subject, week);
        } finally {

            state.isLoading = false;
            window.renderWeekContent(grade, quarter, subject, week);
        }
    };
    window.loadContentForWeek = function (path) {
        state.currentGrade = path.grade;
        state.currentQuarter = path.term;
        state.currentSubject = path.subject;
        state.currentWeek = path.week;
        state.currentView = 'week-content';

        const saved = localStorage.getItem('weekContent');
        if (saved) {
            try { state.weekContent = JSON.parse(saved); } catch (e) { /* ignore */ }
        }

        const key = window.getCurrentContentKey();
        if (key && !state.weekContent[key]) state.weekContent[key] = {};

        window.loadContentFromServer();
    };

    window.formatQuestionsPreview = function (questions, examType) {
        if (!questions || !questions.length) return 'No questions.';
        const total = questions.length;
        let typeLabel = examType || 'mixed';
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

    // ===== Initialize user info =====
    // window.initializeUserInfo = function () {
    //     let user = null;
    //     if (window.user && window.user.fullName) {
    //         user = window.user;
    //     }
    //     if (!user) {
    //         const stored = localStorage.getItem('userData');
    //         if (stored) {
    //             try {
    //                 user = JSON.parse(stored);
    //             } catch (e) { }
    //         }
    //     }
    //     if (!user) return;

    //     const fullName = user.fullName || user.name || user.displayName || 'User';
    //     const initials = fullName.split(' ')
    //         .map(name => name.charAt(0))
    //         .join('')
    //         .toUpperCase()
    //         .substring(0, 2);

    //     const nameEl = document.getElementById('userName') || document.querySelector('.user-name');
    //     const avatarEl = document.getElementById('userAvatar') || document.querySelector('.user-avatar');

    //     if (nameEl) nameEl.textContent = fullName;
    //     if (avatarEl) avatarEl.textContent = initials;
    // };

    window.__contentState = state;
    // window.initializeUserInfo();

    if (window.contentPath) {
        window.loadContentForWeek(window.contentPath);
    }

})();