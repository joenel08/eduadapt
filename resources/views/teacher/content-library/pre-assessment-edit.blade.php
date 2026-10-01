@extends('layouts.teacher-student')

@section('page_title', 'Edit Pre-Assessment - ' . $subject . ' - ' . $week)
@section('page', 'content-library')

@php
    if (!isset($item)) {
        abort(404, 'Content not found.');
    }

    $settings = $item->settings ?? [];
    if (is_string($settings)) {
        $decoded = json_decode($settings, true);
        $settings = is_array($decoded) ? $decoded : [];
    }

    $questions = $item->questions ?? [];
    if (is_string($questions)) {
        $decoded = json_decode($questions, true);
        $questions = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
    }
    if (!is_array($questions)) $questions = [];

    $timer = $settings['timer'] ?? '00:00:00';
    $parts = explode(':', $timer);
    $hours   = isset($parts[0]) ? (int) $parts[0] : 0;
    $minutes = isset($parts[1]) ? (int) $parts[1] : 0;
    $seconds = isset($parts[2]) ? (int) $parts[2] : 0;

    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $settingsJson  = json_encode($settings,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

@section('content')
<div class="back-button show">
    <button type="button" class="back-btn" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i> Back to Week
    </button>
</div>

<div class="week-content-page">
    <div class="week-header">
        <i class="fas fa-clipboard"></i> Edit Pre-Assessment
    </div>
    <div class="week-subheader">
        Update your Pre-Assessment Exam – add or edit questions
    </div>

    {{-- ✅ FIX: surface validation errors --}}
    @if ($errors->any())
        <div class="message-box" style="background:#ffe4e6;color:#991b1b;margin-bottom:16px;padding:12px 16px;border-radius:8px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="message-box" style="background:#eef2ff;color:#1d4ed8;margin-bottom:16px;padding:12px 16px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form
                id="preAssessmentForm"
                action="{{ route('teacher.content-library.update', [$grade, $term, $subject, $week, 'preAssessment', $item->id]) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                {{-- ✅ FIX: hidden input_method so the controller's validator passes --}}
                <input type="hidden" name="input_method" id="inputMethodInput" value="{{ old('input_method', $item->input_method ?? 'manual') }}">

                {{-- Exam type --}}
                <div class="form-group">
                    <label for="exam_type">Exam Type:</label>
                    <select name="exam_type" id="exam_type" class="form-control">
                        <option value="multipleChoice" {{ ($item->exam_type ?? '') === 'multipleChoice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="trueFalse"      {{ ($item->exam_type ?? '') === 'trueFalse'      ? 'selected' : '' }}>True or False</option>
                        <option value="matchingType"   {{ ($item->exam_type ?? '') === 'matchingType'   ? 'selected' : '' }}>Matching Type</option>
                        <option value="mixed"          {{ ($item->exam_type ?? '') === 'mixed'          ? 'selected' : '' }}>Mixed (Combined)</option>
                    </select>
                </div>

                {{-- Input method (display only) --}}
                <div class="input-method-tabs">
                    <div class="input-method-tab active">
                        @if(($item->input_method ?? 'upload') === 'upload')
                            <i class="fas fa-file-upload"></i> Uploaded File
                        @else
                            <i class="fas fa-keyboard"></i> Manual
                        @endif
                    </div>
                </div>

                @if(($item->input_method ?? '') === 'upload')
                    <div class="form-group">
                        <label>Uploaded File:</label>
                        <p><strong>{{ $item->file_name ?? 'None' }}</strong></p>
                        <p class="text-muted" style="font-size:12px;">
                            File cannot be changed here. Delete and recreate the assessment if you need to replace the file.
                        </p>
                    </div>
                @endif

                {{-- Questions --}}
                <div class="form-group">
                    <label style="font-size:18px;font-weight:700;display:block;margin-bottom:15px;">Questions</label>

                    <div style="display:flex;gap:10px;margin-bottom:20px;">
                        <input type="number" id="questionCount" value="3" min="1" max="50"
                               style="width:100px;padding:8px;border:1px solid #ddd;border-radius:6px;">
                        <button type="button" onclick="generateQuestions()" class="btn btn-save">
                            <i class="fas fa-magic"></i> Add Questions
                        </button>
                    </div>

                    <div class="questions-container" id="questionsContainer"
                         style="max-height:500px;overflow-y:auto;padding:10px;background:#f9f9f9;border:1px solid #ddd;border-radius:8px;">
                    </div>

                    {{-- Only `questions` matters to the server; drop the dead manual_questions input --}}
                    <input type="hidden" name="questions" id="questionsInput" value="{{ $questionsJson }}">
                </div>

                {{-- Settings hidden input --}}
                <input type="hidden" name="settings" id="settingsInput" value="{{ $settingsJson }}">

                {{-- Randomization --}}
                <div class="randomization-options" id="randomizationOptions">
                    <div class="randomization-options-title">Randomization (for student view)</div>
                    <label class="toggle-line">
                        <input type="checkbox" name="shuffle_questions" value="1"
                               {{ ($settings['shuffle_questions'] ?? false) ? 'checked' : '' }}>
                        Shuffle / Randomize Question Order
                    </label>
                    <label class="toggle-line" id="shuffleChoicesRow">
                        <input type="checkbox" name="shuffle_choices" value="1"
                               {{ ($settings['shuffle_choices'] ?? false) ? 'checked' : '' }}>
                        Shuffle / Randomize Choice Order
                    </label>
                </div>

                <div class="divider-line"></div>

                {{-- Timer --}}
                <div class="form-group">
                    <label>Timer (HH:MM:SS):</label>
                    <div class="time-picker-group">
                        <input type="number" name="hours"   min="0" max="23" value="{{ $hours }}"   style="max-width:80px;">
                        <span class="time-unit">:</span>
                        <input type="number" name="minutes" min="0" max="59" value="{{ $minutes }}" style="max-width:80px;">
                        <span class="time-unit">:</span>
                        <input type="number" name="seconds" min="0" max="59" value="{{ $seconds }}" style="max-width:80px;">
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn btn-save">Update Pre-Assessment</button>
                    <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const examTypeSelect = document.getElementById('exam_type');
    const isMixed = () => examTypeSelect.value === 'mixed';
    const getDefaultType = () => isMixed() ? 'multipleChoice' : examTypeSelect.value;

    // ============================================================
    // HTML template for a question block
    // ============================================================
    function generateQuestionHtml(qNum, questionType, data) {
        data = data || {};
        const type = questionType || getDefaultType();
        const isMixedMode = isMixed();
        const choices = Array.isArray(data.choices) ? data.choices : [];
        const choiceImages = Array.isArray(data.choiceImages) ? data.choiceImages : [];

        return `
            <div class="question-item" data-q="${qNum}" data-question-type="${type}">
                <div class="question-item-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span style="font-weight:700;">Question ${qNum}</span>
                    <div>
                        <button type="button" class="remove-question-btn" onclick="removeQuestion(this)" title="Delete Question"
                                style="background:#ff6b6b;color:white;border:none;border-radius:4px;padding:4px 8px;cursor:pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                        ${isMixedMode ? `
                            <select class="question-type-select" data-q="${qNum}" onchange="onQuestionTypeChange(this)"
                                    style="margin-left:8px;padding:4px;border:1px solid #ddd;border-radius:4px;">
                                <option value="multipleChoice" ${type === 'multipleChoice' ? 'selected' : ''}>Multiple Choice</option>
                                <option value="trueFalse"      ${type === 'trueFalse'      ? 'selected' : ''}>True or False</option>
                                <option value="matchingType"   ${type === 'matchingType'   ? 'selected' : ''}>Matching Type</option>
                            </select>
                        ` : ''}
                    </div>
                </div>

                <div class="question-input-group" style="margin-bottom:10px;">
                    <label style="display:block;font-weight:600;font-size:13px;">Question Text:</label>
                    <input type="text" class="question-input" autocomplete="off" placeholder="Enter question text"
                           data-q="${qNum}" name="question_text_${qNum}"
                           value="${escapeAttr(data.question || '')}"
                           style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                </div>

                <div class="form-group" style="margin-top:6px;">
                    <label style="font-size:12px;font-weight:600;">Question Image (optional):</label>
                    <input type="file" class="question-image-input" accept="image/*" name="question_image_${qNum}" style="display:block;margin-top:4px;">
                    <div class="question-image-preview" data-q="${qNum}" style="margin-top:4px;">
                        ${data.image ? `<img src="${data.image}" style="max-width:100%;max-height:100px;border-radius:4px;border:1px solid #ddd;">` : ''}
                    </div>
                </div>

                <!-- Multiple Choice -->
                <div class="choice-inputs" data-q="${qNum}" style="display:${type === 'multipleChoice' ? 'block' : 'none'};">
                    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:5px;">Choices:</label>
                    ${[0,1,2,3].map(ci => {
                        const val = choices[ci] || '';
                        const isCorrect = data.correctAnswer && data.correctAnswer === val && val !== '';
                        return `
                        <div class="choice-input-row" style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <span style="font-weight:600;min-width:20px;">${String.fromCharCode(65 + ci)}.</span>
                            <input type="text" class="choice-input" autocomplete="off"
                                   placeholder="Choice ${String.fromCharCode(65 + ci)}"
                                   data-q="${qNum}" data-choice="${ci}"
                                   name="choice_text_${qNum}_${ci}"
                                   value="${escapeAttr(val)}"
                                   style="flex:1;padding:6px;border:1px solid #ddd;border-radius:6px;">
                            <input type="file" class="choice-image-input" accept="image/*"
                                   name="choice_image_${qNum}_${ci}"
                                   style="flex:0.6;padding:4px;font-size:12px;">
                            <div class="choice-image-preview" style="margin-left:4px;">
                                ${choiceImages[ci] ? `<img src="${choiceImages[ci]}" style="max-height:40px;border-radius:4px;border:1px solid #ddd;">` : ''}
                            </div>
                            <label class="correct-choice-marker" style="display:flex;align-items:center;gap:4px;cursor:pointer;">
                                <input type="radio" name="correct_${qNum}" class="correct-choice-radio"
                                       value="${ci}" onchange="updateCorrectIndicator(this)"
                                       ${isCorrect ? 'checked' : ''}>
                                <span class="correct-indicator">${isCorrect ? '(●)' : '( )'}</span>
                            </label>
                        </div>
                    `}).join('')}
                </div>

                <!-- True/False -->
                <div class="truefalse-inputs" data-q="${qNum}" style="display:${type === 'trueFalse' ? 'block' : 'none'};">
                    <div class="question-answer-box" style="padding:10px;background:#f8f9fa;border-radius:6px;">
                        <label style="display:block;font-weight:600;font-size:13px;margin-bottom:5px;">Answer Key</label>
                        <select class="manual-answer-input" name="tf_answer_${qNum}"
                                style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                            <option value="">Select answer</option>
                            <option value="True"  ${data.correctAnswer === 'True'  ? 'selected' : ''}>True</option>
                            <option value="False" ${data.correctAnswer === 'False' ? 'selected' : ''}>False</option>
                        </select>
                    </div>
                </div>

                <!-- Matching -->
                <div class="matching-inputs" data-q="${qNum}" style="display:${type === 'matchingType' ? 'block' : 'none'};">
                    <label style="display:block;font-weight:600;font-size:13px;margin-bottom:5px;">Matching Pairs:</label>
                    <div class="matching-pairs">
                        ${(data.pairs && data.pairs.length ? data.pairs : [{question:'',answer:''}]).map((pair, pi) => `
                            <div class="matching-pair" data-pair="${pi+1}" style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <input type="text" class="matching-left-input" autocomplete="off" placeholder="Left item"
                                       data-q="${qNum}" data-pair="${pi+1}" name="matching_left_${qNum}_${pi+1}"
                                       value="${escapeAttr(pair.question || pair.left || '')}"
                                       style="flex:1;padding:6px;border:1px solid #ddd;border-radius:6px;">
                                <span style="font-weight:bold;">↔</span>
                                <input type="text" class="matching-right-input" autocomplete="off" placeholder="Right item"
                                       data-q="${qNum}" data-pair="${pi+1}" name="matching_right_${qNum}_${pi+1}"
                                       value="${escapeAttr(pair.answer || pair.right || '')}"
                                       style="flex:1;padding:6px;border:1px solid #ddd;border-radius:6px;">
                                <button type="button" class="remove-pair" onclick="removeMatchingPair(this)"
                                        style="background:#ff6b6b;color:white;border:none;border-radius:4px;padding:2px 6px;cursor:pointer;">✕</button>
                            </div>
                        `).join('')}
                    </div>
                    <button type="button" class="add-matching-pair" data-q="${qNum}" onclick="addMatchingPair(this)"
                            style="margin-top:6px;padding:4px 12px;background:#0066CC;color:white;border:none;border-radius:4px;cursor:pointer;">+ Add Pair</button>
                </div>

                <hr style="margin:12px 0;">
            </div>
        `;
    }

    // small helper to safely put strings into HTML attributes
    function escapeAttr(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // ============================================================
    // Populate existing questions
    // ============================================================
    function populateQuestions(questions) {
        const container = document.getElementById('questionsContainer');
        container.innerHTML = '';

        if (!questions || !questions.length) {
            container.innerHTML = '<p style="padding:20px;text-align:center;color:#999;">No questions. Click "Add Questions" to create some.</p>';
            return;
        }

        let html = '';
        questions.forEach((q, idx) => {
            const qNum = idx + 1;
            const type = q.type || getDefaultType();
            html += generateQuestionHtml(qNum, type, q);
        });
        container.innerHTML = html;

        attachPreviewHandlers(container);
        container.querySelectorAll('.correct-choice-radio').forEach(radio => {
            radio.addEventListener('change', function() { updateCorrectIndicator(this); });
        });
    }

    // ============================================================
    // Generate new (blank) questions
    // ============================================================
    function generateQuestions() {
        const count = parseInt(document.getElementById('questionCount').value) || 3;
        const container = document.getElementById('questionsContainer');
        const existing = container.querySelectorAll('.question-item').length;

        let html = '';
        for (let i = 1; i <= count; i++) {
            const qNum = existing + i;
            html += generateQuestionHtml(qNum, getDefaultType(), {});
        }
        container.insertAdjacentHTML('beforeend', html);

        attachPreviewHandlers(container);
        container.querySelectorAll('.correct-choice-radio').forEach(radio => {
            radio.addEventListener('change', function() { updateCorrectIndicator(this); });
        });
    }

    // ============================================================
    // Preview handlers + helpers
    // ============================================================
    function attachPreviewHandlers(container) {
        container.querySelectorAll('.question-image-input').forEach(inp => {
            inp.addEventListener('change', function() {
                const preview = this.closest('.question-item').querySelector('.question-image-preview');
                preview.innerHTML = '';
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        preview.innerHTML = `<img src="${ev.target.result}" style="max-width:100%;max-height:100px;border-radius:4px;border:1px solid #ddd;">`;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
        container.querySelectorAll('.choice-image-input').forEach(inp => {
            inp.addEventListener('change', function() {
                const parent = this.closest('.choice-input-row');
                const preview = parent.querySelector('.choice-image-preview');
                preview.innerHTML = '';
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        preview.innerHTML = `<img src="${ev.target.result}" style="max-height:40px;border-radius:4px;border:1px solid #ddd;">`;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    }

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
        const sections = {
            multipleChoice: item.querySelector('.choice-inputs'),
            trueFalse:      item.querySelector('.truefalse-inputs'),
            matchingType:   item.querySelector('.matching-inputs'),
        };
        Object.entries(sections).forEach(([k, el]) => {
            if (el) el.style.display = (k === type) ? 'block' : 'none';
        });
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
            <input type="text" class="matching-left-input" autocomplete="off" placeholder="Left item"
                   data-q="${qIndex}" data-pair="${pairCount}" name="matching_left_${qIndex}_${pairCount}"
                   style="flex:1;padding:6px;border:1px solid #ddd;border-radius:6px;">
            <span style="font-weight:bold;">↔</span>
            <input type="text" class="matching-right-input" autocomplete="off" placeholder="Right item"
                   data-q="${qIndex}" data-pair="${pairCount}" name="matching_right_${qIndex}_${pairCount}"
                   style="flex:1;padding:6px;border:1px solid #ddd;border-radius:6px;">
            <button type="button" class="remove-pair" onclick="removeMatchingPair(this)"
                    style="background:#ff6b6b;color:white;border:none;border-radius:4px;padding:2px 6px;cursor:pointer;">✕</button>
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
    // Build settings JSON from form fields
    // ============================================================
    function buildSettingsJson() {
        const pad = n => String(n).padStart(2, '0');
        const h = parseInt(document.querySelector('input[name="hours"]').value || 0, 10);
        const m = parseInt(document.querySelector('input[name="minutes"]').value || 0, 10);
        const s = parseInt(document.querySelector('input[name="seconds"]').value || 0, 10);
        return JSON.stringify({
            timer: `${pad(h)}:${pad(m)}:${pad(s)}`,
            shuffle_questions: !!document.querySelector('input[name="shuffle_questions"]').checked,
            shuffle_choices:   !!document.querySelector('input[name="shuffle_choices"]').checked,
        });
    }

    // ============================================================
    // Submit — rebuild questions + settings before posting
    // ============================================================
    document.getElementById('preAssessmentForm').addEventListener('submit', function(e) {
        // ✅ always refresh settings so timer/shuffle changes are saved
        document.getElementById('settingsInput').value = buildSettingsJson();

        const container = document.getElementById('questionsContainer');
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

            const questionData = { type: qType, question: qText, image: null };

            if (qType === 'multipleChoice') {
                // ✅ preserve slot alignment — don't filter empties, they belong to specific radio values
                const choices = Array.from(item.querySelectorAll('.choice-input'))
                    .map(inp => inp.value.trim());

                const nonEmpty = choices.filter(Boolean).length;
                if (nonEmpty < 2) {
                    alert(`Question ${idx+1} needs at least two choices.`);
                    hasError = true;
                    return;
                }

                const radio = item.querySelector('.correct-choice-radio:checked');
                if (!radio) {
                    alert(`Question ${idx+1} needs a correct answer selected.`);
                    hasError = true;
                    return;
                }
                const correctIndex = parseInt(radio.value, 10);
                if (!choices[correctIndex]) {
                    alert(`Question ${idx+1}: the choice marked as correct is empty.`);
                    hasError = true;
                    return;
                }

                questionData.choices = choices;
                questionData.choiceImages = [];
                questionData.correctAnswer = choices[correctIndex];
                questionData.correctIndex = correctIndex;

            } else if (qType === 'trueFalse') {
                const correctAnswer = item.querySelector('.manual-answer-input')?.value || '';
                if (!correctAnswer) {
                    alert(`Question ${idx+1} needs a correct answer (True/False).`);
                    hasError = true;
                    return;
                }
                questionData.choices = ['True', 'False'];
                questionData.correctAnswer = correctAnswer;

            } else if (qType === 'matchingType') {
                const pairs = [];
                item.querySelectorAll('.matching-pair').forEach(pair => {
                    const left = pair.querySelector('.matching-left-input')?.value.trim() || '';
                    const right = pair.querySelector('.matching-right-input')?.value.trim() || '';
                    if (left && right) pairs.push({ question: left, answer: right });
                });
                if (!pairs.length) {
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

        // ✅ single source of truth — the controller only reads `questions`
        document.getElementById('questionsInput').value = JSON.stringify(questions);
    });

    // ============================================================
    // Init
    // ============================================================
    (function() {
        const existingQuestions = @json($questions);
        populateQuestions(existingQuestions);
    })();

    // Keep the mixed-type default in sync when the user changes exam type
    examTypeSelect.addEventListener('change', function() {
        // Existing questions keep their own types; new ones will default to the new exam type.
    });
</script>
@endpush