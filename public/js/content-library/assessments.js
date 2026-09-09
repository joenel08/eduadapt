(function () {
    'use strict';

    // ===== Assessment state =====
    let currentAssessmentMethod = { preAssessment: 'upload', postAssessment: 'upload' };
    let assessmentUploadData = {
        preAssessment: { questions: [], fileName: '', examType: '' },
        postAssessment: { questions: [], fileName: '', examType: '' }
    };

    // ===== Helpers to access state from core =====
    function getState() {
        return window.__contentState || {};
    }

    function getCurrentGrade() {
        return getState().currentGrade;
    }

    function getCurrentQuarter() {
        return getState().currentQuarter;
    }

    function getCurrentSubject() {
        return getState().currentSubject;
    }

    function getCurrentWeek() {
        return getState().currentWeek;
    }

    function getWeekContent() {
        return getState().weekContent || {};
    }

    function setWeekContent(content) {
        if (window.__contentState) {
            window.__contentState.weekContent = content;
        }
    }

    // ===== Exported functions =====
    window.switchAssessmentMethod = function (type, method) {
        currentAssessmentMethod[type] = method;

        // Toggle content panels
        document.getElementById(`${type}-upload`).classList.remove('active');
        document.getElementById(`${type}-manual`).classList.remove('active');
        document.getElementById(`${type}-${method}`).classList.add('active');

        // Update tabs using data-method attribute
        document.querySelectorAll(`#${type}Modal .input-method-tab`).forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.method === method) {
                tab.classList.add('active');
            }
        });
    };
    window.generatePreAssessmentQuestions = function () {
        const container = document.getElementById('preAssessmentQuestionsContainer');
        const currentCount = container.querySelectorAll('.question-item').length;
        const newCount = parseInt(document.getElementById('preAssessmentQuestionCount').value) || 5;
        const examType = getAssessmentExamType('preAssessment');
        const isMixed = (examType === 'mixed');
        // Generate only the new ones, starting from currentCount+1
        const newHTML = generateAssessmentQuestionItems('preAssessment', newCount, isMixed, currentCount);
        container.insertAdjacentHTML('beforeend', newHTML);
    };
    window.savePreAssessment = async function () {
        if (!validateAssessmentBeforeSave('preAssessment')) return;

        const grade = getCurrentGrade();
        const quarter = getCurrentQuarter();
        const subject = getCurrentSubject();
        const week = getCurrentWeek();
        const payload = buildAssessmentPayload('preAssessment');

        if (!grade || !quarter || !subject || !week) {
            alert('Please navigate to a specific week first.');
            return;
        }

        const hours = document.getElementById('preAssessmentHours').value || 0;
        const minutes = document.getElementById('preAssessmentMinutes').value || 0;
        const seconds = document.getElementById('preAssessmentSeconds').value || 0;
        // const dueDate = document.getElementById('preAssessmentDueDate').value;


        const settings = {
            timer: `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`,
            // due_date: dueDate,
            shuffle_questions: payload.shuffleQuestions,
            shuffle_choices: payload.shuffleChoices,
            // status: payload.status,
            exam_type: getAssessmentExamType('preAssessment')
        };
        // Prepare FormData for server upload
        const formData = new FormData();
        formData.append('exam_type', payload.examType);
        formData.append('input_method', payload.inputMethod);
        formData.append('questions', JSON.stringify(payload.questions));
        formData.append('settings', JSON.stringify(settings));
        formData.append('grade_level', grade);
        formData.append('term', quarter);
        formData.append('subject', subject);
        formData.append('week', week);
        formData.append('school_year_id', window.activeSchoolYearId || '');

        // Check if file was uploaded (Excel)
        const fileInput = document.getElementById('preAssessmentFile');
        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('file', fileInput.files[0]);
        }

        // Disable button and show spinner
        const btn = document.querySelector('#preAssessmentModal .btn-save');
        const originalText = btn.textContent;
        btn.textContent = 'Saving...';
        btn.disabled = true;

        try {
            const response = await fetch('/teacher/content-library/pre-assessment', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();
            btn.textContent = originalText;
            btn.disabled = false;

            if (data.success) {
                // Clear modal state
                window.closeModal('preAssessmentModal');
                window.clearAssessmentUploadState('preAssessment');
                document.getElementById('preAssessmentQuestionsContainer').innerHTML = '';
                alert('Pre-Assessment saved successfully!');

                // Reload the week content from server
                if (typeof window.loadContentFromServer === 'function') {
                    await window.loadContentFromServer();
                } else {
                    // Fallback: re-render from localStorage
                    const weekContent = getWeekContent();
                    const key = `${grade}-${quarter}-${subject}-${week}`;
                    if (weekContent[key] && weekContent[key].preAssessment) {
                        window.renderWeekContent(grade, quarter, subject, week);
                    }
                }
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            btn.textContent = originalText;
            btn.disabled = false;
            alert('Network error: ' + error.message);
        }
    };

   window.generatePostAssessmentQuestions = function () {
    const container = document.getElementById('postAssessmentQuestionsContainer');
    const currentCount = container.querySelectorAll('.question-item').length;
    const newCount = parseInt(document.getElementById('postAssessmentQuestionCount').value) || 5;
    const examType = getAssessmentExamType('postAssessment');
    const isMixed = (examType === 'mixed');
    const newHTML = generateAssessmentQuestionItems('postAssessment', newCount, isMixed, currentCount);
    container.insertAdjacentHTML('beforeend', newHTML);
};
    window.savePostAssessment = async function () {
    console.log('[savePostAssessment] Starting...');

    // 1. Validate
    if (!validateAssessmentBeforeSave('postAssessment')) {
        console.warn('[savePostAssessment] Validation failed.');
        return;
    }

    // 2. Gather context
    const grade = getCurrentGrade();
    const quarter = getCurrentQuarter();
    const subject = getCurrentSubject();
    const week = getCurrentWeek();

    if (!grade || !quarter || !subject || !week) {
        alert('Please navigate to a specific week first.');
        console.warn('[savePostAssessment] Missing context:', { grade, quarter, subject, week });
        return;
    }
    console.log('[savePostAssessment] Context:', { grade, quarter, subject, week });

    // 3. Build payload
    const hours = document.getElementById('postAssessmentHours')?.value || 0;
    const minutes = document.getElementById('postAssessmentMinutes')?.value || 0;
    const seconds = document.getElementById('postAssessmentSeconds')?.value || 0;

    const payload = buildAssessmentPayload('postAssessment');
    console.log('[savePostAssessment] Payload (before settings):', payload);

    const settings = {
        timer: `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`,
        shuffle_questions: payload.shuffleQuestions,
        shuffle_choices: payload.shuffleChoices,
        exam_type: getAssessmentExamType('postAssessment')
    };

    // 4. Prepare FormData
    const formData = new FormData();
    formData.append('exam_type', payload.examType);
    formData.append('input_method', payload.inputMethod);
    formData.append('questions', JSON.stringify(payload.questions));
    formData.append('settings', JSON.stringify(settings));
    formData.append('grade_level', grade);
    formData.append('term', quarter);
    formData.append('subject', subject);
    formData.append('week', week);
    formData.append('school_year_id', window.activeSchoolYearId || '');

    // Attach file if present
    const fileInput = document.getElementById('postAssessmentFile');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('file', fileInput.files[0]);
        console.log('[savePostAssessment] File attached:', fileInput.files[0].name);
    } else {
        console.log('[savePostAssessment] No file attached.');
    }

    // 5. Get button and disable
    const btn = document.querySelector('#postAssessmentModal .btn-save');
    if (!btn) {
        console.error('[savePostAssessment] Save button not found!');
        alert('UI error: Save button not found. Please check modal HTML.');
        return;
    }
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    try {
        // 6. Send request
        const url = '/teacher/content-library/post-assessment';
        console.log('[savePostAssessment] Sending POST to:', url);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        });

        console.log('[savePostAssessment] Response status:', response.status);

        // 7. Parse response
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            console.error('[savePostAssessment] Failed to parse JSON:', jsonError);
            const text = await response.text();
            console.error('[savePostAssessment] Raw response:', text);
            alert('Server returned invalid JSON. Check console for details.');
            return;
        }

        console.log('[savePostAssessment] Response data:', data);

        // 8. Handle success / error
        if (response.ok && data.success) {
            // Clear modal state
            window.closeModal('postAssessmentModal');
            window.clearAssessmentUploadState('postAssessment');
            document.getElementById('postAssessmentQuestionsContainer').innerHTML = '';
            alert('Post-Assessment saved successfully!');

            // Reload content
            if (typeof window.loadContentFromServer === 'function') {
                await window.loadContentFromServer();
            } else {
                const weekContent = getWeekContent();
                const key = `${grade}-${quarter}-${subject}-${week}`;
                if (weekContent[key] && weekContent[key].postAssessment) {
                    window.renderWeekContent(grade, quarter, subject, week);
                }
            }
        } else {
            // Show detailed error
            const errorMsg = data.message || data.error || 'Unknown error';
            const errors = data.errors ? JSON.stringify(data.errors) : '';
            alert(`Error: ${errorMsg} ${errors}`);
            console.error('[savePostAssessment] Server error:', data);
        }
    } catch (error) {
        console.error('[savePostAssessment] Network or other error:', error);
        alert('Network error: ' + error.message + '. Check console for details.');
    } finally {
        // Restore button
        if (btn) {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }
};

    // ===== Internal helper functions =====
    function getAssessmentExamType(prefix) {
        return document.getElementById(`${prefix}ExamType`)?.value || 'multipleChoice';
    }

    function getExamTypeLabel(examType) {
        if (examType === 'trueFalse') return 'True or False';
        if (examType === 'matchingType') return 'Matching Type';
        return 'Multiple Choice';
    }

    function buildAssessmentPayload(prefix) {
        const inputMethod = currentAssessmentMethod[prefix] || 'upload';
        const shuffleQuestions = !!document.getElementById(`${prefix}ShuffleQuestions`)?.checked;
        const shuffleChoices = !!document.getElementById(`${prefix}ShuffleChoices`)?.checked;
        // const status = document.getElementById(`${prefix}Status`)?.value || 'open';
        const examType = getAssessmentExamType(prefix); 
        let questions = [];
        let fileName = '';
        if (inputMethod === 'manual') {
            questions = collectManualAssessmentQuestions(prefix);
        } else {
            questions = assessmentUploadData[prefix]?.questions || [];
            fileName = assessmentUploadData[prefix]?.fileName || document.getElementById(`${prefix}FileName`)?.textContent?.trim() || '';
        }
        return { inputMethod, shuffleQuestions, shuffleChoices, questions, fileName, examType };
    }
    function validateAssessmentBeforeSave(prefix) {
        const payload = buildAssessmentPayload(prefix);
        if (payload.inputMethod === 'upload' && !payload.questions.length) {
            alert('Please upload a valid Excel file.');
            return false;
        }
        if (payload.inputMethod === 'manual' && !payload.questions.length) {
            alert('Please add at least one question.');
            return false;
        }
        // Additional validation per question
        for (let i = 0; i < payload.questions.length; i++) {
            const q = payload.questions[i];
            if (!q.question) {
                alert(`Question ${i + 1} is missing text.`);
                return false;
            }
            if (q.type === 'multipleChoice') {
                if (!q.choices || q.choices.length < 2) {
                    alert(`Question ${i + 1} needs at least two choices.`);
                    return false;
                }
                if (!q.correctAnswer) {
                    alert(`Question ${i + 1} needs a correct answer.`);
                    return false;
                }
            } else if (q.type === 'trueFalse') {
                if (!q.correctAnswer) {
                    alert(`Question ${i + 1} needs a correct answer.`);
                    return false;
                }
            } else if (q.type === 'matchingType') {
                if (!q.pairs || q.pairs.length === 0) {
                    alert(`Question ${i + 1} needs at least one matching pair.`);
                    return false;
                }
            }
        }
        return true;
    }

    function collectManualAssessmentQuestions(prefix) {
        const containerId = prefix === 'quiz' ? 'interventionQuestionsContainer' : `${prefix}QuestionsContainer`;
        const container = document.getElementById(containerId);
        if (!container) return [];

        const globalExamType = getAssessmentExamType(prefix);
        const questionItems = container.querySelectorAll('.question-item');
        const questions = [];

        questionItems.forEach(item => {
            // Determine the type: either from the per-question dropdown or global
            let type;
            const typeSelect = item.querySelector('.question-type-select');
            if (typeSelect) {
                type = typeSelect.value;
            } else {
                type = globalExamType;
            }
            if (type === 'mixed') type = 'multipleChoice'; // fallback

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
                    if (left && right) {
                        pairs.push({ question: left, answer: right });
                    }
                });
                if (pairs.length === 0) return;
                questionData.pairs = pairs;
            }

            questions.push(questionData);
        });

        return questions;
    }

    function updateQuestionFieldsForType(prefix) {
        const examType = getAssessmentExamType(prefix);
        // If mixed, do not override per‑question visibility
        if (examType === 'mixed') {
            return;
        }
        const container = document.getElementById(`${prefix}QuestionsContainer`);
        if (!container) return;
        const items = container.querySelectorAll('.question-item');
        items.forEach(item => {
            const choiceSection = item.querySelector('.choice-inputs');
            const tfSection = item.querySelector('.truefalse-inputs');
            const matchSection = item.querySelector('.matching-inputs');
            if (choiceSection) choiceSection.style.display = 'none';
            if (tfSection) tfSection.style.display = 'none';
            if (matchSection) matchSection.style.display = 'none';
            if (examType === 'multipleChoice' && choiceSection) {
                choiceSection.style.display = 'block';
            } else if (examType === 'trueFalse' && tfSection) {
                tfSection.style.display = 'block';
            } else if (examType === 'matchingType' && matchSection) {
                matchSection.style.display = 'block';
            }
        });
    }

    function getManualQuestionCorrectAnswer(questionItem) {
        const checkedRadio = questionItem.querySelector('.correct-choice-radio:checked');
        if (!checkedRadio) return '';
        const choiceInputs = questionItem.querySelectorAll('.choice-input');
        const index = parseInt(checkedRadio.value, 10) - 1;
        return choiceInputs[index]?.value?.trim() || '';
    }

    function updateCorrectChoiceIndicator(radio) {
        const questionItem = radio.closest('.question-item');
        if (!questionItem) return;
        questionItem.querySelectorAll('.correct-indicator').forEach(span => { span.textContent = '( )'; });
        if (radio.checked) {
            radio.closest('.correct-choice-marker').querySelector('.correct-indicator').textContent = '(●)';
        }
    }

    function normalizeQuestionHeader(value) {
        return String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function buildHeaderIndexMap(headers) {
        const indexMap = {};
        headers.forEach((header, index) => { indexMap[header] = index; });
        return indexMap;
    }

    function assertRequiredColumns(headers, requiredColumns) {
        const missing = requiredColumns.filter(column => !headers.includes(column));
        if (missing.length) throw new Error(`Missing required columns: ${missing.join(', ')}.`);
    }

    function assertNoDuplicateValues(values, label) {
        const seen = new Set();
        values.forEach((value, index) => {
            const key = String(value || '').trim().toLowerCase();
            if (!key) return;
            if (seen.has(key)) throw new Error(`Duplicate ${label} found: "${value}".`);
            seen.add(key);
        });
    }

    function getExcelFormatConfig(examType) {
        if (examType === 'trueFalse') {
            return {
                sheetName: 'Questions',
                columns: ['Question', 'Correct Answer'],
                sampleRows: [['The Earth revolves around the Sun.', 'True'], ['HTML is a programming language.', 'False']],
                instruction: 'Upload an Excel file containing the Question and Correct Answer columns. The system will automatically generate the True and False choices.'
            };
        }
        if (examType === 'matchingType') {
            return {
                sheetName: 'Questions',
                columns: ['Question', 'Answer'],
                sampleRows: [['Philippines', 'Manila'], ['Japan', 'Tokyo'], ['Korea', 'Seoul'], ['Thailand', 'Bangkok']],
                instruction: 'Upload an Excel file containing Question and Answer columns. Each row is one matching pair. Questions stay in upload order; only the choices are randomized for students.'
            };
        }
        if (examType === 'mixed') {
        return {
            sheetName: 'Questions',
            columns: ['Type', 'Question', 'Choice 1', 'Choice 2', 'Choice 3', 'Choice 4', 'Correct Answer', 'Left', 'Right'],
            sampleRows: [
                ['MC', 'What is 2+2?', '3', '4', '5', '6', '4', '', ''],
                ['TF', 'The sun is hot.', '', '', '', '', 'True', '', ''],
                ['MT', 'Match countries', '', '', '', '', '', 'Philippines', 'Manila'],
                ['MT', '', '', '', '', '', '', 'Japan', 'Tokyo']
            ],
            instruction: 'For Mixed type, include a "Type" column with values: MC, TF, or MT. For MC, provide choices and correct answer. For TF, provide correct answer (True/False). For MT, provide Left and Right columns (Question/Answer for each pair). You can group MT rows by leaving the Question column empty or repeating the same question text.'
        };
    }
        return {
            sheetName: 'Questions',
            columns: ['Question', 'Choice 1', 'Choice 2', 'Choice 3', 'Choice 4', 'Correct Answer'],
            sampleRows: [['What is HTML?', 'Programming Language', 'Markup Language', 'Database', 'Browser', 'Markup Language']],
            instruction: 'Upload an Excel file with questions, choices, and the correct answer in one sheet.'
        };
    }

    function renderAssessmentUploadFormatHint(prefix, examType) {
        const config = getExcelFormatConfig(examType);
        const hintEl = document.getElementById(`${prefix}UploadFormatHint`);
        const instructionEl = document.getElementById(`${prefix}UploadInstruction`);
        if (!hintEl) return;
        const headerCells = config.columns.map(column => `<th>${window.escapeHtml(column)}</th>`).join('');
        const bodyRows = config.sampleRows.map(row => `<tr>${row.map(cell => `<td>${window.escapeHtml(cell)}</td>`).join('')}</tr>`).join('');
        hintEl.innerHTML = `
            <strong>Sheet name: ${window.escapeHtml(config.sheetName)}</strong>
            <table class="excel-format-table"><thead><tr>${headerCells}</tr></thead><tbody>${bodyRows}</tbody></table>
        `;
        if (instructionEl) instructionEl.textContent = config.instruction;
    }

    function updateAssessmentRandomizationOptions(prefix, examType) {
        const randomization = document.getElementById(`${prefix}Randomization`);
        const shuffleQuestionsRow = document.getElementById(`${prefix}ShuffleQuestions`)?.closest('.toggle-line');
        const shuffleChoicesRow = document.getElementById(`${prefix}ShuffleChoicesRow`) || document.getElementById(`${prefix}ShuffleChoices`)?.closest('.toggle-line');
        if (!randomization) return;
        randomization.style.display = 'block';
        if (shuffleQuestionsRow) shuffleQuestionsRow.style.display = examType === 'matchingType' ? 'none' : 'flex';
        if (shuffleChoicesRow) shuffleChoicesRow.style.display = (examType === 'multipleChoice' || examType === 'matchingType') ? 'flex' : 'none';
    }

    function clearAssessmentUploadState(prefix) {
        const fileEl = document.getElementById(`${prefix}File`);
        const nameEl = document.getElementById(`${prefix}FileName`);
        const previewEl = document.getElementById(`${prefix}UploadPreview`);
        const errorEl = document.getElementById(`${prefix}UploadError`);
        if (fileEl) fileEl.value = '';
        if (nameEl) nameEl.textContent = '';
        if (previewEl) { previewEl.style.display = 'none'; previewEl.innerHTML = ''; }
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        assessmentUploadData[prefix] = { questions: [], fileName: '', examType: '' };
    }

    function parseMultipleChoiceExcelRows(rows) {
        if (!rows.length) return [];
        const headers = rows[0].map(normalizeQuestionHeader);
        assertRequiredColumns(headers, ['question', 'choice 1', 'choice 2', 'choice 3', 'choice 4', 'correct answer']);
        const indexMap = buildHeaderIndexMap(headers);
        const questions = [];
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const question = String(row[indexMap['question']] || '').trim();
            const choices = [1, 2, 3, 4].map(num => String(row[indexMap[`choice ${num}`]] || '').trim());
            const nonEmptyChoices = choices.filter(Boolean);
            const correctAnswer = String(row[indexMap['correct answer']] || '').trim();
            if (!question) continue;
            if (nonEmptyChoices.length < 2) throw new Error(`Row ${i + 1}: at least two choices are required.`);
            if (!correctAnswer) throw new Error(`Row ${i + 1}: Correct Answer is required.`);
            const matchedChoice = nonEmptyChoices.find(choice => choice.toLowerCase() === correctAnswer.toLowerCase());
            if (!matchedChoice) throw new Error(`Row ${i + 1}: Correct Answer must match one of the choices exactly.`);
            questions.push({
                question,
                choices: nonEmptyChoices,
                correctAnswer: matchedChoice,
                type: 'multipleChoice'
            });
        }
        if (!questions.length) throw new Error('No valid questions were found in the Excel file.');
        assertNoDuplicateValues(questions.map(q => q.question), 'question');
        return questions;
    }

    function parseTrueFalseExcelRows(rows) {
        if (!rows.length) return [];
        const headers = rows[0].map(normalizeQuestionHeader);
        assertRequiredColumns(headers, ['question', 'correct answer']);
        const indexMap = buildHeaderIndexMap(headers);
        const questions = [];
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const question = String(row[indexMap['question']] || '').trim();
            const rawAnswer = String(row[indexMap['correct answer']] || '').trim();
            if (!question && !rawAnswer) continue;
            if (!question) throw new Error(`Row ${i + 1}: Question is required.`);
            if (!rawAnswer) throw new Error(`Row ${i + 1}: Correct Answer is required.`);
            const normalizedAnswer = rawAnswer.toLowerCase();
            if (normalizedAnswer !== 'true' && normalizedAnswer !== 'false') throw new Error(`Row ${i + 1}: Correct Answer must be True or False only.`);
            const correctAnswer = normalizedAnswer === 'true' ? 'True' : 'False';
            questions.push({
                question,
                choices: ['True', 'False'],
                correctAnswer,
                type: 'trueFalse'
            });
        }
        if (!questions.length) throw new Error('No valid True/False questions were found in the Excel file.');
        assertNoDuplicateValues(questions.map(q => q.question), 'question');
        return questions;
    }

    function validateMatchingPairs(pairs, contextLabel) {
        if (!pairs.length) throw new Error(`${contextLabel}: at least one matching pair is required.`);
        assertNoDuplicateValues(pairs.map(pair => pair.question || pair.left), 'Question');
        assertNoDuplicateValues(pairs.map(pair => pair.answer || pair.right), 'Answer');
    }

    function parseMatchingTypeExcelRows(rows) {
        if (!rows.length) return [];
        const headers = rows[0].map(normalizeQuestionHeader);
        assertRequiredColumns(headers, ['question', 'answer']);
        const indexMap = buildHeaderIndexMap(headers);
        const pairs = [];
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const question = String(row[indexMap['question']] || '').trim();
            const answer = String(row[indexMap['answer']] || '').trim();
            if (!question && !answer) continue;
            if (!question) throw new Error(`Row ${i + 1}: Question is required.`);
            if (!answer) throw new Error(`Row ${i + 1}: Answer is required.`);
            pairs.push({ question, answer });
        }
        if (!pairs.length) throw new Error('No valid matching pairs were found in the Excel file.');
        validateMatchingPairs(pairs, 'Matching upload');
        return [{
            question: 'Matching Exercise',
            pairs,
            type: 'matchingType'
        }];
    }

    function parseAssessmentExcelRows(rows, examType) {
        if (examType === 'multipleChoice') return parseMultipleChoiceExcelRows(rows);
        if (examType === 'trueFalse') return parseTrueFalseExcelRows(rows);
        if (examType === 'matchingType') return parseMatchingTypeExcelRows(rows);
        throw new Error('Unsupported exam type for Excel upload.');
    }

    function readAssessmentExcelRows(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                try {
                    if (typeof XLSX === 'undefined') throw new Error('Excel parser is not loaded.');
                    const workbook = XLSX.read(reader.result, { type: 'binary' });
                    const sheetName = workbook.SheetNames.find(name => normalizeQuestionHeader(name) === 'questions') || workbook.SheetNames[0];
                    const sheet = workbook.Sheets[sheetName];
                    const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });
                    resolve(rows);
                } catch (error) { reject(error); }
            };
            reader.onerror = () => reject(new Error('Failed to read the uploaded file.'));
            reader.readAsBinaryString(file);
        });
    }

   function parseAssessmentExcelFile(prefix, file, examType) {
    examType = examType || getAssessmentExamType(prefix);
    return readAssessmentExcelRows(file).then(rows => {
        let questions;
        if (examType === 'mixed') {
            questions = parseMixedExcelRows(rows);
        } else {
            questions = parseAssessmentExcelRows(rows, examType);
        }
        assessmentUploadData[prefix] = { questions, fileName: file.name, examType };
        renderAssessmentUploadPreview(prefix, questions, examType);
        return questions;
    }).catch(error => {
        assessmentUploadData[prefix] = { questions: [], fileName: '', examType: '' };
        throw error;
    });
}

    function renderAssessmentUploadPreview(prefix, questions, examType) {
        const previewEl = document.getElementById(`${prefix}UploadPreview`);
        const errorEl = document.getElementById(`${prefix}UploadError`);
        if (!previewEl) return;
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        const sample = questions.slice(0, 5).map((q, index) => {
            if (examType === 'matchingType' && q.pairs) {
                const pairPreview = q.pairs.slice(0, 4).map(pair => {
                    const question = pair.question || pair.left;
                    const answer = pair.answer || pair.right;
                    return `${question} → ${answer}`;
                }).join(', ');
                return `<div><strong>Matching set.</strong> ${window.escapeHtml(pairPreview)}${q.pairs.length > 4 ? ' …' : ''}</div>`;
            }
            return `<div><strong>Q${index + 1}.</strong> ${window.escapeHtml(q.question)} — Answer: ${window.escapeHtml(q.correctAnswer)}</div>`;
        }).join('');
        const label = examType === 'matchingType' ? `${questions[0]?.pairs?.length || 0} matching pair(s) loaded successfully.` : `${questions.length} question(s) loaded successfully.`;
        previewEl.innerHTML = `<strong>${label}</strong> ${sample} ${questions.length > 5 ? '<div>…and more</div>' : ''}`;
        previewEl.style.display = 'block';
    }

    function updateAssessmentUploadVisibility(prefix) {
        const examType = getAssessmentExamType(prefix);
        renderAssessmentUploadFormatHint(prefix, examType);
        updateAssessmentRandomizationOptions(prefix, examType);
        if (assessmentUploadData[prefix]?.examType && assessmentUploadData[prefix].examType !== examType) {
            clearAssessmentUploadState(prefix);
        }
        // Also update existing question fields if any
        updateQuestionFieldsForType(prefix);
    }

    function generateAssessmentQuestionItems(prefix, count, isMixed = false, startIndex = 0) {
        const examType = getAssessmentExamType(prefix);
        let html = '';
        for (let i = 1; i <= count; i++) {
            const questionNumber = startIndex + i;
            // If mixed, default to multipleChoice for new questions
            const defaultType = isMixed ? 'multipleChoice' : examType;
            html += `
        <div class="question-item" data-q="${questionNumber}" data-question-type="${defaultType}">
            <div class="question-item-header">
                Question ${questionNumber}
                <button type="button" class="remove-question-btn" onclick="window.removeQuestion(this)" title="Delete Question">
                    <i class="fas fa-trash"></i>
                </button>
                ${isMixed ? `
                <select class="question-type-select" data-q="${questionNumber}" onchange="window.onQuestionTypeChange(this, '${prefix}')">
                    <option value="multipleChoice" ${defaultType === 'multipleChoice' ? 'selected' : ''}>Multiple Choice</option>
                    <option value="trueFalse" ${defaultType === 'trueFalse' ? 'selected' : ''}>True or False</option>
                    <option value="matchingType" ${defaultType === 'matchingType' ? 'selected' : ''}>Matching Type</option>
                </select>
                ` : ''}
            </div>
            <div class="question-input-group">
                <input type="text" class="question-input" placeholder="Enter question text" data-q="${questionNumber}">
            </div>

            <!-- Multiple Choice -->
            <div class="choice-inputs" data-q="${questionNumber}" style="display: ${defaultType === 'multipleChoice' ? 'block' : 'none'};">
                <div class="choice-input-row">
                    <span>A.</span>
                    <input type="text" class="choice-input" placeholder="Choice A" data-q="${questionNumber}" data-choice="0">
                    <label class="correct-choice-marker">
                        <input type="radio" name="${prefix}-q${questionNumber}-correct" class="correct-choice-radio" value="0" onchange="window.updateCorrectChoiceIndicator(this)">
                        <span class="correct-indicator">( )</span>
                    </label>
                </div>
                <div class="choice-input-row">
                    <span>B.</span>
                    <input type="text" class="choice-input" placeholder="Choice B" data-q="${questionNumber}" data-choice="1">
                    <label class="correct-choice-marker">
                        <input type="radio" name="${prefix}-q${questionNumber}-correct" class="correct-choice-radio" value="1" onchange="window.updateCorrectChoiceIndicator(this)">
                        <span class="correct-indicator">( )</span>
                    </label>
                </div>
                <div class="choice-input-row">
                    <span>C.</span>
                    <input type="text" class="choice-input" placeholder="Choice C" data-q="${questionNumber}" data-choice="2">
                    <label class="correct-choice-marker">
                        <input type="radio" name="${prefix}-q${questionNumber}-correct" class="correct-choice-radio" value="2" onchange="window.updateCorrectChoiceIndicator(this)">
                        <span class="correct-indicator">( )</span>
                    </label>
                </div>
                <div class="choice-input-row">
                    <span>D.</span>
                    <input type="text" class="choice-input" placeholder="Choice D" data-q="${questionNumber}" data-choice="3">
                    <label class="correct-choice-marker">
                        <input type="radio" name="${prefix}-q${questionNumber}-correct" class="correct-choice-radio" value="3" onchange="window.updateCorrectChoiceIndicator(this)">
                        <span class="correct-indicator">( )</span>
                    </label>
                </div>
            </div>

            <!-- True/False -->
            <div class="truefalse-inputs" data-q="${questionNumber}" style="display: ${defaultType === 'trueFalse' ? 'block' : 'none'};">
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
            <div class="matching-inputs" data-q="${questionNumber}" style="display: ${defaultType === 'matchingType' ? 'block' : 'none'};">
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

            <hr style="margin: 12px 0;">
        </div>
        `;
        }
        return html;
    }

    window.removeQuestion = function (btn) {
        const item = btn.closest('.question-item');
        if (item) {
            const container = item.parentNode;
            // Only allow removal if more than 1 question remains
            if (container.querySelectorAll('.question-item').length > 1) {
                item.remove();
            } else {
                alert('You need at least one question.');
            }
        }
    };
    window.onQuestionTypeChange = function (select, prefix) {
        const questionItem = select.closest('.question-item');
        const type = select.value;
        questionItem.dataset.questionType = type;
        const choiceSection = questionItem.querySelector('.choice-inputs');
        const tfSection = questionItem.querySelector('.truefalse-inputs');
        const matchSection = questionItem.querySelector('.matching-inputs');
        if (choiceSection) choiceSection.style.display = (type === 'multipleChoice') ? 'block' : 'none';
        if (tfSection) tfSection.style.display = (type === 'trueFalse') ? 'block' : 'none';
        if (matchSection) matchSection.style.display = (type === 'matchingType') ? 'block' : 'none';
    };
    function addMatchingPair(btn) {
        const container = btn.closest('.matching-inputs');
        const pairsContainer = container.querySelector('.matching-pairs');
        const pairCount = pairsContainer.querySelectorAll('.matching-pair').length + 1;
        const qIndex = btn.dataset.q;

        const newPair = document.createElement('div');
        newPair.className = 'matching-pair';
        newPair.dataset.pair = pairCount;
        newPair.innerHTML = `
        <input type="text" class="matching-left-input" placeholder="Left item" data-q="${qIndex}" data-pair="${pairCount}">
        <span>↔</span>
        <input type="text" class="matching-right-input" placeholder="Right item" data-q="${qIndex}" data-pair="${pairCount}">
        <button type="button" class="remove-pair" onclick="window.removeMatchingPair(this)">✕</button>
    `;
        pairsContainer.appendChild(newPair);
    }

    function removeMatchingPair(btn) {
        const pair = btn.closest('.matching-pair');
        if (pair && pair.parentElement.children.length > 1) {
            pair.remove();
        } else {
            alert('At least one pair is required.');
        }
    }

    function buildMatchingQuestionItemHtml(prefix, index) {
        return `
            <div class="question-item">
                <div class="question-item-header">Pair ${index}</div>
                <div class="option-inputs">
                    <div class="option-input-group">
                        <label>Question</label>
                        <input type="text" class="matching-question-input matching-left-input" placeholder="e.g., Philippines">
                    </div>
                    <div class="option-input-group">
                        <label>Answer</label>
                        <input type="text" class="matching-answer-input matching-right-input" placeholder="e.g., Manila">
                    </div>
                </div>
            </div>
        `;
    }

    function buildAssessmentQuizSummary(prefix, payload) {
        payload = payload || buildAssessmentPayload(prefix);
        const examType = getExamTypeLabel(payload.examType);
        const isManual = payload.inputMethod === 'manual';
        const chunks = [];
        chunks.push(`Exam Type: ${examType}`);
        chunks.push(`Input Method: ${isManual ? 'Manual' : 'Upload'}`);
        if (payload.fileName) chunks.push(`File: ${payload.fileName}`);
        if (payload.questions.length > 0) {
            const preview = payload.questions.slice(0, 3).map((q, index) => formatQuestionPreview(q, index));
            chunks.push(`Questions (${payload.questions.length}): ${preview.join(' | ')}${payload.questions.length > 3 ? ' ...' : ''}`);
        }
        chunks.push(`Shuffle Questions: ${payload.shuffleQuestions ? 'Yes' : 'No'}`);
        if (payload.examType === 'multipleChoice' || payload.examType === 'matchingType') {
            chunks.push(`Shuffle Choices: ${payload.shuffleChoices ? 'Yes' : 'No'}`);
        }
        return chunks.join('\n');
    }

    function formatQuestionPreview(q, index) {
        if (q.pairs?.length) {
            const pairPreview = q.pairs.slice(0, 2).map(pair => {
                const question = pair.question || pair.left;
                const answer = pair.answer || pair.right;
                return `${question} → ${answer}`;
            }).join(', ');
            return `Matching (${q.pairs.length} pairs): ${pairPreview}${q.pairs.length > 2 ? ' …' : ''}`;
        }
        return `Q${index + 1}: ${q.question} [Answer: ${q.correctAnswer}]`;
    }

    // ===== File input listeners =====
    function handleAssessmentFileChange(prefix) {
        const input = document.getElementById(`${prefix}File`);
        const nameEl = document.getElementById(`${prefix}FileName`);
        const previewEl = document.getElementById(`${prefix}UploadPreview`);
        const errorEl = document.getElementById(`${prefix}UploadError`);
        const file = input?.files?.[0];
        const examType = getAssessmentExamType(prefix);
        if (nameEl) nameEl.textContent = file?.name || '';
        if (previewEl) { previewEl.style.display = 'none'; previewEl.innerHTML = ''; }
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        if (!file) { assessmentUploadData[prefix] = { questions: [], fileName: '', examType: '' }; return; }
        parseAssessmentExcelFile(prefix, file, examType).catch(error => {
            assessmentUploadData[prefix] = { questions: [], fileName: '', examType: '' };
            if (errorEl) { errorEl.textContent = error.message || 'Failed to parse Excel file.'; errorEl.style.display = 'block'; }
            if (nameEl) nameEl.textContent = '';
            input.value = '';
        });
    }

    function parseMixedExcelRows(rows) {
    if (!rows.length) throw new Error('Empty file.');
    const headers = rows[0].map(normalizeQuestionHeader);
    const indexMap = buildHeaderIndexMap(headers);
    if (indexMap.type === undefined) throw new Error('Missing required column: Type.');
    if (indexMap.question === undefined) throw new Error('Missing required column: Question.');

    const result = [];
    const matchingGroups = {};

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const type = String(row[indexMap.type] || '').trim().toLowerCase();
        const question = String(row[indexMap.question] || '').trim();

        if (type === 'mc' || type === 'multiplechoice' || type === 'multiple choice') {
            const choiceKeys = headers.filter(h => h.includes('choice') || /^[0-9]+$/.test(h));
            if (choiceKeys.length < 2) throw new Error(`Row ${i+1}: Multiple Choice requires at least two choices.`);
            const choices = choiceKeys.map(k => String(row[indexMap[k]] || '').trim()).filter(Boolean);
            if (choices.length < 2) throw new Error(`Row ${i+1}: Multiple Choice requires at least two non-empty choices.`);
            const correctKey = headers.find(h => h.includes('correct') || h === 'answer');
            if (!correctKey) throw new Error(`Row ${i+1}: Missing correct answer column for Multiple Choice.`);
            let correctAnswer = String(row[indexMap[correctKey]] || '').trim();
            if (!correctAnswer) throw new Error(`Row ${i+1}: Correct answer is required.`);
            const matched = choices.find(c => c.toLowerCase() === correctAnswer.toLowerCase());
            if (!matched) throw new Error(`Row ${i+1}: Correct answer does not match any choice.`);
            result.push({ type: 'multipleChoice', question, choices, correctAnswer: matched });
        } else if (type === 'tf' || type === 'truefalse' || type === 'true/false') {
            const correctKey = headers.find(h => h.includes('correct') || h === 'answer');
            if (!correctKey) throw new Error(`Row ${i+1}: Missing correct answer column for True/False.`);
            let correctAnswer = String(row[indexMap[correctKey]] || '').trim();
            if (!correctAnswer) throw new Error(`Row ${i+1}: Correct answer is required.`);
            const normalized = correctAnswer.toLowerCase();
            if (normalized !== 'true' && normalized !== 'false') throw new Error(`Row ${i+1}: Correct answer must be True or False.`);
            result.push({ type: 'trueFalse', question, choices: ['True', 'False'], correctAnswer: normalized === 'true' ? 'True' : 'False' });
        } else if (type === 'mt' || type === 'matchingtype' || type === 'matching' || type === 'matching type') {
            const leftKey = headers.find(h => h.includes('left') || h === 'question');
            const rightKey = headers.find(h => h.includes('right') || h === 'answer');
            if (!leftKey || !rightKey) throw new Error(`Row ${i+1}: Matching type requires Left/Question and Right/Answer columns.`);
            const left = String(row[indexMap[leftKey]] || '').trim();
            const right = String(row[indexMap[rightKey]] || '').trim();
            if (!left || !right) throw new Error(`Row ${i+1}: Both left and right items are required for matching.`);
            const groupKey = question || 'Matching Exercise';
            if (!matchingGroups[groupKey]) matchingGroups[groupKey] = [];
            matchingGroups[groupKey].push({ question: left, answer: right });
        }
    }

    // Add grouped matching questions
    for (const [qText, pairs] of Object.entries(matchingGroups)) {
        result.push({ type: 'matchingType', question: qText, pairs });
    }

    if (!result.length) throw new Error('No valid questions found in the Excel file.');
    return result;
}



    window.populateAssessmentModal = function (prefix, data) {
        // 1. Set exam type and refresh hints / randomization
        const examTypeSelect = document.getElementById(`${prefix}ExamType`);
        if (examTypeSelect) {
            examTypeSelect.value = data.examType || 'multipleChoice';
            if (typeof window.updateAssessmentUploadVisibility === 'function') {
                window.updateAssessmentUploadVisibility(prefix);
            }
        }

        // 2. Common fields
        // const statusSelect = document.getElementById(`${prefix}Status`);
        // if (statusSelect) statusSelect.value = data.status || 'open';

        const shuffleQ = document.getElementById(`${prefix}ShuffleQuestions`);
        if (shuffleQ) shuffleQ.checked = !!data.shuffleQuestions;
        const shuffleC = document.getElementById(`${prefix}ShuffleChoices`);
        if (shuffleC) shuffleC.checked = !!data.shuffleChoices;

        const timerParts = (data.timer || '00:00:00').split(':');
        document.getElementById(`${prefix}Hours`).value = parseInt(timerParts[0]) || 0;
        document.getElementById(`${prefix}Minutes`).value = parseInt(timerParts[1]) || 0;
        document.getElementById(`${prefix}Seconds`).value = parseInt(timerParts[2]) || 0;

        // const dueDateInput = document.getElementById(`${prefix}DueDate`);
        // if (dueDateInput && data.dueDate) {
        //     dueDateInput.value = data.dueDate;
        // }

        // 3. Input method
        const method = data.inputMethod || 'upload';
        window.switchAssessmentMethod(prefix, method);

        // 4. Update question count
        const countInput = document.getElementById(`${prefix}QuestionCount`);
        if (countInput && data.questions && data.questions.length) {
            countInput.value = data.questions.length;
        }

        // 5. Manual: generate and populate
        if (method === 'manual' && data.questions && data.questions.length) {
            const container = document.getElementById(`${prefix}QuestionsContainer`);
            const isMixed = data.examType === 'mixed';

            // Regenerate HTML
            container.innerHTML = generateAssessmentQuestionItems(prefix, data.questions.length, isMixed);

            // Use MutationObserver to wait for the first question item to appear
            const observer = new MutationObserver((mutations, obs) => {
                const items = container.querySelectorAll('.question-item');
                if (items.length === data.questions.length) {
                    obs.disconnect();
                    // Now populate
                    populateQuestions(container, data.questions, prefix, isMixed);
                }
            });

            observer.observe(container, { childList: true, subtree: true });

            // Fallback: if after 500ms the items still aren't there, force populate anyway
            setTimeout(() => {
                observer.disconnect();
                populateQuestions(container, data.questions, prefix, isMixed);
            }, 500);
        }

        // 6. Upload method – show file name and preview
        if (method === 'upload') {
            const fileNameEl = document.getElementById(`${prefix}FileName`);
            if (fileNameEl && data.fileName) {
                fileNameEl.textContent = data.fileName;
            }
            assessmentUploadData[prefix] = {
                questions: data.questions || [],
                fileName: data.fileName || '',
                examType: data.examType || 'multipleChoice'
            };
            if (data.questions && data.questions.length) {
                renderAssessmentUploadPreview(prefix, data.questions, data.examType || 'multipleChoice');
            }
        }

        // 7. Open the modal
        window.openModal(`${prefix}Modal`);

        // 8. Store ID
        window._editingAssessmentId = data.id;

        // Helper function to populate questions (defined inside to capture variables)
        function populateQuestions(container, questions, prefix, isMixed) {
            const questionItems = container.querySelectorAll('.question-item');
            questions.forEach((q, index) => {
                const item = questionItems[index];
                if (!item) return;

                // Set question text
                const qInput = item.querySelector('.question-input');
                if (qInput) qInput.value = q.question || '';

                // Determine the type
                let currentType = q.type || 'multipleChoice';
                if (isMixed) {
                    const typeSelect = item.querySelector('.question-type-select');
                    if (typeSelect) typeSelect.value = currentType;
                } else {
                    // For non-mixed, use the global type; but we already have it.
                }

                // Directly show/hide sections
                const choiceSection = item.querySelector('.choice-inputs');
                const tfSection = item.querySelector('.truefalse-inputs');
                const matchSection = item.querySelector('.matching-inputs');

                // Hide all first
                if (choiceSection) choiceSection.style.display = 'none';
                if (tfSection) tfSection.style.display = 'none';
                if (matchSection) matchSection.style.display = 'none';

                // Show correct one
                if (currentType === 'multipleChoice' && choiceSection) {
                    choiceSection.style.display = 'block';
                } else if (currentType === 'trueFalse' && tfSection) {
                    tfSection.style.display = 'block';
                } else if (currentType === 'matchingType' && matchSection) {
                    matchSection.style.display = 'block';
                }

                // Now populate data
                if (currentType === 'multipleChoice' && q.choices) {
                    const choiceInputs = item.querySelectorAll('.choice-input');
                    q.choices.forEach((choice, ci) => {
                        if (choiceInputs[ci]) choiceInputs[ci].value = choice;
                    });
                    const radios = item.querySelectorAll('.correct-choice-radio');
                    const correctIndex = q.choices.indexOf(q.correctAnswer);
                    if (radios[correctIndex]) {
                        radios[correctIndex].checked = true;
                        window.updateCorrectChoiceIndicator(radios[correctIndex]);
                    }
                } else if (currentType === 'trueFalse') {
                    const answerSelect = item.querySelector('.manual-answer-input');
                    if (answerSelect) answerSelect.value = q.correctAnswer || '';
                } else if (currentType === 'matchingType' && q.pairs) {
                    const pairsContainer = item.querySelector('.matching-pairs');
                    // Keep first pair, remove others
                    const existingPairs = pairsContainer.querySelectorAll('.matching-pair');
                    existingPairs.forEach((p, idx) => { if (idx > 0) p.remove(); });

                    const firstPair = pairsContainer.querySelector('.matching-pair');
                    if (firstPair && q.pairs.length) {
                        const leftInput = firstPair.querySelector('.matching-left-input');
                        const rightInput = firstPair.querySelector('.matching-right-input');
                        if (leftInput) leftInput.value = q.pairs[0].question || q.pairs[0].left || '';
                        if (rightInput) rightInput.value = q.pairs[0].answer || q.pairs[0].right || '';
                    }

                    // Add remaining pairs
                    for (let i = 1; i < q.pairs.length; i++) {
                        const pair = q.pairs[i];
                        const newPair = document.createElement('div');
                        newPair.className = 'matching-pair';
                        const pairNum = i + 1;
                        newPair.dataset.pair = pairNum;
                        const qNum = index + 1;
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
        }
    };


    // ===== Expose to window =====
    window.updateAssessmentUploadVisibility = updateAssessmentUploadVisibility;
    window.updateQuestionFieldsForType = updateQuestionFieldsForType;
    window.handleAssessmentFileChange = handleAssessmentFileChange;
    window.updateCorrectChoiceIndicator = updateCorrectChoiceIndicator;
    window.clearAssessmentUploadState = clearAssessmentUploadState;
    window.addMatchingPair = addMatchingPair;
    window.removeMatchingPair = removeMatchingPair;

    // ===== Initialize event listeners =====
    document.getElementById('preAssessmentFile')?.addEventListener('change', function () {
        handleAssessmentFileChange('preAssessment');
    });
    document.getElementById('postAssessmentFile')?.addEventListener('change', function () {
        handleAssessmentFileChange('postAssessment');
    });

    // ===== Initialize exam type change handlers =====
    ['preAssessment', 'postAssessment'].forEach(prefix => {
        const select = document.getElementById(`${prefix}ExamType`);
        if (select) {
            select.addEventListener('change', function () {
                updateAssessmentUploadVisibility(prefix);
                updateQuestionFieldsForType(prefix);
            });
            // Initial setup
            updateAssessmentUploadVisibility(prefix);
            updateQuestionFieldsForType(prefix);
        }
    });

})();