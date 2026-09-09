{{-- Configuration Modal --}}

<div class="modal" id="contentConfigModal">
    <div class="modal-content modal-wide">
        <button class="modal-close" onclick="closeModal('contentConfigModal')">
            <i class="fas fa-times"></i>
        </button>

        <div class="modal-header" id="learningPackageModalTitle">
            <i class="fas fa-calendar-check"></i>
            Configure Release
        </div>
        <div class="modal-body">
            <p class="package-modal-lead">Grade, quarter, subject, and week come from the folder you uploaded into. Set classes, schedule, and visibility below—uploads stay separate from publishing.</p>
            <div class="package-settings-section-title">Learning Package Settings</div>
            <p class="text-muted">Assign this content to classes and set release/due dates.</p>
            <div id="configContentInfo"></div>
            <div id="configClassList"></div>
        </div>
        <div class="modal-buttons modal-buttons-learning-package">
            <button class="btn-cancel" onclick="closeModal('contentConfigModal')">Cancel</button>
            <button class="btn btn-publish " onclick="saveContentConfig()">Publish</button>
        </div>
    </div>
</div>