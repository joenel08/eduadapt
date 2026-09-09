@extends('layouts.teacher-student')

@section('page_title', 'Add Mini Quiz - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<div class="back-button show">
    <button class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Intervention
    </button>
</div>

<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-question-circle"></i> Add Mini Quiz
    </div>
    <div class="week-subheader">Create a mini‑quiz for this intervention level</div>

    <div class="card">
        <div class="card-body">
            <form id="quizForm" action="{{ route('teacher.content-library.intervention.quiz.store', [$grade, $term, $subject, $week]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- Level selector --}}
                <div class="form-group">
                    <label for="level">Select Level:</label>
                    <select name="level" id="level" class="form-control" required>
                        <option value="">-- Select Level --</option>
                        <option value="basic">🔹 Basic (Below Average)</option>
                        <option value="standard" selected>🔹 Standard (Average)</option>
                        <option value="advanced">🔹 Advanced (Above Average)</option>
                    </select>
                </div>

                {{-- Exam Type --}}
                <div class="form-group">
                    <label for="quizExamType">Exam Type:</label>
                    <select name="exam_type" id="quizExamType" class="form-control">
                        <option value="multipleChoice">Multiple Choice</option>
                        <option value="trueFalse">True or False</option>
                        <option value="matchingType">Matching Type</option>
                        <option value="mixed">Mixed (Combined)</option>
                    </select>
                </div>

                {{-- Input Method Tabs --}}
                <div class="input-method-tabs">
                    <button type="button" class="input-method-tab active" data-method="upload" onclick="switchQuizMethod('upload')">
                        <i class="fas fa-file-upload"></i> Upload File
                    </button>
                    <button type="button" class="input-method-tab" data-method="manual" onclick="switchQuizMethod('manual')">
                        <i class="fas fa-keyboard"></i> Manual Input
                    </button>
                </div>

                {{-- Upload Method --}}
                <div class="input-method-content active" id="quizUploadContent">
                    <div class="form-group">
                        <label>Upload Mini Quiz (Excel):</label>
                        <div id="quizUploadFormatHint" class="excel-format-hint"></div>
                        <p id="quizUploadInstruction" class="upload-instruction"></p>

                        {{-- ✅ NEW: Download Template Buttons --}}
                        <div class="template-download-buttons" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; margin-top: 12px;">
                            <button type="button" class="btn btn-publish btn-sm template-dl-btn" data-type="multipleChoice" style="display:none;" onclick="downloadQuizTemplate('multipleChoice')">
                                <i class="fas fa-file-download"></i> &nbsp;Download Multiple Choice Template
                            </button>
                            <button type="button" class="btn btn-publish btn-sm template-dl-btn" data-type="trueFalse" style="display:none;" onclick="downloadQuizTemplate('trueFalse')">
                                <i class="fas fa-file-download"></i> &nbsp;Download True or False Template
                            </button>
                            <button type="button" class="btn btn-publish btn-sm template-dl-btn" data-type="matchingType" style="display:none;" onclick="downloadQuizTemplate('matchingType')">
                                <i class="fas fa-file-download"></i> &nbsp;Download Matching Type Template
                            </button>
                            <button type="button" class="btn btn-publish btn-sm template-dl-btn" data-type="mixed" style="display:none;" onclick="downloadQuizTemplate('mixed')">
                                <i class="fas fa-file-download"></i>&nbsp;Download Mixed Type Template
                            </button>
                        </div>

                        <div class="file-upload-area" onclick="document.getElementById('quizFileInput').click()">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <div class="upload-text">Click to upload Excel file</div>
                            <div class="upload-subtext">.xlsx / .xls (Max 20MB)</div>
                        </div>
                        <input type="file" name="file" id="quizFileInput" style="display:none;" accept=".xlsx,.xls">
                        <p id="quizFileName" style="color:#0066CC; font-weight:600; margin-top:10px;"></p>
                        <div id="quizUploadPreview" class="assessment-preview" style="display:none;"></div>
                        @error('file')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <input type="hidden" name="questions" id="questionsInput" value="{{ old('questions') }}">
                </div>

                {{-- Manual Method --}}
                <div class="input-method-content" id="quizManualContent" style="display:none;">
                    <div class="form-group">
                        <label for="questionCount">Number of Questions:</label>
                        <input type="number" id="questionCount" value="3" min="1" max="50" class="form-control">
                    </div>
                    <button type="button" onclick="generateQuizQuestions()" class="btn btn-save" style="width:100%; margin-bottom:15px;">
                        <i class="fas fa-magic"></i> Add Questions
                    </button>
                    <div class="questions-container" id="quizQuestionsContainer"></div>
                    <input type="hidden" name="manual_questions" id="manualQuestionsInput" value="{{ old('manual_questions') }}">
                </div>

                {{-- Randomization Options --}}
                <div class="randomization-options" id="randomizationOptions">
                    <div class="randomization-options-title">Randomization (for student view)</div>
                    <label class="toggle-line">
                        <input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions') ? 'checked' : '' }}> Shuffle / Randomize Question Order
                    </label>
                    <label class="toggle-line" id="shuffleChoicesRow">
                        <input type="checkbox" name="shuffle_choices" value="1" {{ old('shuffle_choices') ? 'checked' : '' }}> Shuffle / Randomize Choice Order
                    </label>
                </div>

                <div class="divider-line"></div>

                {{-- Timer --}}
                <div class="form-group">
                    <label>Quiz Timer (HH:MM:SS):</label>
                    <div class="time-picker-group">
                        <input type="number" name="hours" placeholder="HH" min="0" max="23" value="{{ old('hours', 0) }}" style="max-width: 80px;">
                        <span class="time-unit">:</span>
                        <input type="number" name="minutes" placeholder="MM" min="0" max="59" value="{{ old('minutes', 0) }}" style="max-width: 80px;">
                        <span class="time-unit">:</span>
                        <input type="number" name="seconds" placeholder="SS" min="0" max="59" value="{{ old('seconds', 0) }}" style="max-width: 80px;">
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-save">Save Quiz</button>
                    <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ============================================================
    // 1. Upload / Manual toggle
    // ============================================================
    let currentMethod = 'upload';
    let uploadedQuestions = [];

    function switchQuizMethod(method) {
        currentMethod = method;
        document.getElementById('quizUploadContent').style.display = (method === 'upload') ? 'block' : 'none';
        document.getElementById('quizManualContent').style.display = (method === 'manual') ? 'block' : 'none';
        document.querySelectorAll('.input-method-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.method === method);
        });
    }

    // ============================================================
    // 2. Upload Format Hint (Excel)
    // ============================================================
    function renderUploadFormatHint(examType) {
        const hintEl = document.getElementById('quizUploadFormatHint');
        const instructionEl = document.getElementById('quizUploadInstruction');
        let columns, sampleRows, instruction;
        if (examType === 'trueFalse') {
            columns = ['Question', 'Correct Answer'];
            sampleRows = [
                ['The Earth revolves around the Sun.', 'True'],
                ['HTML is a programming language.', 'False']
            ];
            instruction = 'Upload an Excel file containing the Question and Correct Answer columns. The system will automatically generate the True and False choices.';
        } else if (examType === 'matchingType') {
            columns = ['Question', 'Answer'];
            sampleRows = [
                ['Philippines', 'Manila'],
                ['Japan', 'Tokyo'],
                ['Korea', 'Seoul']
            ];
            instruction = 'Upload an Excel file containing Question and Answer columns. Each row is one matching pair.';
        } else if (examType === 'mixed') {
            columns = ['Type', 'Question', 'Choice 1', 'Choice 2', 'Choice 3', 'Choice 4', 'Correct Answer', 'Left', 'Right'];
            sampleRows = [
                ['MC', 'What is 2+2?', '3', '4', '5', '6', '4', '', ''],
                ['TF', 'The sun is hot.', '', '', '', '', 'True', '', ''],
                ['MT', 'Match countries', '', '', '', '', '', 'Philippines', 'Manila']
            ];
            instruction = 'For Mixed type, include a "Type" column with values: MC, TF, or MT. For MC, provide choices and correct answer. For TF, provide correct answer (True/False). For MT, provide Left and Right columns (Question/Answer for each pair).';
        } else { // multipleChoice
            columns = ['Question', 'Choice 1', 'Choice 2', 'Choice 3', 'Choice 4', 'Correct Answer'];
            sampleRows = [
                ['What is HTML?', 'Programming Language', 'Markup Language', 'Database', 'Browser', 'Markup Language']
            ];
            instruction = 'Upload an Excel file with questions, choices, and the correct answer in one sheet.';
        }
        const headerCells = columns.map(c => `<th>${c}</th>`).join('');
        const bodyRows = sampleRows.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('');
        hintEl.innerHTML = `
            <strong>Sheet name: Questions</strong>
            <table class="excel-format-table"><thead><tr>${headerCells}</tr></thead><tbody>${bodyRows}</tbody></table>
        `;
        instructionEl.textContent = instruction;
    }
    renderUploadFormatHint(document.getElementById('quizExamType').value);

    // ============================================================
    // 2b. Update template download button visibility
    // ============================================================
    function updateDownloadButtonVisibility(examType) {
        document.querySelectorAll('.template-dl-btn').forEach(btn => {
            btn.style.display = (btn.dataset.type === examType) ? 'inline-flex' : 'none';
        });
    }

    // ============================================================
    // 2c. Download Template (using XLSX)
    // ============================================================
    function downloadQuizTemplate(type) {
        if (typeof XLSX === 'undefined') {
            alert('XLSX library not loaded. Please refresh the page or include SheetJS library.');
            return;
        }

        let rows = [];
        let filename = '';

        switch (type) {
            case 'multipleChoice':
                rows = [
                    ['Question', 'Choice 1', 'Choice 2', 'Choice 3', 'Choice 4', 'Correct Answer'],
                    ['What is the capital of France?', 'London', 'Paris', 'Berlin', 'Madrid', 'Paris'],
                    ['Which planet is known as the Red Planet?', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'Mars']
                ];
                filename = 'multiple_choice_template.xlsx';
                break;

            case 'trueFalse':
                rows = [
                    ['Question', 'Correct Answer'],
                    ['The Earth revolves around the Sun.', 'True'],
                    ['HTML is a programming language.', 'False'],
                    ['Water boils at 100°C.', 'True']
                ];
                filename = 'true_false_template.xlsx';
                break;

            case 'matchingType':
                rows = [
                    ['Question', 'Answer'],
                    ['Country: Philippines', 'Manila'],
                    ['Country: Japan', 'Tokyo'],
                    ['Country: France', 'Paris']
                ];
                filename = 'matching_type_template.xlsx';
                break;

            case 'mixed':
                rows = [
                    ['Type', 'Question', 'Choice 1', 'Choice 2', 'Choice 3', 'Choice 4', 'Correct Answer', 'Left', 'Right'],
                    ['MC', 'What is 2+2?', '3', '4', '5', '6', '4', '', ''],
                    ['TF', 'The sun is hot.', '', '', '', '', 'True', '', ''],
                    ['MT', 'Match countries', '', '', '', '', '', 'Philippines', 'Manila']
                ];
                filename = 'mixed_template.xlsx';
                break;

            default:
                alert('Unknown template type.');
                return;
        }

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        ws['!cols'] = rows[0].map(() => ({ wch: 20 }));
        XLSX.utils.book_append_sheet(wb, ws, 'Questions');
        XLSX.writeFile(wb, filename);
    }

    // ============================================================
    // 3. Excel file parsing (using XLSX)
    // ============================================================
    document.getElementById('quizFileInput').addEventListener('change', function(e) {
        const file = this.files[0];
        const nameDisplay = document.getElementById('quizFileName');
        const previewEl = document.getElementById('quizUploadPreview');
        const questionsInput = document.getElementById('questionsInput');
        if (!file) {
            nameDisplay.textContent = '';
            previewEl.style.display = 'none';
            previewEl.innerHTML = '';
            uploadedQuestions = [];
            questionsInput.value = '';
            return;
        }
        nameDisplay.textContent = file.name;
        if (typeof XLSX === 'undefined') {
            alert('Excel parser library not loaded. Please include XLSX library.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {
                    type: 'array'
                });
                const sheetName = workbook.SheetNames.find(name => name.toLowerCase().trim() === 'questions') || workbook.SheetNames[0];
                const sheet = workbook.Sheets[sheetName];
                const rows = XLSX.utils.sheet_to_json(sheet, {
                    header: 1,
                    defval: ''
                });
                const qs = rows.slice(1).map(row => ({
                    question: row[0] || 'Sample',
                    correctAnswer: row[1] || ''
                }));
                uploadedQuestions = qs;
                questionsInput.value = JSON.stringify(qs);
                previewEl.innerHTML = `<strong>${qs.length} question(s) loaded successfully.</strong>`;
                previewEl.style.display = 'block';
            } catch (err) {
                alert('Error parsing Excel: ' + err.message);
                questionsInput.value = '';
                previewEl.style.display = 'none';
            }
        };
        reader.readAsArrayBuffer(file);
    });

    // ============================================================
    // 4. Manual Question Generation (with images)
    // ============================================================
    function generateQuizQuestions() {
        const count = parseInt(document.getElementById('questionCount').value) || 3;
        const container = document.getElementById('quizQuestionsContainer');
        const examType = document.getElementById('quizExamType').value;
        const isMixed = (examType === 'mixed');
        let html = '';
        for (let i = 1; i <= count; i++) {
            const qNum = container.querySelectorAll('.question-item').length + i;
            const defaultType = isMixed ? 'multipleChoice' : examType;
            html += `
            <div class="question-item" data-q="${qNum}" data-question-type="${defaultType}">
                <div class="question-item-header">
                    Question ${qNum}
                    <button type="button" class="remove-question-btn" onclick="removeQuestion(this)" title="Delete Question">
                        <i class="fas fa-trash"></i>
                    </button>
                    ${isMixed ? `
                    <select class="question-type-select" data-q="${qNum}" onchange="onQuestionTypeChange(this)">
                        <option value="multipleChoice" ${defaultType === 'multipleChoice' ? 'selected' : ''}>Multiple Choice</option>
                        <option value="trueFalse" ${defaultType === 'trueFalse' ? 'selected' : ''}>True or False</option>
                        <option value="matchingType" ${defaultType === 'matchingType' ? 'selected' : ''}>Matching Type</option>
                    </select>
                    ` : ''}
                </div>

                <div class="question-input-group">
                    <input type="text" class="question-input" placeholder="Enter question text" name="question_text_${qNum}">
                </div>

                <div class="form-group" style="margin-top:6px;">
                    <label style="font-size:12px; font-weight:600;">Question Image (optional):</label>
                    <input type="file" class="question-image-input" accept="image/*" name="question_image_${qNum}">
                    <div class="question-image-preview" style="margin-top:4px;"></div>
                </div>

                <!-- Multiple Choice -->
                <div class="choice-inputs" style="display: ${defaultType === 'multipleChoice' ? 'block' : 'none'};">
                    ${[0,1,2,3].map(ci => `
                    <div class="choice-input-row">
                        <span>${String.fromCharCode(65 + ci)}.</span>
                        <input type="text" class="choice-input" placeholder="Choice ${String.fromCharCode(65 + ci)}" name="choice_text_${qNum}_${ci}">
                        <input type="file" class="choice-image-input" accept="image/*" name="choice_image_${qNum}_${ci}" style="flex:0.6; padding:4px; font-size:12px;">
                        <div class="choice-image-preview"></div>
                        <label class="correct-choice-marker">
                            <input type="radio" name="correct_${qNum}" class="correct-choice-radio" value="${ci}" onchange="updateCorrectIndicator(this)">
                            <span class="correct-indicator">( )</span>
                        </label>
                    </div>
                    `).join('')}
                </div>

                <!-- True/False -->
                <div class="truefalse-inputs" style="display: ${defaultType === 'trueFalse' ? 'block' : 'none'};">
                    <div class="question-answer-box">
                        <label>Answer Key</label>
                        <select class="manual-answer-input" name="tf_answer_${qNum}">
                            <option value="">Select answer</option>
                            <option value="True">True</option>
                            <option value="False">False</option>
                        </select>
                    </div>
                </div>

                <!-- Matching Type -->
                <div class="matching-inputs" style="display: ${defaultType === 'matchingType' ? 'block' : 'none'};">
                    <div class="matching-pairs">
                        <div class="matching-pair" data-pair="1">
                            <input type="text" class="matching-left-input" placeholder="Left item" name="matching_left_${qNum}_1">
                            <span>↔</span>
                            <input type="text" class="matching-right-input" placeholder="Right item" name="matching_right_${qNum}_1">
                            <button type="button" class="remove-pair" onclick="removeMatchingPair(this)">✕</button>
                        </div>
                    </div>
                    <button type="button" class="add-matching-pair" data-q="${qNum}" onclick="addMatchingPair(this)">+ Add Pair</button>
                </div>

                <hr style="margin: 12px 0;">
            </div>
            `;
        }
        container.insertAdjacentHTML('beforeend', html);

        // Attach preview handlers for file inputs
        container.querySelectorAll('.question-image-input').forEach(inp => {
            inp.addEventListener('change', function(e) {
                const preview = this.closest('.question-item').querySelector('.question-image-preview');
                preview.innerHTML = '';
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.innerHTML = `<img src="${ev.target.result}" style="max-width:100%; max-height:100px; border-radius:4px; border:1px solid #ddd;">`;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
        container.querySelectorAll('.choice-image-input').forEach(inp => {
            inp.addEventListener('change', function(e) {
                const parent = this.closest('.choice-input-row');
                const preview = parent.querySelector('.choice-image-preview');
                preview.innerHTML = '';
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.innerHTML = `<img src="${ev.target.result}" style="max-height:40px; border-radius:4px; border:1px solid #ddd;">`;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
        // Update radio indicators
        container.querySelectorAll('.correct-choice-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                updateCorrectIndicator(this);
            });
        });
    }

    // ============================================================
    // 5. Helper functions (same as pre‑assessment)
    // ============================================================
    function removeQuestion(btn) {
        const item = btn.closest('.question-item');
        if (item && item.parentElement.querySelectorAll('.question-item').length > 1) {
            item.remove();
        } else {
            alert('You need at least one question.');
        }
    }

    function onQuestionTypeChange(select) {
        const item = select.closest('.question-item');
        const type = select.value;
        item.dataset.questionType = type;
        const choiceSection = item.querySelector('.choice-inputs');
        const tfSection = item.querySelector('.truefalse-inputs');
        const matchSection = item.querySelector('.matching-inputs');
        if (choiceSection) choiceSection.style.display = (type === 'multipleChoice') ? 'block' : 'none';
        if (tfSection) tfSection.style.display = (type === 'trueFalse') ? 'block' : 'none';
        if (matchSection) matchSection.style.display = (type === 'matchingType') ? 'block' : 'none';
    }

    function updateCorrectIndicator(radio) {
        const item = radio.closest('.question-item');
        if (!item) return;
        item.querySelectorAll('.correct-indicator').forEach(el => el.textContent = '( )');
        const marker = radio.closest('.correct-choice-marker');
        if (marker) marker.querySelector('.correct-indicator').textContent = '(●)';
    }

    function addMatchingPair(btn) {
        const container = btn.closest('.matching-inputs');
        const pairsContainer = container.querySelector('.matching-pairs');
        const pairCount = pairsContainer.querySelectorAll('.matching-pair').length + 1;
        const qIndex = btn.dataset.q;
        const newPair = document.createElement('div');
        newPair.className = 'matching-pair';
        newPair.dataset.pair = pairCount;
        newPair.innerHTML = `
            <input type="text" class="matching-left-input" placeholder="Left item" name="matching_left_${qIndex}_${pairCount}">
            <span>↔</span>
            <input type="text" class="matching-right-input" placeholder="Right item" name="matching_right_${qIndex}_${pairCount}">
            <button type="button" class="remove-pair" onclick="removeMatchingPair(this)">✕</button>
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

    // ============================================================
    // 6. On form submit: build JSON from manual inputs
    // ============================================================
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        if (currentMethod === 'manual') {
            const container = document.getElementById('quizQuestionsContainer');
            const items = container.querySelectorAll('.question-item');
            const questions = [];
            let hasError = false;

            items.forEach((item, idx) => {
                const qType = item.dataset.questionType || 'multipleChoice';
                const qText = item.querySelector('.question-input')?.value.trim() || '';
                if (!qText) {
                    alert(`Question ${idx+1} is missing text.`);
                    hasError = true;
                    return;
                }

                const qNum = item.dataset.q;
                let questionData = {
                    type: qType,
                    question: qText,
                    image: null // will be filled by controller
                };

                if (qType === 'multipleChoice') {
                    const choiceInputs = item.querySelectorAll('.choice-input');
                    const choices = [];
                    choiceInputs.forEach(inp => {
                        const val = inp.value.trim();
                        if (val) choices.push(val);
                    });
                    if (choices.length < 2) {
                        alert(`Question ${idx+1} needs at least two choices.`);
                        hasError = true;
                        return;
                    }
                    const radio = item.querySelector('.correct-choice-radio:checked');
                    let correctIndex = -1;
                    if (radio) correctIndex = parseInt(radio.value, 10);
                    if (correctIndex === -1 || correctIndex >= choices.length) {
                        alert(`Question ${idx+1} needs a correct answer selected.`);
                        hasError = true;
                        return;
                    }
                    questionData.choices = choices;
                    questionData.choiceImages = []; // will be filled by controller
                    questionData.correctAnswer = choices[correctIndex];
                } else if (qType === 'trueFalse') {
                    const answerSelect = item.querySelector('.manual-answer-input');
                    const correctAnswer = answerSelect ? answerSelect.value : '';
                    if (!correctAnswer) {
                        alert(`Question ${idx+1} needs a correct answer (True/False).`);
                        hasError = true;
                        return;
                    }
                    questionData.choices = ['True', 'False'];
                    questionData.correctAnswer = correctAnswer;
                } else if (qType === 'matchingType') {
                    const pairs = [];
                    const pairElements = item.querySelectorAll('.matching-pair');
                    pairElements.forEach(pair => {
                        const left = pair.querySelector('.matching-left-input')?.value.trim() || '';
                        const right = pair.querySelector('.matching-right-input')?.value.trim() || '';
                        if (left && right) {
                            pairs.push({
                                question: left,
                                answer: right
                            });
                        }
                    });
                    if (pairs.length === 0) {
                        alert(`Question ${idx+1} needs at least one matching pair.`);
                        hasError = true;
                        return;
                    }
                    questionData.pairs = pairs;
                }

                questions.push(questionData);
            });

            if (hasError) {
                e.preventDefault();
                return;
            }

            document.getElementById('manualQuestionsInput').value = JSON.stringify(questions);
        } else {
            // Upload method – ensure questions are set
            const questionsInput = document.getElementById('questionsInput');
            if (!questionsInput.value) {
                alert('Please upload a valid Excel file.');
                e.preventDefault();
                return;
            }
        }
    });

    // ============================================================
    // 7. Initialize: update template visibility and exam type change
    // ============================================================
    document.getElementById('quizExamType').addEventListener('change', function() {
        renderUploadFormatHint(this.value);
        updateDownloadButtonVisibility(this.value);
    });

    // Initialise on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateDownloadButtonVisibility(document.getElementById('quizExamType').value);
    });
</script>
@endpush