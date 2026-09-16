// ============ LEARNING INTERVENTION ============
let currentVideoType = 'link';
let currentInterventionLevel = 'basic';
let currentInterventionContent = 'material';
let currentQuizMethod = 'upload';
let interventionMaterials = { basic: [], standard: [], advanced: [] };
let interventionVideos = { basic: [], standard: [], advanced: [] };
let interventionQuizzes = { basic: [], standard: [], advanced: [] };
let quizUploadData = { questions: [], fileName: '', examType: '' };

// -------- Core helpers --------
function getWeekContent() {
    return window.__contentState?.weekContent || {};
}
function getCurrentGrade() { return window.__contentState?.currentGrade || ''; }
function getCurrentQuarter() { return window.__contentState?.currentQuarter || ''; }
function getCurrentSubject() { return window.__contentState?.currentSubject || ''; }
function getCurrentWeek() { return window.__contentState?.currentWeek || ''; }
function getCurrentContentKey() {
    const g = getCurrentGrade(), q = getCurrentQuarter(), s = getCurrentSubject(), w = getCurrentWeek();
    if (!g || !q || !s || !w) return null;
    return `${g}-${q}-${s}-${w}`;
}

// -------- Safe calls for assessment functions --------
function safeRenderHint(prefix, examType) {
    if (typeof window.renderAssessmentUploadFormatHint === 'function') {
        window.renderAssessmentUploadFormatHint(prefix, examType);
    } else {
        const hintEl = document.getElementById(`${prefix}UploadFormatHint`);
        if (hintEl) hintEl.innerHTML = `<p style="color:#888;">Upload an Excel file with the required columns.</p>`;
    }
}
function safeUpdateRandomization(prefix, examType) {
    if (typeof window.updateAssessmentRandomizationOptions === 'function') {
        window.updateAssessmentRandomizationOptions(prefix, examType);
    }
}

// ============ HYDRATE FROM WEEK CONTENT ============
window.hydrateInterventionFromCurrentWeek = function () {
    const emptyBuckets = () => ({ basic: [], standard: [], advanced: [] });
    const ck = getCurrentContentKey();
    const weekContent = getWeekContent();
    const inv = ck && weekContent[ck] && weekContent[ck].intervention;
    if (inv && typeof inv === 'object') {
        interventionMaterials = inv.materials ? JSON.parse(JSON.stringify(inv.materials)) : emptyBuckets();
        interventionVideos = inv.videos ? JSON.parse(JSON.stringify(inv.videos)) : emptyBuckets();
        interventionQuizzes = inv.quizzes ? JSON.parse(JSON.stringify(inv.quizzes)) : emptyBuckets();
    } else {
        interventionMaterials = emptyBuckets();
        interventionVideos = emptyBuckets();
        interventionQuizzes = emptyBuckets();
    }
};

// ============ INTERVENTION UI ============
window.switchContent = function (type) {
    currentInterventionContent = type;
    document.getElementById('materialContent').classList.remove('active');
    document.getElementById('videoContent').classList.remove('active');
    document.getElementById('quizContent').classList.remove('active');
    document.querySelectorAll('.intervention-btn-item').forEach(btn => btn.classList.remove('active'));
    if (type === 'material') {
        document.getElementById('materialContent').classList.add('active');
        document.querySelectorAll('.intervention-btn-item')[0].classList.add('active');
    } else if (type === 'video') {
        document.getElementById('videoContent').classList.add('active');
        document.querySelectorAll('.intervention-btn-item')[1].classList.add('active');
    } else if (type === 'quiz') {
        document.getElementById('quizContent').classList.add('active');
        document.querySelectorAll('.intervention-btn-item')[2].classList.add('active');
    }
};
window.loadInterventionLevel = function () {
    currentInterventionLevel = document.getElementById('interventionLevel').value;
    updateInterventionDisplay();
};
window.updateInterventionDisplay = function () {
    displayMaterials();
    displayVideos();
    displayQuizzes();
};
window.selectVideoType = function (type, btn) {
    currentVideoType = type;
    document.querySelectorAll('#videoContent .option-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('videoLinkInput').style.display = type === 'link' ? 'block' : 'none';
    document.getElementById('videoFileInput').style.display = type === 'file' ? 'block' : 'none';
};

// ============ ADD ITEMS ============
window.addInterventionMaterial = function () {
    const fileInput = document.getElementById('interventionMaterialFile');
    if (!fileInput.files || fileInput.files.length === 0) { alert('Please select a file'); return; }
    const fileName = fileInput.files[0].name;
    interventionMaterials[currentInterventionLevel].push(fileName);
    fileInput.value = '';
    displayMaterials();
};
window.addInterventionVideo = function () {
    let videoInfo;
    if (currentVideoType === 'link') {
        const link = document.getElementById('videoLink').value.trim();
        if (!link) { alert('Please enter a video link'); return; }
        videoInfo = link;
        document.getElementById('videoLink').value = '';
    } else {
        const fileInput = document.getElementById('interventionVideoFile');
        if (!fileInput.files || fileInput.files.length === 0) { alert('Please select a video file'); return; }
        videoInfo = fileInput.files[0].name;
        fileInput.value = '';
    }
    interventionVideos[currentInterventionLevel].push(videoInfo);
    displayVideos();
};
window.generateQuizQuestions = function () {
    const numQuestions = parseInt(document.getElementById('quizNumQuestions').value) || 3;
    const examType = document.getElementById('quizExamType').value || 'multipleChoice';
    const isMixed = (examType === 'mixed');
    document.getElementById('quizSetupPhase').style.display = 'none';
    document.getElementById('quizQuestionsPhase').style.display = 'block';
    const container = document.getElementById('interventionQuestionsContainer');
    if (isMixed) {
        container.innerHTML = generateMixedQuizQuestionItems('quiz', numQuestions, 0);
    } else {
        const numChoices = parseInt(document.getElementById('quizNumChoices').value) || 4;
        let html = '';
        for (let i = 1; i <= numQuestions; i++) {
            html += buildQuizQuestionHtml(i, examType, numChoices);
        }
        container.innerHTML = html;
    }
};
window.addInterventionQuiz = function () {
    if (!validateQuizBeforeAdd()) return;
    const payload = buildQuizPayload();
    const hours = document.getElementById('quizHours').value || 0;
    const minutes = document.getElementById('quizMinutes').value || 0;
    const seconds = document.getElementById('quizSeconds').value || 0;
    const quizInfo = {
        questions: payload.examType === 'matchingType' ? (payload.questions[0]?.pairs?.length || payload.questions.length) : payload.questions.length,
        questionItems: payload.questions,
        examType: payload.examType,
        examTypeLabel: getExamTypeLabel(payload.examType),
        inputMethod: payload.inputMethod,
        shuffleQuestions: payload.shuffleQuestions,
        shuffleChoices: payload.shuffleChoices,
        shuffle: payload.shuffleQuestions,
        fileName: payload.fileName,
        timer: `${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`
    };
    interventionQuizzes[currentInterventionLevel].push(quizInfo);
    displayQuizzes();
    document.getElementById('quizSetupPhase').style.display = 'block';
    document.getElementById('quizQuestionsPhase').style.display = 'none';
    document.getElementById('interventionQuestionsContainer').innerHTML = '';
    document.getElementById('quizNumQuestions').value = 3;
    document.getElementById('quizNumChoices').value = 4;
    document.getElementById('quizExamType').value = 'multipleChoice';
    document.getElementById('quizShuffleQuestions').checked = false;
    document.getElementById('quizShuffleChoices').checked = false;
    currentQuizMethod = 'upload';
    switchQuizMethod('upload');
    clearQuizUploadState();
};

// ============ DISPLAY LISTS (TABLES) ============
window.displayMaterials = function () {
    const tbody = document.getElementById('materialsTableBody');
    const materials = interventionMaterials[currentInterventionLevel] || [];
    if (materials.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No materials added yet</td></tr>';
        return;
    }
    let html = '';
    materials.forEach((material, index) => {
        html += `
            <tr>
                <td style="padding:8px;">${index+1}</td>
                <td style="padding:8px;">${window.escapeHtml(material)}</td>
                <td style="padding:8px; text-align:right;">
                    <button class="btn-edit btn-primary" type="button" onclick="openEditMaterial(${index})" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="btn-remove" type="button" onclick="removeInterventionItem('materials', ${index})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
};
window.displayVideos = function () {
    const tbody = document.getElementById('videosTableBody');
    const videos = interventionVideos[currentInterventionLevel] || [];
    if (videos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No videos added yet</td></tr>';
        return;
    }
    let html = '';
    videos.forEach((video, index) => {
        const isLink = video.includes('http') || video.includes('youtube');
        html += `
            <tr>
                <td style="padding:8px;">${index+1}</td>
                <td style="padding:8px;">${window.escapeHtml(video)}</td>
                <td style="padding:8px; text-align:right;">
                    <button class="btn-edit" type="button" onclick="openEditVideo(${index})" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="btn-remove" type="button" onclick="removeInterventionItem('videos', ${index})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
};
window.displayQuizzes = function () {
    const tbody = document.getElementById('quizzesTableBody');
    const quizzes = interventionQuizzes[currentInterventionLevel] || [];
    if (quizzes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#999;padding:20px;">No quizzes added yet</td></tr>';
        return;
    }
    let html = '';
    quizzes.forEach((quiz, index) => {
        const typeLabel = quiz.examTypeLabel || getExamTypeLabel(quiz.examType) || 'Multiple Choice';
        const count = quiz.examType === 'matchingType' ? (quiz.questionItems?.[0]?.pairs?.length || quiz.questions || 0) : (quiz.questionItems?.length || quiz.questions || 0);
        const countLabel = quiz.examType === 'matchingType' ? 'pairs' : 'questions';
        const methodLabel = quiz.inputMethod === 'manual' ? 'Manual' : 'Upload';
        html += `
            <tr>
                <td style="padding:8px;">${index+1}</td>
                <td style="padding:8px;">${count} ${countLabel}, ${typeLabel}, ${methodLabel} - ${quiz.timer}${quiz.shuffleQuestions ? ' • Shuffled' : ''}</td>
                <td style="padding:8px; text-align:right;">
                    <button class="btn-edit" type="button" onclick="openEditQuiz(${index})" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="btn-remove" type="button" onclick="removeInterventionItem('quizzes', ${index})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
};

// ============ EDIT MATERIAL ============
window.openEditMaterial = function (index) {
    const materials = interventionMaterials[currentInterventionLevel];
    if (!materials || !materials[index]) return;
    document.getElementById('editMaterialIndex').value = index;
    document.getElementById('editMaterialLevel').value = currentInterventionLevel;
    document.getElementById('editMaterialFileName').value = materials[index];
    openModal('editMaterialModal');
};
window.saveEditedMaterial = function () {
    const index = parseInt(document.getElementById('editMaterialIndex').value);
    const level = document.getElementById('editMaterialLevel').value;
    const newName = document.getElementById('editMaterialFileName').value.trim();
    if (!newName) { alert('File name is required.'); return; }
    if (interventionMaterials[level] && interventionMaterials[level][index] !== undefined) {
        interventionMaterials[level][index] = newName;
        displayMaterials();
        closeModal('editMaterialModal');
    }
};

// ============ EDIT VIDEO ============
window.openEditVideo = function (index) {
    const videos = interventionVideos[currentInterventionLevel];
    if (!videos || !videos[index]) return;
    const video = videos[index];
    const isLink = video.includes('http') || video.includes('youtube') || video.includes('://');
    document.getElementById('editVideoIndex').value = index;
    document.getElementById('editVideoLevel').value = currentInterventionLevel;
    document.getElementById('editVideoType').value = isLink ? 'link' : 'file';
    toggleEditVideoFields(isLink ? 'link' : 'file');
    document.getElementById('editVideoUrl').value = isLink ? video : '';
    document.getElementById('editVideoFileName').value = isLink ? '' : video;
    openModal('editVideoModal');
};
window.toggleEditVideoFields = function (type) {
    document.getElementById('editVideoLinkGroup').style.display = type === 'link' ? 'block' : 'none';
    document.getElementById('editVideoFileGroup').style.display = type === 'file' ? 'block' : 'none';
};
window.saveEditedVideo = function () {
    const index = parseInt(document.getElementById('editVideoIndex').value);
    const level = document.getElementById('editVideoLevel').value;
    const type = document.getElementById('editVideoType').value;
    let newValue = type === 'link'
        ? document.getElementById('editVideoUrl').value.trim()
        : document.getElementById('editVideoFileName').value.trim();
    if (!newValue) { alert('Video details are required.'); return; }
    if (interventionVideos[level] && interventionVideos[level][index] !== undefined) {
        interventionVideos[level][index] = newValue;
        displayVideos();
        closeModal('editVideoModal');
    }
};
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('editVideoType');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            toggleEditVideoFields(this.value);
        });
    }
});

// ============ EDIT QUIZ ============
window.openEditQuiz = function (index) {
    const quizzes = interventionQuizzes[currentInterventionLevel];
    if (!quizzes || !quizzes[index]) return;
    const quiz = quizzes[index];
    document.getElementById('editQuizIndex').value = index;
    document.getElementById('editQuizLevel').value = currentInterventionLevel;
    // Populate fields
    document.getElementById('editQuizExamType').value = quiz.examType || 'multipleChoice';
    document.getElementById('editQuizShuffleQuestions').checked = !!quiz.shuffleQuestions;
    document.getElementById('editQuizShuffleChoices').checked = !!quiz.shuffleChoices;
    const timerParts = (quiz.timer || '00:00:00').split(':');
    document.getElementById('editQuizHours').value = parseInt(timerParts[0]) || 0;
    document.getElementById('editQuizMinutes').value = parseInt(timerParts[1]) || 0;
    document.getElementById('editQuizSeconds').value = parseInt(timerParts[2]) || 0;
    // Render questions
    const container = document.getElementById('editQuizQuestionsContainer');
    container.innerHTML = '';
    if (quiz.questionItems && quiz.questionItems.length) {
        const isMixed = quiz.examType === 'mixed';
        if (isMixed) {
            container.innerHTML = generateMixedQuizQuestionItems('editQuiz', quiz.questionItems.length, 0);
        } else {
            let html = '';
            for (let i = 0; i < quiz.questionItems.length; i++) {
                html += buildQuizQuestionHtml(i+1, quiz.examType, 4, 'editQuiz');
            }
            container.innerHTML = html;
        }
        // Populate data
        const questionItems = container.querySelectorAll('.question-item');
        quiz.questionItems.forEach((q, idx) => {
            const item = questionItems[idx];
            if (!item) return;
            const qInput = item.querySelector('.question-input');
            if (qInput) qInput.value = q.question || '';
            const typeSelect = item.querySelector('.question-type-select');
            if (typeSelect) {
                typeSelect.value = q.type || 'multipleChoice';
                window.onQuestionTypeChange(typeSelect, 'editQuiz');
            }
            // Fill choices etc.
            if (q.type === 'multipleChoice' && q.choices) {
                const choiceInputs = item.querySelectorAll('.choice-input');
                q.choices.forEach((choice, ci) => { if (choiceInputs[ci]) choiceInputs[ci].value = choice; });
                const radios = item.querySelectorAll('.correct-choice-radio');
                const correctIndex = q.choices.indexOf(q.correctAnswer);
                if (radios[correctIndex]) { radios[correctIndex].checked = true; window.updateCorrectChoiceIndicator(radios[correctIndex]); }
            } else if (q.type === 'trueFalse') {
                const answerSelect = item.querySelector('.manual-answer-input');
                if (answerSelect) answerSelect.value = q.correctAnswer || '';
            } else if (q.type === 'matchingType' && q.pairs) {
                const pairsContainer = item.querySelector('.matching-pairs');
                const existingPairs = pairsContainer.querySelectorAll('.matching-pair');
                existingPairs.forEach((p, idx) => { if (idx > 0) p.remove(); });
                const firstPair = pairsContainer.querySelector('.matching-pair');
                if (firstPair && q.pairs.length) {
                    const leftInput = firstPair.querySelector('.matching-left-input');
                    const rightInput = firstPair.querySelector('.matching-right-input');
                    if (leftInput) leftInput.value = q.pairs[0].question || q.pairs[0].left || '';
                    if (rightInput) rightInput.value = q.pairs[0].answer || q.pairs[0].right || '';
                }
                for (let i = 1; i < q.pairs.length; i++) {
                    const pair = q.pairs[i];
                    const newPair = document.createElement('div');
                    newPair.className = 'matching-pair';
                    const pairNum = i+1;
                    newPair.dataset.pair = pairNum;
                    const qNum = idx+1;
                    newPair.innerHTML = `
                        <input type="text" class="matching-left-input" placeholder="Left item" data-q="${qNum}" data-pair="${pairNum}" value="${window.escapeHtml(pair.question || pair.left || '')}">
                        <span>↔</span>
                        <input type="text" class="matching-right-input" placeholder="Right item" data-q="${qNum}" data-pair="${pairNum}" value="${window.escapeHtml(pair.answer || pair.right || '')}">
                        <button type="button" class="remove-pair" onclick="window.removeMatchingPair(this)">✕</button>
                    `;
                    pairsContainer.appendChild(newPair);
                }
            }
        });
    } else {
        container.innerHTML = '<p style="text-align:center;color:#888;padding:20px;">No questions to edit.</p>';
    }
    openModal('editQuizModal');
};
window.saveEditedQuiz = function () {
    const index = parseInt(document.getElementById('editQuizIndex').value);
    const level = document.getElementById('editQuizLevel').value;
    const examType = document.getElementById('editQuizExamType').value;
    const shuffleQuestions = document.getElementById('editQuizShuffleQuestions').checked;
    const shuffleChoices = document.getElementById('editQuizShuffleChoices').checked;
    const hours = document.getElementById('editQuizHours').value || 0;
    const minutes = document.getElementById('editQuizMinutes').value || 0;
    const seconds = document.getElementById('editQuizSeconds').value || 0;
    const timer = `${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
    const container = document.getElementById('editQuizQuestionsContainer');
    const questionItems = container.querySelectorAll('.question-item');
    const questions = [];
    questionItems.forEach(item => {
        let typeSelect = item.querySelector('.question-type-select');
        let type = typeSelect ? typeSelect.value : examType;
        if (type === 'mixed') type = 'multipleChoice';
        const questionText = item.querySelector('.question-input')?.value?.trim();
        if (!questionText) return;
        let questionData = { type, question: questionText };
        if (type === 'multipleChoice') {
            const choiceInputs = item.querySelectorAll('.choice-input');
            const choices = Array.from(choiceInputs).map(inp => inp.value.trim()).filter(Boolean);
            if (choices.length < 2) return;
            const correctRadio = item.querySelector('.correct-choice-radio:checked');
            let correctAnswer = '';
            if (correctRadio) {
                const idx = parseInt(correctRadio.value, 10);
                correctAnswer = choices[idx] || '';
            }
            if (!correctAnswer) return;
            questionData.choices = choices;
            questionData.correctAnswer = correctAnswer;
        } else if (type === 'trueFalse') {
            const answerSelect = item.querySelector('.manual-answer-input');
            const correctAnswer = answerSelect ? answerSelect.value : '';
            if (!correctAnswer) return;
            questionData.choices = ['True', 'False'];
            questionData.correctAnswer = correctAnswer;
        } else if (type === 'matchingType') {
            const pairs = [];
            const pairElements = item.querySelectorAll('.matching-pair');
            pairElements.forEach(pair => {
                const left = pair.querySelector('.matching-left-input')?.value?.trim() || '';
                const right = pair.querySelector('.matching-right-input')?.value?.trim() || '';
                if (left && right) pairs.push({ question: left, answer: right });
            });
            if (pairs.length === 0) return;
            questionData.pairs = pairs;
        }
        questions.push(questionData);
    });
    if (!questions.length) { alert('Please complete at least one question.'); return; }
    const levelQuizzes = interventionQuizzes[level];
    if (levelQuizzes && levelQuizzes[index] !== undefined) {
        levelQuizzes[index] = {
            ...levelQuizzes[index],
            questionItems: questions,
            examType: examType,
            shuffleQuestions: shuffleQuestions,
            shuffleChoices: shuffleChoices,
            timer: timer,
            questions: questions.length,
            examTypeLabel: getExamTypeLabel(examType)
        };
        displayQuizzes();
        closeModal('editQuizModal');
        alert('Quiz updated. Save intervention to persist.');
    }
};

// ============ REMOVE ITEM ============
window.removeInterventionItem = function (type, index) {
    if (type === 'materials') {
        interventionMaterials[currentInterventionLevel].splice(index, 1);
        displayMaterials();
    } else if (type === 'videos') {
        interventionVideos[currentInterventionLevel].splice(index, 1);
        displayVideos();
    } else if (type === 'quizzes') {
        interventionQuizzes[currentInterventionLevel].splice(index, 1);
        displayQuizzes();
    }
};

// ============ RESET FORMS ============
window.resetInterventionForms = function () {
    currentInterventionLevel = 'basic';
    currentInterventionContent = 'material';
    currentQuizMethod = 'upload';
    document.getElementById('interventionLevel').value = 'basic';
    document.getElementById('quizNumQuestions').value = 3;
    document.getElementById('quizNumChoices').value = 4;
    document.getElementById('quizHours').value = 0;
    document.getElementById('quizMinutes').value = 0;
    document.getElementById('quizSeconds').value = 0;
    document.getElementById('videoLink').value = '';
    document.getElementById('interventionMaterialFile').value = '';
    document.getElementById('interventionVideoFile').value = '';
    document.getElementById('quizSetupPhase').style.display = 'block';
    document.getElementById('quizQuestionsPhase').style.display = 'none';
    document.getElementById('interventionQuestionsContainer').innerHTML = '';
    document.getElementById('quizExamType').value = 'multipleChoice';
    document.getElementById('quizShuffleQuestions').checked = false;
    document.getElementById('quizShuffleChoices').checked = false;
    clearQuizUploadState();
    switchQuizMethod('upload');
    switchContent('material');
    updateInterventionDisplay();
};

// ============ SAVE INTERVENTION ============
window.saveIntervention = async function () {
    const grade = getCurrentGrade();
    const quarter = getCurrentQuarter();
    const subject = getCurrentSubject();
    const week = getCurrentWeek();
    if (!grade || !quarter || !subject || !week) {
        alert('No week context found. Please navigate to a specific week.');
        return;
    }
    const levels = ['basic', 'standard', 'advanced'];
    // Ensure structure
    if (!interventionMaterials || typeof interventionMaterials !== 'object') {
        interventionMaterials = { basic: [], standard: [], advanced: [] };
    }
    ['basic','standard','advanced'].forEach(l => {
        if (!Array.isArray(interventionMaterials[l])) interventionMaterials[l] = [];
    });
    async function sendPart(endpoint, level, items, transformer, dataKey) {
        const safeItems = Array.isArray(items) ? items : [];
        const mappedItems = safeItems.map(transformer);
        const payload = {
            grade_level: grade,
            term: quarter,
            subject: subject,
            week: week,
            level: level,
        };
        payload[dataKey] = mappedItems;
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            const errText = await res.text();
            throw new Error(`Failed to save ${endpoint}: ${errText}`);
        }
        return res.json();
    }
    try {
        for (const level of levels) {
            const items = interventionMaterials[level] || [];
            await sendPart('/teacher/content-library/intervention/material', level, items, (f) => ({ file_name: f }), 'materials');
        }
        for (const level of levels) {
            const items = interventionVideos[level] || [];
            await sendPart('/teacher/content-library/intervention/video', level, items, (v) => ({
                video_type: v.includes('http') ? 'link' : 'file',
                video_url: v.includes('http') ? v : null,
                file_name: v.includes('http') ? null : v,
            }), 'videos');
        }
        for (const level of levels) {
            const items = interventionQuizzes[level] || [];
            await sendPart('/teacher/content-library/intervention/quiz', level, items, (q) => ({
                exam_type: q.examType,
                input_method: q.inputMethod,
                questions: JSON.stringify(q.questionItems || []),
                settings: JSON.stringify({
                    timer: q.timer,
                    shuffle_questions: q.shuffleQuestions || false,
                    shuffle_choices: q.shuffleChoices || false,
                }),
                file_name: q.fileName || null,
            }), 'quizzes');
        }
        // Local storage backup
        const ck = getCurrentContentKey();
        const weekContent = getWeekContent();
        if (!weekContent[ck]) weekContent[ck] = {};
        weekContent[ck].hasContent = true;
        weekContent[ck].intervention = {
            title: 'Learning Intervention',
            dateUploaded: new Date().toISOString(),
            fileName: getFirstInterventionFileName(),
            videoLink: getFirstInterventionVideoLink(),
            quizContent: buildInterventionQuizSummary(),
            materials: interventionMaterials,
            videos: interventionVideos,
            quizzes: interventionQuizzes
        };
        if (window.__contentState) window.__contentState.weekContent = weekContent;
        localStorage.setItem('weekContent', JSON.stringify(weekContent));
        closeModal('interventionModal');
        alert('Intervention saved successfully!');
        if (typeof window.renderWeekContent === 'function') {
            window.renderWeekContent(grade, quarter, subject, week);
        }
    } catch (err) {
        alert('Error saving intervention: ' + err.message);
        console.error(err);
    }
};

// ============ HELPERS ============
window.getInterventionStatsLabel = function (intervention) {
    const levels = ['basic','standard','advanced'];
    const totals = levels.reduce((acc, level) => {
        acc.materials += (intervention.materials?.[level] || []).length;
        acc.videos += (intervention.videos?.[level] || []).length;
        acc.quizzes += (intervention.quizzes?.[level] || []).length;
        return acc;
    }, { materials: 0, videos: 0, quizzes: 0 });
    return `Materials: ${totals.materials}, Videos: ${totals.videos}, Quizzes: ${totals.quizzes}`;
};
window.getFirstInterventionFileName = function () {
    const levels = ['basic','standard','advanced'];
    for (const level of levels) {
        const first = interventionMaterials[level]?.[0];
        if (first) return first;
    }
    return '';
};
window.getFirstInterventionVideoLink = function () {
    const levels = ['basic','standard','advanced'];
    for (const level of levels) {
        const first = interventionVideos[level]?.find(video => String(video).startsWith('http'));
        if (first) return first;
    }
    return '';
};
window.buildInterventionQuizSummary = function () {
    const levels = ['basic','standard','advanced'];
    const labels = levels.map(level => {
        const quizzes = interventionQuizzes[level] || [];
        if (!quizzes.length) return `${level}: none`;
        return `${level}: ${quizzes.map(q => {
            const count = q.questionItems?.length || q.questions || 0;
            const typeLabel = q.examTypeLabel || getExamTypeLabel(q.examType) || 'Multiple Choice';
            const shuffleLabel = q.shuffleQuestions ? '/Shuffled' : '';
            return `${count}Q/${q.timer}/${typeLabel}${shuffleLabel}`;
        }).join(', ')}`;
    });
    return labels.join(' | ');
};

// ============ QUIZ UPLOAD ============
window.clearQuizUploadState = function () {
    const fileEl = document.getElementById('quizFile');
    const nameEl = document.getElementById('quizFileName');
    const previewEl = document.getElementById('quizUploadPreview');
    const errorEl = document.getElementById('quizUploadError');
    if (fileEl) fileEl.value = '';
    if (nameEl) nameEl.textContent = '';
    if (previewEl) { previewEl.style.display = 'none'; previewEl.innerHTML = ''; }
    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    quizUploadData = { questions: [], fileName: '', examType: '' };
};
window.switchQuizMethod = function (method) {
    currentQuizMethod = method;
    document.getElementById('quiz-upload')?.classList.toggle('active', method === 'upload');
    document.getElementById('quiz-manual')?.classList.toggle('active', method === 'manual');
    document.querySelectorAll('#quizContent .input-method-tab').forEach((tab, index) => {
        tab.classList.toggle('active', (method === 'upload' && index === 0) || (method === 'manual' && index === 1));
    });
    updateQuizUploadVisibility();
};
window.updateQuizUploadVisibility = function () {
    const examType = document.getElementById('quizExamType')?.value || 'multipleChoice';
    safeRenderHint('quiz', examType);
    safeUpdateRandomization('quiz', examType);
    const numQuestionsGroup = document.getElementById('quizNumQuestionsGroup');
    const numChoicesGroup = document.getElementById('quizNumChoicesGroup');
    const isManual = currentQuizMethod === 'manual';
    const isMixed = (examType === 'mixed');
    if (numQuestionsGroup) numQuestionsGroup.style.display = isManual ? 'block' : 'none';
    if (numChoicesGroup) numChoicesGroup.style.display = (isManual && examType === 'multipleChoice') ? 'block' : 'none';
    if (quizUploadData.examType && quizUploadData.examType !== examType) clearQuizUploadState();
};
function collectQuizManualQuestions(prefix) {
    const container = document.getElementById('interventionQuestionsContainer');
    if (!container) return [];
    const globalExamType = document.getElementById('quizExamType')?.value || 'multipleChoice';
    const questionItems = container.querySelectorAll('.question-item');
    const questions = [];
    questionItems.forEach(item => {
        let type;
        const typeSelect = item.querySelector('.question-type-select');
        if (typeSelect) type = typeSelect.value;
        else type = globalExamType;
        if (type === 'mixed') type = 'multipleChoice';
        const questionText = item.querySelector('.question-input')?.value?.trim() || '';
        if (!questionText) return;
        let questionData = { type, question: questionText };
        if (type === 'multipleChoice') {
            const choiceInputs = item.querySelectorAll('.choice-input');
            const choices = Array.from(choiceInputs).map(input => input.value.trim()).filter(Boolean);
            if (choices.length < 2) return;
            const correctRadio = item.querySelector('.correct-choice-radio:checked');
            let correctAnswer = '';
            if (correctRadio) {
                const index = parseInt(correctRadio.value, 10);
                correctAnswer = choices[index] || '';
            }
            if (!correctAnswer) return;
            questionData.choices = choices;
            questionData.correctAnswer = correctAnswer;
        } else if (type === 'trueFalse') {
            const answerSelect = item.querySelector('.manual-answer-input');
            const correctAnswer = answerSelect ? answerSelect.value : '';
            if (!correctAnswer) return;
            questionData.choices = ['True', 'False'];
            questionData.correctAnswer = correctAnswer;
        } else if (type === 'matchingType') {
            const pairs = [];
            const pairElements = item.querySelectorAll('.matching-pair');
            pairElements.forEach(pair => {
                const left = pair.querySelector('.matching-left-input')?.value?.trim() || '';
                const right = pair.querySelector('.matching-right-input')?.value?.trim() || '';
                if (left && right) pairs.push({ question: left, answer: right });
            });
            if (pairs.length === 0) return;
            questionData.pairs = pairs;
        }
        questions.push(questionData);
    });
    return questions;
}
window.buildQuizPayload = function () {
    const examType = document.getElementById('quizExamType')?.value || 'multipleChoice';
    const shuffleQuestions = !!document.getElementById('quizShuffleQuestions')?.checked;
    const shuffleChoices = !!document.getElementById('quizShuffleChoices')?.checked;
    let questions = [];
    let fileName = '';
    if (currentQuizMethod === 'manual') {
        questions = collectQuizManualQuestions('quiz');
    } else {
        questions = quizUploadData.questions || [];
        fileName = quizUploadData.fileName || document.getElementById('quizFileName')?.textContent?.trim() || '';
    }
    return { examType, inputMethod: currentQuizMethod, shuffleQuestions, shuffleChoices, questions, fileName };
};
window.validateQuizBeforeAdd = function () {
    const payload = buildQuizPayload();
    if (payload.inputMethod === 'upload' && !payload.questions.length) {
        const messages = {
            multipleChoice: 'Please upload a valid Excel file with a "Questions" sheet.',
            trueFalse: 'Please upload a valid Excel file with Question and Correct Answer columns.',
            matchingType: 'Please upload a valid Excel file with Question and Answer columns.'
        };
        alert(messages[payload.examType] || 'Please upload a valid Excel file.');
        return false;
    }
    if (payload.inputMethod === 'manual' && !payload.questions.length) {
        alert('Please generate and complete the manual quiz questions before adding.');
        return false;
    }
    return true;
};

// ============ QUIZ QUESTION HTML ============
window.buildQuizQuestionHtml = function (questionNumber, examType, numChoices, prefix = 'quiz') {
    if (examType === 'matchingType') {
        if (typeof window.buildMatchingQuestionItemHtml === 'function') {
            return window.buildMatchingQuestionItemHtml(prefix, questionNumber);
        } else {
            return `<div class="question-item">Matching type not supported.</div>`;
        }
    }
    let html = `
        <div class="question-item">
            <div class="question-item-header">Question ${questionNumber}</div>
            <input type="text" class="question-input" placeholder="Enter question text" id="quiz-q-${questionNumber}">
    `;
    if (examType === 'multipleChoice') {
        html += `<div class="choice-inputs">`;
        for (let choiceNum = 1; choiceNum <= numChoices; choiceNum++) {
            html += `
                <div class="choice-input-row">
                    <span class="choice-label">Choice ${choiceNum}:</span>
                    <input type="text" class="choice-input" id="${prefix}-q${questionNumber}-choice${choiceNum}" placeholder="Enter choice text">
                    <label class="correct-choice-marker">
                        <input type="radio" name="${prefix}-q${questionNumber}-correct" class="correct-choice-radio" value="${choiceNum}" onchange="window.updateCorrectChoiceIndicator && updateCorrectChoiceIndicator(this)">
                        <span class="correct-indicator">( )</span>
                    </label>
                </div>
            `;
        }
        html += `</div>`;
    } else if (examType === 'trueFalse') {
        html += `
            <div class="question-answer-box">
                <label>Answer Key</label>
                <select class="manual-answer-input">
                    <option value="">Select answer</option>
                    <option>True</option>
                    <option>False</option>
                </select>
            </div>
        `;
    }
    html += `</div>`;
    return html;
};

// ============ QUIZ GENERATION ============
function generateMixedQuizQuestionItems(prefix, count, startIndex = 0) {
    let html = '';
    for (let i = 1; i <= count; i++) {
        const questionNumber = startIndex + i;
        const defaultType = 'multipleChoice';
        html += `
        <div class="question-item" data-q="${questionNumber}" data-question-type="${defaultType}">
            <div class="question-item-header">
                Question ${questionNumber}
                <button type="button" class="remove-question-btn" onclick="window.removeQuestion(this)" title="Delete Question">
                    <i class="fas fa-trash"></i>
                </button>
                <select class="question-type-select" data-q="${questionNumber}" onchange="window.onQuestionTypeChange(this, '${prefix}')">
                    <option value="multipleChoice" selected>Multiple Choice</option>
                    <option value="trueFalse">True or False</option>
                    <option value="matchingType">Matching Type</option>
                </select>
            </div>
            <div class="question-input-group">
                <input type="text" class="question-input" placeholder="Enter question text" data-q="${questionNumber}">
            </div>
            <!-- Multiple Choice -->
            <div class="choice-inputs" data-q="${questionNumber}" style="display:block;">
                ${['A','B','C','D'].map((label, ci) => `
                <div class="choice-input-row">
                    <span>${label}.</span>
                    <input type="text" class="choice-input" placeholder="Choice ${label}" data-q="${questionNumber}" data-choice="${ci}">
                    <label class="correct-choice-marker">
                        <input type="radio" name="${prefix}-q${questionNumber}-correct" class="correct-choice-radio" value="${ci}" onchange="window.updateCorrectChoiceIndicator(this)">
                        <span class="correct-indicator">( )</span>
                    </label>
                </div>
                `).join('')}
            </div>
            <!-- True/False -->
            <div class="truefalse-inputs" data-q="${questionNumber}" style="display:none;">
                <div class="question-answer-box">
                    <label>Answer Key</label>
                    <select class="manual-answer-input" data-q="${questionNumber}">
                        <option value="">Select answer</option>
                        <option value="True">True</option>
                        <option value="False">False</option>
                    </select>
                </div>
            </div>
            <!-- Matching Type -->
            <div class="matching-inputs" data-q="${questionNumber}" style="display:none;">
                <div class="matching-pairs">
                    <div class="matching-pair" data-pair="1">
                        <input type="text" class="matching-left-input" placeholder="Left item" data-q="${questionNumber}" data-pair="1">
                        <span>↔</span>
                        <input type="text" class="matching-right-input" placeholder="Right item" data-q="${questionNumber}" data-pair="1">
                        <button type="button" class="remove-pair" onclick="window.removeMatchingPair(this)">✕</button>
                    </div>
                </div>
                <button type="button" class="add-matching-pair" data-q="${questionNumber}" onclick="window.addMatchingPair(this)">+ Add Pair</button>
            </div>
            <hr style="margin:12px 0;">
        </div>
        `;
    }
    return html;
}
window.generateQuizQuestions = function () {
    const numQuestions = parseInt(document.getElementById('quizNumQuestions').value) || 3;
    const examType = document.getElementById('quizExamType').value || 'multipleChoice';
    const isMixed = (examType === 'mixed');
    document.getElementById('quizSetupPhase').style.display = 'none';
    document.getElementById('quizQuestionsPhase').style.display = 'block';
    const container = document.getElementById('interventionQuestionsContainer');
    if (isMixed) {
        container.innerHTML = generateMixedQuizQuestionItems('quiz', numQuestions, 0);
    } else {
        const numChoices = parseInt(document.getElementById('quizNumChoices').value) || 4;
        let html = '';
        for (let i = 1; i <= numQuestions; i++) {
            html += buildQuizQuestionHtml(i, examType, numChoices);
        }
        container.innerHTML = html;
    }
};

// ============ INIT QUIZ EXAM TYPE HANDLERS ============
window.initializeQuizExamTypeHandlers = function () {
    const select = document.getElementById('quizExamType');
    if (!select) return;
    select.addEventListener('change', () => {
        document.getElementById('interventionQuestionsContainer').innerHTML = '';
        document.getElementById('quizQuestionsPhase').style.display = 'none';
        updateQuizUploadVisibility();
    });
    updateQuizUploadVisibility();
};
document.addEventListener('DOMContentLoaded', function() {
    // Attach handlers after DOM ready
    setTimeout(initializeQuizExamTypeHandlers, 100);
});

// ============ QUIZ FILE CHANGE ============
document.getElementById('quizFile')?.addEventListener('change', function() {
    window.handleQuizFileChange();
});
window.handleQuizFileChange = function () {
    const input = document.getElementById('quizFile');
    const nameEl = document.getElementById('quizFileName');
    const previewEl = document.getElementById('quizUploadPreview');
    const errorEl = document.getElementById('quizUploadError');
    const file = input?.files?.[0];
    const examType = document.getElementById('quizExamType')?.value || 'multipleChoice';
    if (nameEl) nameEl.textContent = file?.name || '';
    if (previewEl) { previewEl.style.display = 'none'; previewEl.innerHTML = ''; }
    if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
    if (!file) { quizUploadData = { questions: [], fileName: '', examType: '' }; return; }
    if (typeof window.parseAssessmentExcelFile === 'function') {
        window.parseAssessmentExcelFile('quiz', file, examType)
            .catch(error => {
                quizUploadData = { questions: [], fileName: '', examType: '' };
                if (errorEl) { errorEl.textContent = error.message || 'Failed to parse Excel file.'; errorEl.style.display = 'block'; }
                if (nameEl) nameEl.textContent = '';
                input.value = '';
            });
    } else {
        alert('Excel parser not loaded.');
    }
};

// ============ LOAD FROM SERVER ============
window.loadInterventionFromServer = async function () {
    const grade = getCurrentGrade();
    const quarter = getCurrentQuarter();
    const subject = getCurrentSubject();
    const week = getCurrentWeek();
    if (!grade || !quarter || !subject || !week) {
        console.warn('No week context to load intervention.');
        return;
    }
    try {
        const endpoints = [
            { key: 'materials', url: `/teacher/content-library/intervention/material/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}` },
            { key: 'videos', url: `/teacher/content-library/intervention/video/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}` },
            { key: 'quizzes', url: `/teacher/content-library/intervention/quiz/fetch/${encodeURIComponent(grade)}/${encodeURIComponent(quarter)}/${encodeURIComponent(subject)}/${encodeURIComponent(week)}` }
        ];
        // Reset
        interventionMaterials = { basic: [], standard: [], advanced: [] };
        interventionVideos = { basic: [], standard: [], advanced: [] };
        interventionQuizzes = { basic: [], standard: [], advanced: [] };
        for (const { key, url } of endpoints) {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) continue;
            const data = await res.json();
            if (!data.success) continue;
            const items = data[key] || [];
            if (key === 'materials') {
                items.forEach(item => {
                    if (interventionMaterials[item.level]) interventionMaterials[item.level].push(item.file_name);
                });
            } else if (key === 'videos') {
                items.forEach(item => {
                    if (interventionVideos[item.level]) {
                        const video = item.video_type === 'link' ? item.video_url : item.file_name;
                        interventionVideos[item.level].push(video);
                    }
                });
            } else if (key === 'quizzes') {
                items.forEach(item => {
                    if (interventionQuizzes[item.level]) {
                        let questions = item.questions;
                        if (typeof questions === 'string') {
                            try { questions = JSON.parse(questions); } catch(e) { questions = []; }
                        }
                        const quiz = {
                            questionItems: questions || [],
                            examType: item.exam_type,
                            inputMethod: item.input_method,
                            shuffleQuestions: item.settings?.shuffle_questions || false,
                            shuffleChoices: item.settings?.shuffle_choices || false,
                            timer: item.settings?.timer || '00:00:00',
                            fileName: item.file_name || '',
                            questions: questions?.length || 0,
                            examTypeLabel: getExamTypeLabel(item.exam_type)
                        };
                        interventionQuizzes[item.level].push(quiz);
                    }
                });
            }
        }
        updateInterventionDisplay();
    } catch (e) {
        console.error('Error loading intervention data:', e);
        alert('Failed to load intervention data. Please refresh and try again.');
    }
};

// ============ GET EXAM TYPE LABEL ============
function getExamTypeLabel(examType) {
    if (examType === 'trueFalse') return 'True or False';
    if (examType === 'matchingType') return 'Matching Type';
    if (examType === 'mixed') return 'Mixed';
    return 'Multiple Choice';
}

// ============ EDIT MODAL FROM CONTENT CARD ============
window.openInterventionEditModal = function (item) {
    // Load data first, then open modal
    window.loadInterventionFromServer().then(() => {
        window.openModal('interventionModal');
        // Switch to the tab that was clicked? We can just open the modal and let user choose.
        // Optionally, if item.tab exists, switch to it.
        if (item && item.tab) {
            window.switchContent(item.tab);
        }
    }).catch(() => {
        window.openModal('interventionModal');
    });
};