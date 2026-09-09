<!-- ADD CONTENT TYPE MODAL -->
<div class="modal" id="addContentTypeModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('addContentTypeModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-plus-circle"></i>
            What do you want to add?
        </div>
        <div class="modal-body">
            <div class="content-type-grid">
                <button class="content-type-btn" type="button" onclick="selectContentType('materials')">
                    <i class="fas fa-book-open"></i> Learning Materials
                </button>
                <button class="content-type-btn" type="button" onclick="selectContentType('preAssessment')">
                    <i class="fas fa-clipboard"></i> Pre-Assessment
                </button>
                <button class="content-type-btn" type="button" onclick="selectContentType('postAssessment')">
                    <i class="fas fa-clipboard-check"></i> Post-Assessment
                </button>
                <button class="content-type-btn" type="button" onclick="selectContentType('intervention')">
                    <i class="fas fa-graduation-cap"></i> Learning Intervention + Mini Quiz
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PRE-ASSESSMENT MODAL -->
<div class="modal" id="preAssessmentModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('preAssessmentModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-clipboard"></i>
            Pre-Assessment Setup
        </div>
        <div class="modal-body">

            <div class="form-group">
                <label for="preAssessmentStatus">Status:</label>
                <select id="preAssessmentStatus">
                    <option value="open">Open</option>
                    <option value="updating">Updating</option>
                </select>
                <small style="color:#888;">Closed automatically when due date passes.</small>
            </div>
            <div class="form-group">
                <label for="preAssessmentExamType">Exam Type:</label>
                <select id="preAssessmentExamType">
                    <option value="multipleChoice">Multiple Choice</option>
                    <option value="trueFalse">True or False</option>
                    <option value="matchingType">Matching Type</option>
                    <option value="mixed">Mixed (Combined)</option>
                </select>
            </div>

            <div class="input-method-tabs">
                <button class="input-method-tab active" type="button" data-method="upload" onclick="switchAssessmentMethod('preAssessment', 'upload')">
                    <i class="fas fa-file-upload"></i> Upload File
                </button>
                <button class="input-method-tab" type="button" data-method="manual" onclick="switchAssessmentMethod('preAssessment', 'manual')">
                    <i class="fas fa-keyboard"></i> Manual Input
                </button>
            </div>

            <div class="input-method-content active" id="preAssessment-upload">
                <div id="preAssessmentUploadMcSection">
                    <div class="form-group">
                        <label id="preAssessmentUploadLabel">Upload Questionnaire (Excel):</label>
                        <div class="excel-format-hint" id="preAssessmentUploadFormatHint"></div>
                        <p class="upload-instruction" id="preAssessmentUploadInstruction"></p>
                        <div class="file-upload-area" onclick="document.getElementById('preAssessmentFile').click()">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <div class="upload-text">Click to upload Excel file</div>
                            <div class="upload-subtext">.xlsx / .xls (Max 20MB)</div>
                        </div>
                        <input type="file" id="preAssessmentFile" style="display: none;" accept=".xlsx,.xls">
                        <p id="preAssessmentFileName" style="color: #0066CC; font-weight: 600; margin-top: 10px;"></p>
                        <div id="preAssessmentUploadPreview" class="assessment-preview" style="display: none;"></div>
                        <p id="preAssessmentUploadError" class="upload-error" style="display: none; color: #c0392b;"></p>
                    </div>
                </div>
            </div>

            <div class="input-method-content" id="preAssessment-manual">
                <div class="form-group">
                    <label for="preAssessmentQuestionCount">Number of Questions:</label>
                    <input type="number" id="preAssessmentQuestionCount" value="5" min="1" max="50">
                </div>
                <button type="button" onclick="generatePreAssessmentQuestions()" style="width: 100%; padding: 12px; background: #0066CC; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-bottom: 15px;">
                    <i class="fas fa-magic"></i> Add Questions
                </button>
                <div class="questions-container" id="preAssessmentQuestionsContainer"></div>
            </div>

            <div class="randomization-options" id="preAssessmentRandomization">
                <div class="randomization-options-title">Randomization (for student view)</div>
                <label class="toggle-line">
                    <input type="checkbox" id="preAssessmentShuffleQuestions"> Shuffle / Randomize Question Order
                </label>
                <label class="toggle-line">
                    <input type="checkbox" id="preAssessmentShuffleChoices"> Shuffle / Randomize Choice Order
                </label>
            </div>

            <div class="divider-line"></div>
            <div class="form-group">
                <label>Timer (HH:MM:SS):</label>
                <div class="time-picker-group">
                    <input type="number" id="preAssessmentHours" placeholder="HH" min="0" max="23" value="0" style="max-width: 80px;">
                    <span class="time-unit">:</span>
                    <input type="number" id="preAssessmentMinutes" placeholder="MM" min="0" max="59" value="0" style="max-width: 80px;">
                    <span class="time-unit">:</span>
                    <input type="number" id="preAssessmentSeconds" placeholder="SS" min="0" max="59" value="0" style="max-width: 80px;">
                </div>
            </div>
            <div class="form-group">
                <label for="preAssessmentDueDate">Due Date:</label>
                <input type="date" id="preAssessmentDueDate">
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('preAssessmentModal')">Cancel</button>
            <button class="btn-save" onclick="savePreAssessment()">Save Assessment</button>
        </div>
    </div>
</div>
<!-- LEARNING MATERIALS MODAL -->
<div class="modal" id="learningMaterialsModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('learningMaterialsModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-book-open"></i>
            Learning Materials
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Upload Module:</label>
                <div class="file-upload-area" onclick="document.getElementById('learningMaterialFile').click()">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <div class="upload-text">Click to upload</div>
                    <div class="upload-subtext">PDF / PPT / Videos / DOCX (Max 50MB)</div>
                </div>
                <input type="file" id="learningMaterialFile" style="display: none;" accept=".pdf,.ppt,.pptx,.doc,.docx,.mp4,.mov,.avi,.webm">
                <p id="learningMaterialFileName" style="color: #0066CC; font-weight: 600; margin-top: 10px;"></p>
            </div>
            <div class="form-group">
                <label for="learningMaterialTitle">Module Title:</label>
                <input type="text" id="learningMaterialTitle" placeholder="Enter module title">
            </div>
            <div class="form-group">
                <label for="learningMaterialDescription">Description:</label>
                <textarea id="learningMaterialDescription" placeholder="Enter module description" rows="4"></textarea>
            </div>
            <button class="add-more-btn" type="button" onclick="addLearningMaterial()">
                <i class="fas fa-plus"></i> Add This Material
            </button>
            <div class="items-list" id="learningMaterialsList">
                <!-- dynamically populated by JavaScript -->
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('learningMaterialsModal')">Close</button>
        </div>
    </div>
</div>

<!-- POST-ASSESSMENT MODAL -->
<div class="modal" id="postAssessmentModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('postAssessmentModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-clipboard-check"></i>
            Post-Assessment Setup
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="postAssessmentStatus">Status:</label>
                <select id="postAssessmentStatus">
                    <option value="open">Open</option>
                    <option value="updating">Updating</option>
                </select>
                <small style="color:#888;">Closed automatically when due date passes.</small>
            </div>
            <div class="form-group">
                <label for="postAssessmentExamType">Exam Type:</label>
                <select id="postAssessmentExamType">
                    <option value="multipleChoice">Multiple Choice</option>
                    <option value="trueFalse">True or False</option>
                    <option value="matchingType">Matching Type</option>
                    <option value="mixed">Mixed (Combined)</option>
                </select>
            </div>

            <div class="input-method-tabs">
                <button class="input-method-tab active" type="button" data-method="upload" onclick="switchAssessmentMethod('postAssessment', 'upload')">
                    <i class="fas fa-file-upload"></i> Upload File
                </button>
                <button class="input-method-tab" type="button" data-method="manual" onclick="switchAssessmentMethod('postAssessment', 'manual')">
                    <i class="fas fa-keyboard"></i> Manual Input
                </button>
            </div>
            <div class="input-method-content active" id="postAssessment-upload">
                <div id="postAssessmentUploadMcSection">
                    <div class="form-group">
                        <label id="postAssessmentUploadLabel">Upload Questionnaire (Excel):</label>
                        <div class="excel-format-hint" id="postAssessmentUploadFormatHint"></div>
                        <p class="upload-instruction" id="postAssessmentUploadInstruction"></p>
                        <div class="file-upload-area" onclick="document.getElementById('postAssessmentFile').click()">
                            <i class="fas fa-cloud-arrow-up"></i>
                            <div class="upload-text">Click to upload Excel file</div>
                            <div class="upload-subtext">.xlsx / .xls (Max 20MB)</div>
                        </div>
                        <input type="file" id="postAssessmentFile" style="display: none;" accept=".xlsx,.xls">
                        <p id="postAssessmentFileName" style="color: #0066CC; font-weight: 600; margin-top: 10px;"></p>
                        <div id="postAssessmentUploadPreview" class="assessment-preview" style="display: none;"></div>
                        <p id="postAssessmentUploadError" class="upload-error" style="display: none; color: #c0392b;"></p>
                    </div>
                </div>
            </div>

            <div class="input-method-content" id="postAssessment-manual">
                <div class="form-group">
                    <label for="postAssessmentQuestionCount">Number of Questions:</label>
                    <input type="number" id="postAssessmentQuestionCount" value="5" min="1" max="50">
                </div>
                <button type="button" onclick="generatePostAssessmentQuestions()" style="width: 100%; padding: 12px; background: #0066CC; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-bottom: 15px;">
                    <i class="fas fa-magic"></i> Add Questions
                </button>
                <div class="questions-container" id="postAssessmentQuestionsContainer"></div>
            </div>

            <div class="randomization-options" id="postAssessmentRandomization">
                <div class="randomization-options-title">Randomization (for student view)</div>
                <label class="toggle-line">
                    <input type="checkbox" id="postAssessmentShuffleQuestions"> Shuffle / Randomize Question Order
                </label>
                <label class="toggle-line">
                    <input type="checkbox" id="postAssessmentShuffleChoices"> Shuffle / Randomize Choice Order
                </label>
            </div>

            <div class="divider-line"></div>
            <div class="form-group">
                <label>Timer (HH:MM:SS):</label>
                <div class="time-picker-group">
                    <input type="number" id="postAssessmentHours" placeholder="HH" min="0" max="23" value="0" style="max-width: 80px;">
                    <span class="time-unit">:</span>
                    <input type="number" id="postAssessmentMinutes" placeholder="MM" min="0" max="59" value="0" style="max-width: 80px;">
                    <span class="time-unit">:</span>
                    <input type="number" id="postAssessmentSeconds" placeholder="SS" min="0" max="59" value="0" style="max-width: 80px;">
                </div>
            </div>
            <div class="form-group">
                <label for="postAssessmentDueDate">Due Date:</label>
                <input type="date" id="postAssessmentDueDate">
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('postAssessmentModal')">Cancel</button>
            <button class="btn-save" onclick="savePostAssessment()">Save Assessment</button>
        </div>
    </div>
</div>

<!-- INTERVENTION MODAL -->
<div class="modal" id="interventionModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('interventionModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-graduation-cap"></i>
            Intervention Setup
        </div>
        <div class="modal-body">
            <!-- Level selector -->
            <div class="form-group">
                <label for="interventionLevel">Select Level:</label>
                <select id="interventionLevel" class="intervention-level-select" onchange="loadInterventionLevel()">
                    <option value="basic">🔹 Basic (Below Average)</option>
                    <option value="standard">🔹 Standard (Average)</option>
                    <option value="advanced">🔹 Advanced (Above Average)</option>
                </select>
            </div>

            <!-- Tabs -->
            <div class="intervention-top-buttons">
                <button class="intervention-btn-item active" type="button" onclick="switchContent('material')">
                    <i class="fas fa-book-reader"></i> + Add Material
                </button>
                <button class="intervention-btn-item" type="button" onclick="switchContent('video')">
                    <i class="fas fa-video"></i> + Add Video
                </button>
                <button class="intervention-btn-item" type="button" onclick="switchContent('quiz')">
                    <i class="fas fa-question-circle"></i> + Add Quiz
                </button>
            </div>

            <!-- Material Tab -->
            <div class="intervention-content-area active" id="materialContent">
                <div class="intervention-content-title">
                    <i class="fas fa-book-reader"></i> Upload Material
                </div>
                <div class="file-upload-area" onclick="document.getElementById('interventionMaterialFile').click()">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <div class="upload-text">Click to upload</div>
                    <div class="upload-subtext">PDF / PPT (Max 20MB)</div>
                </div>
                <input type="file" id="interventionMaterialFile" style="display:none;" accept=".pdf,.ppt,.pptx">
                <button class="add-more-btn" type="button" onclick="addInterventionMaterial()">
                    <i class="fas fa-plus"></i> Add This Material
                </button>
                <!-- Scrollable table -->
                <div class="items-list" style="max-height:250px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; margin-top:10px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead style="position:sticky; top:0; background:#f8f9fa; border-bottom:1px solid #ddd;">
                            <tr>
                                <th style="padding:8px; text-align:left;">#</th>
                                <th style="padding:8px; text-align:left;">File Name</th>
                                <th style="padding:8px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="materialsTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Video Tab -->
            <div class="intervention-content-area" id="videoContent">
                <div class="intervention-content-title">
                    <i class="fas fa-video"></i> Add Video
                </div>
                <div class="option-grid">
                    <button class="option-btn active" type="button" onclick="selectVideoType('link', this)">
                        <i class="fas fa-link"></i> Video Link
                    </button>
                    <button class="option-btn" type="button" onclick="selectVideoType('file', this)">
                        <i class="fas fa-file-video"></i> Upload File
                    </button>
                </div>
                <div id="videoLinkInput" class="form-group">
                    <label for="videoLink">Paste Video Link:</label>
                    <input type="url" id="videoLink" placeholder="https://youtube.com/...">
                </div>
                <div id="videoFileInput" class="form-group" style="display:none;">
                    <div class="file-upload-area" onclick="document.getElementById('interventionVideoFile').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <div class="upload-text">Click to upload</div>
                        <div class="upload-subtext">MP4 / MOV / AVI (Max 100MB)</div>
                    </div>
                    <input type="file" id="interventionVideoFile" style="display:none;" accept=".mp4,.mov,.avi,.webm">
                </div>
                <button class="add-more-btn" type="button" onclick="addInterventionVideo()">
                    <i class="fas fa-plus"></i> Add This Video
                </button>
                <!-- Scrollable table -->
                <div class="items-list" style="max-height:250px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; margin-top:10px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead style="position:sticky; top:0; background:#f8f9fa; border-bottom:1px solid #ddd;">
                            <tr>
                                <th style="padding:8px; text-align:left;">#</th>
                                <th style="padding:8px; text-align:left;">Video</th>
                                <th style="padding:8px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="videosTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Quiz Tab -->
            <div class="intervention-content-area" id="quizContent">
                <div class="intervention-content-title">
                    <i class="fas fa-question-circle"></i> Add Mini Quiz
                </div>
                <div id="quizSetupPhase">
                    <!-- ... existing quiz setup ... -->
                    <div class="form-group">
                        <label for="quizExamType">Exam Type:</label>
                        <select id="quizExamType">
                            <option value="multipleChoice">Multiple Choice</option>
                            <option value="trueFalse">True or False</option>
                            <option value="matchingType">Matching Type</option>
                            <option value="mixed">Mixed (Combined)</option>
                        </select>
                    </div>
                    <div class="input-method-tabs">
                        <button class="input-method-tab active" onclick="switchQuizMethod('upload')">Upload File</button>
                        <button class="input-method-tab" onclick="switchQuizMethod('manual')">Manual Input</button>
                    </div>
                    <div class="input-method-content active" id="quiz-upload">
                        <div class="form-group">
                            <label id="quizUploadLabel">Upload Mini Quiz (Excel):</label>
                            <div class="excel-format-hint" id="quizUploadFormatHint"></div>
                            <p class="upload-instruction" id="quizUploadInstruction"></p>
                            <div class="file-upload-area" onclick="document.getElementById('quizFile').click()">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <div class="upload-text">Click to upload Excel file</div>
                                <div class="upload-subtext">.xlsx / .xls (Max 20MB)</div>
                            </div>
                            <input type="file" id="quizFile" style="display:none;" accept=".xlsx,.xls">
                            <p id="quizFileName" style="color:#0066CC; font-weight:600; margin-top:10px;"></p>
                            <div id="quizUploadPreview" class="assessment-preview" style="display:none;"></div>
                            <p id="quizUploadError" class="upload-error" style="display:none; color:#c0392b;"></p>
                        </div>
                    </div>
                    <div class="input-method-content" id="quiz-manual">
                        <div class="form-group" id="quizNumQuestionsGroup">
                            <label for="quizNumQuestions">Number of Questions:</label>
                            <input type="number" id="quizNumQuestions" value="3" min="1" max="50">
                        </div>
                        <div class="form-group" id="quizNumChoicesGroup">
                            <label for="quizNumChoices">Number of Choices per Question:</label>
                            <input type="number" id="quizNumChoices" value="4" min="2" max="6">
                        </div>
                        <button type="button" onclick="generateQuizQuestions()" style="width:100%; padding:12px; background:#0066CC; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; margin-bottom:15px;">
                            <i class="fas fa-magic"></i> Add Questions
                        </button>
                    </div>
                    <div class="randomization-options" id="quizRandomization">
                        <div class="randomization-options-title">Randomization (for student view)</div>
                        <label class="toggle-line">
                            <input type="checkbox" id="quizShuffleQuestions"> Shuffle / Randomize Question Order
                        </label>
                        <label class="toggle-line" id="quizShuffleChoicesRow">
                            <input type="checkbox" id="quizShuffleChoices"> Shuffle / Randomize Choice Order
                        </label>
                    </div>
                </div>
                <div id="quizQuestionsPhase" style="display:none;">
                    <div class="questions-container" id="interventionQuestionsContainer"></div>
                </div>
                <div class="form-group">
                    <label>Quiz Timer (HH:MM:SS):</label>
                    <div class="time-picker-group">
                        <input type="number" id="quizHours" value="0" min="0" max="23" style="max-width:80px;">
                        <span class="time-unit">:</span>
                        <input type="number" id="quizMinutes" value="0" min="0" max="59" style="max-width:80px;">
                        <span class="time-unit">:</span>
                        <input type="number" id="quizSeconds" value="0" min="0" max="59" style="max-width:80px;">
                    </div>
                </div>
                <button class="add-more-btn" type="button" onclick="addInterventionQuiz()">
                    <i class="fas fa-plus"></i> Add This Quiz
                </button>
                <!-- Scrollable table for quizzes -->
                <div class="items-list" style="max-height:250px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; margin-top:10px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead style="position:sticky; top:0; background:#f8f9fa; border-bottom:1px solid #ddd;">
                            <tr>
                                <th style="padding:8px; text-align:left;">#</th>
                                <th style="padding:8px; text-align:left;">Quiz Details</th>
                                <th style="padding:8px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="quizzesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('interventionModal')">Cancel</button>
            <button class="btn-save" onclick="saveIntervention()">Save Intervention</button>
        </div>
    </div>
</div>

<!-- EDIT MATERIAL MODAL -->
<div class="modal" id="editMaterialModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('editMaterialModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-file-pdf"></i> Edit Material
        </div>
        <div class="modal-body">
            <input type="hidden" id="editMaterialIndex">
            <input type="hidden" id="editMaterialLevel">
            <div class="form-group">
                <label>File Name:</label>
                <input type="text" id="editMaterialFileName" class="form-control" placeholder="Enter file name">
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('editMaterialModal')">Cancel</button>
            <button class="btn-save" onclick="saveEditedMaterial()">Update Material</button>
        </div>
    </div>
</div>

<!-- EDIT VIDEO MODAL -->
<div class="modal" id="editVideoModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('editVideoModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-video"></i> Edit Video
        </div>
        <div class="modal-body">
            <input type="hidden" id="editVideoIndex">
            <input type="hidden" id="editVideoLevel">
            <div class="form-group">
                <label for="editVideoType">Video Type:</label>
                <select id="editVideoType">
                    <option value="link">Link</option>
                    <option value="file">Uploaded File</option>
                </select>
            </div>
            <div class="form-group" id="editVideoLinkGroup">
                <label>Video URL:</label>
                <input type="url" id="editVideoUrl" class="form-control" placeholder="https://...">
            </div>
            <div class="form-group" id="editVideoFileGroup" style="display:none;">
                <label>File Name:</label>
                <input type="text" id="editVideoFileName" class="form-control" placeholder="video.mp4">
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('editVideoModal')">Cancel</button>
            <button class="btn-save" onclick="saveEditedVideo()">Update Video</button>
        </div>
    </div>
</div>

<!-- EDIT QUIZ MODAL -->
<div class="modal" id="editQuizModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('editQuizModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-edit"></i> Edit Quiz
        </div>
        <div class="modal-body">
            <input type="hidden" id="editQuizIndex">
            <input type="hidden" id="editQuizLevel">
            <div class="form-group">
                <label for="editQuizExamType">Exam Type:</label>
                <select id="editQuizExamType">
                    <option value="multipleChoice">Multiple Choice</option>
                    <option value="trueFalse">True or False</option>
                    <option value="matchingType">Matching Type</option>
                    <option value="mixed">Mixed (Combined)</option>
                </select>
            </div>
            <div class="randomization-options">
                <label class="toggle-line">
                    <input type="checkbox" id="editQuizShuffleQuestions"> Shuffle Questions
                </label>
                <label class="toggle-line">
                    <input type="checkbox" id="editQuizShuffleChoices"> Shuffle Choices
                </label>
            </div>
            <div class="form-group">
                <label>Timer (HH:MM:SS):</label>
                <div class="time-picker-group">
                    <input type="number" id="editQuizHours" value="0" min="0" max="23" style="max-width:80px;">
                    <span class="time-unit">:</span>
                    <input type="number" id="editQuizMinutes" value="0" min="0" max="59" style="max-width:80px;">
                    <span class="time-unit">:</span>
                    <input type="number" id="editQuizSeconds" value="0" min="0" max="59" style="max-width:80px;">
                </div>
            </div>
            <div class="form-group">
                <label>Questions</label>
                <div class="questions-container" id="editQuizQuestionsContainer" style="max-height:300px; overflow-y:auto;"></div>
            </div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('editQuizModal')">Cancel</button>
            <button class="btn-save" onclick="saveEditedQuiz()">Update Quiz</button>
        </div>
    </div>
</div>

<!-- CONFIGURE RELEASE MODAL -->
<div class="modal" id="learningPackageModal" role="dialog" aria-modal="true">
    <div class="modal-content modal-wide">
        <button class="modal-close" type="button" onclick="closeModal('learningPackageModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header" id="learningPackageModalTitle">
            <i class="fas fa-calendar-check"></i>
            Configure Release
        </div>
        <div class="modal-body">
            <p class="package-modal-lead">Grade, quarter, subject, and week come from the folder you uploaded into. Set classes, schedule, and visibility below—uploads stay separate from publishing.</p>
            <div class="package-settings-section-title">Learning Package Settings</div>
            <div class="form-group">
                <label>Assigned Class</label>
                <p class="assigned-class-scope-hint" id="lpAssignedClassesHint"></p>
                <div class="checkbox-grid" id="lpAssignedClasses"></div>
            </div>
            <div class="form-group">
                <label for="lpReleaseDate">Release Date</label>
                <input type="date" id="lpReleaseDate">
            </div>
            <div class="form-group">
                <label for="lpDueDate">Due Date</label>
                <input type="date" id="lpDueDate">
            </div>
            <div class="form-group">
                <label for="lpStatus">Status</label>
                <select id="lpStatus">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="package-logic-hint">
                <strong>Student visibility:</strong> <strong>Draft</strong> — hidden from students. <strong>Published</strong> — becomes available to assigned classes on or after the release date. <strong>Archived</strong> — removed from active student view; kept in records.
            </div>
        </div>
        <div class="modal-buttons modal-buttons-learning-package">
            <button class="btn-save" type="button" onclick="saveLearningPackageDraft()">Save Draft</button>
            <button class="btn-publish" type="button" onclick="publishLearningPackage()">Publish</button>
            <button class="btn-cancel" type="button" onclick="closeModal('learningPackageModal')">Cancel</button>
        </div>
    </div>
</div>

<!-- CONTENT DETAILS MODAL -->
<div class="modal" id="contentDetailsModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('contentDetailsModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-circle-info"></i>
            Content Details
        </div>
        <div class="modal-body">
            <div class="details-grid" id="contentDetailsBody"></div>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('contentDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- EDIT CONTENT MODAL -->
<div class="modal" id="editContentModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('editContentModal')">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-header">
            <i class="fas fa-pen"></i>
            Edit Content
        </div>
        <div class="modal-body">
            <form id="editContentForm">
                <div class="form-group">
                    <label for="editContentTitle">Title:</label>
                    <input type="text" id="editContentTitle" required>
                </div>
                <div class="form-group" id="editFileGroup">
                    <label for="editContentFileName">Uploaded File:</label>
                    <input type="text" id="editContentFileName" placeholder="Current file name">
                    <input type="file" id="editContentFileUpload" style="margin-top: 8px;">
                </div>
                <div class="form-group" id="editVideoGroup">
                    <label for="editContentVideoLink">Video Link:</label>
                    <input type="url" id="editContentVideoLink" placeholder="https://...">
                </div>
                <div class="form-group" id="editTextGroup">
                    <label for="editContentText" id="editContentTextLabel">Quiz Content:</label>
                    <textarea id="editContentText" rows="5" placeholder="Quiz questions/notes"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal('editContentModal')">Cancel</button>
            <button class="btn-save" onclick="saveEditedContent()">Save Changes</button>
        </div>
    </div>
</div>