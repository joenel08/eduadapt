@extends('layouts.teacher-student')

@section('page_title', 'Profile Settings')
@section('page', 'settings')

@push('styles')
<style>
    /* ============ TAB NAVIGATION ============ */
    .tabs-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .tabs-header {
        display: flex;
        border-bottom: 2px solid #e0e0e0;
    }

    .tab-button {
        flex: 1;
        padding: 18px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        color: #999;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-bottom: 3px solid transparent;
        position: relative;
        bottom: -2px;
    }

    .tab-button:hover {
        color: #0066CC;
        background-color: #f5f5f5;
    }

    .tab-button.active {
        color: #0066CC;
        border-bottom-color: #0066CC;
    }

    .tab-button i {
        font-size: 18px;
    }

    /* ============ TAB CONTENT ============ */
    .tabs-content {
        padding: 30px;
        background: white;
        min-height: 500px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* ============ FORM GROUPS ============ */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .form-group input,
    .form-group textarea {
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #0066CC;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .form-group input:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
        color: #999;
    }

    /* ============ PROFILE PICTURE SECTION ============ */
    .profile-picture-section {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #f0f0f0;
    }

    .profile-picture-display {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0066CC 0%, #004D99 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 48px;
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
    }

    .profile-picture-upload {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: -9999px;
    }

    .file-input-label {
        display: inline-block;
        padding: 10px 20px;
        background: #0066CC;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .file-input-label:hover {
        background: #004D99;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
    }

    .file-name-display {
        font-size: 12px;
        color: #999;
        min-height: 20px;
    }

    /* ============ PASSWORD INPUT GROUP ============ */
    .password-input-group {
        position: relative;
    }

    .password-input-group input {
        width: 100%;
        padding: 12px 40px 12px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .password-input-group input:focus {
        outline: none;
        border-color: #0066CC;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #0066CC;
        font-size: 18px;
        background: none;
        border: none;
        padding: 0;
    }

    .toggle-password:hover {
        color: #004D99;
    }

    /* ============ VALIDATION MESSAGES ============ */
    .validation-message {
        font-size: 12px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .validation-message.error {
        color: #FF6B6B;
    }

    .validation-message.success {
        color: #51CF66;
    }

    /* ============ PASSWORD REQUIREMENTS ============ */
    .password-requirements {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 6px;
        margin-top: 15px;
    }

    .requirements-title {
        font-weight: 600;
        color: #333;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .requirement-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #666;
        margin-bottom: 6px;
    }

    .requirement-item.met {
        color: #51CF66;
    }

    .requirement-item.unmet {
        color: #FF6B6B;
    }
</style>
@endpush

@section('content')

<!-- Page Header -->
<!-- <div class="page-header">
    <h1><i class="fas fa-cog"></i> Settings</h1>
    <p>Manage your profile and security settings</p>
</div> -->

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1 style="font-size:28px;font-weight:700;">Settings</h1>
        <p style="color:#999;">Manage your profile and security settings</p>
    </div>
   
</div>

<!-- Tabs -->
<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-button active" onclick="switchTab(event, 'profile')">
            <i class="fas fa-user-edit"></i> Edit Profile
        </button>
        <button class="tab-button" onclick="switchTab(event, 'password')">
            <i class="fas fa-lock"></i> Change Password
        </button>
    </div>

    <!-- ===== TAB 1: EDIT PROFILE ===== -->
    <div id="profile" class="tab-pane active">
        <div class="tabs-content">
            <div id="profileAlert" class="alert"></div>

            <div class="profile-picture-section">
                <div class="profile-picture" id="profilePictureDisplay"
                    style="@if($teacher->profile_picture) 
                background-image: url('{{ asset('storage/' . $teacher->profile_picture) }}'); 
                background-size: cover; 
                background-position: center; 
                @else 
                background: linear-gradient(135deg, #0066CC 0%, #004D99 100%); 
                @endif">
                    @if(!$teacher->profile_picture)
                    {{ strtoupper(substr($user->name ?? 'T', 0, 2)) }}
                    @endif
                </div>
                <div class="profile-picture-upload">
                    <form id="pictureForm" action="{{ route('teacher.profile.picture') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="file-input-wrapper">
                            <input type="file" name="profile_picture" id="profilePictureInput" accept="image/*">
                            <label for="profilePictureInput" class="file-input-label">
                                <i class="fas fa-upload"></i> Upload Photo
                            </label>
                        </div>
                        <div class="file-name-display" id="fileNameDisplay"></div>
                        <button type="submit" class="btn btn-publish" style="margin-top: 10px;">
                            <i class="fas fa-save"></i> Save Photo
                        </button>
                    </form>
                </div>
            </div>

            <!-- <form id="profileForm" action="{{ route('teacher.profile.update') }}" method="POST">
                @csrf
                @method('PUT') -->

                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" readonly value="{{ old('name', $fullName) }}" placeholder="Full name">
                </div>

                <div class="form-group">
                    <label for="employeeId">Employee ID</label>
                    <input type="text" id="employeeId" readonly name="employee_id" value="{{ old('employee_id', $teacher->employee_id ?? '') }}" placeholder="Employee ID">
                    @error('employee_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="Email" readonly style="background:#f5f5f5;">
                </div> -->

                <!-- <div class="button-group">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="reset" class="btn btn-cancel">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div> -->
            <!-- </form> -->
        </div>
    </div>

    <!-- ===== TAB 2: CHANGE PASSWORD ===== -->
    <div id="password" class="tab-pane">
        <div class="tabs-content">
            <div id="passwordAlert" class="alert"></div>

            <form id="passwordForm" action="{{ route('teacher.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <div class="password-input-group">
                        <input type="password" id="currentPassword" name="current_password" placeholder="Enter current password">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('currentPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="password-input-group">
                        <input type="password" id="newPassword" name="new_password" placeholder="Enter new password">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('newPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="validation-message" id="newPasswordValidation"></div>
                    @error('new_password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Re-type New Password</label>
                    <div class="password-input-group">
                        <input type="password" id="confirmPassword" name="new_password_confirmation" placeholder="Re-type new password">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="validation-message" id="confirmPasswordValidation"></div>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <div class="requirements-title">
                        <i class="fas fa-info-circle"></i> Password Requirements:
                    </div>
                    <div class="requirement-item" id="req-length">
                        <i class="fas fa-circle"></i> At least 6 characters
                    </div>
                    <div class="requirement-item" id="req-letters">
                        <i class="fas fa-circle"></i> Letters (A–Z, a–z)
                    </div>
                    <div class="requirement-item" id="req-numbers">
                        <i class="fas fa-circle"></i> Numbers (0–9)
                    </div>
                    <div class="requirement-item" id="req-special">
                        <i class="fas fa-circle"></i> Special characters (!@#$%^&*)
                    </div>
                    <div class="requirement-item" id="req-match">
                        <i class="fas fa-circle"></i> Passwords match
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button type="reset" class="btn btn-cancel">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ============ TAB SWITCHING ============
    function switchTab(e, tabName) {
        e.preventDefault();

        document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

        document.getElementById(tabName).classList.add('active');
        e.currentTarget.classList.add('active');
    }

    // ============ SIDEBAR / NOTIFICATIONS (inherited from layout) ============
    // (Already present in layout, no need to redeclare)

    // ============ PROFILE PICTURE UPLOAD ============
    document.getElementById('profilePictureInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('fileNameDisplay').textContent = file.name;
            const reader = new FileReader();
            reader.onload = function(event) {
                const display = document.getElementById('profilePictureDisplay');
                display.style.backgroundImage = `url(${event.target.result})`;
                display.style.backgroundSize = 'cover';
                display.style.backgroundPosition = 'center';
                display.textContent = '';
            };
            reader.readAsDataURL(file);
        }
    });

    // ============ PASSWORD VISIBILITY TOGGLE ============
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // ============ PASSWORD VALIDATION (real‑time) ============
    const newPass = document.getElementById('newPassword');
    const confirmPass = document.getElementById('confirmPassword');

    function validatePassword(password) {
        return {
            length: password.length >= 6,
            letters: /[a-zA-Z]/.test(password),
            numbers: /[0-9]/.test(password),
            special: /[!@#$%^&*]/.test(password)
        };
    }

    function updateRequirement(id, met) {
        const el = document.getElementById(id);
        if (met) {
            el.classList.remove('unmet');
            el.classList.add('met');
            el.querySelector('i').className = 'fas fa-check-circle';
        } else {
            el.classList.remove('met');
            el.classList.add('unmet');
            el.querySelector('i').className = 'fas fa-circle';
        }
    }

    newPass?.addEventListener('input', function() {
        const v = validatePassword(this.value);
        updateRequirement('req-length', v.length);
        updateRequirement('req-letters', v.letters);
        updateRequirement('req-numbers', v.numbers);
        updateRequirement('req-special', v.special);
        // Check match
        const match = this.value === confirmPass.value && this.value !== '';
        updateRequirement('req-match', match);

        const msg = document.getElementById('newPasswordValidation');
        const allValid = v.length && v.letters && v.numbers && v.special;
        if (this.value === '') {
            msg.textContent = '';
        } else if (!allValid) {
            msg.className = 'validation-message error';
            msg.innerHTML = '<i class="fas fa-times-circle"></i> Password does not meet requirements';
        } else {
            msg.className = 'validation-message success';
            msg.innerHTML = '<i class="fas fa-check-circle"></i> Password meets all requirements';
        }
    });

    confirmPass?.addEventListener('input', function() {
        const match = this.value === newPass.value && newPass.value !== '';
        updateRequirement('req-match', match);
        const msg = document.getElementById('confirmPasswordValidation');
        if (this.value === '') {
            msg.textContent = '';
        } else if (!match) {
            msg.className = 'validation-message error';
            msg.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
        } else {
            msg.className = 'validation-message success';
            msg.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
        }
    });

    // ============ FORM SUBMISSION – SHOW ALERTS ============
    @if(session('success'))
        (function() {
            const alert = document.getElementById('profileAlert') || document.getElementById('passwordAlert');
            if (alert) {
                alert.className = 'alert success show';
                alert.innerHTML = '<i class="fas fa-check-circle"></i> {{ session('
                success ') }}';
                setTimeout(() => alert.classList.remove('show'), 4000);
            }
        })();
    @endif

    @if($errors -> any())
        (function() {
            const alert = document.getElementById('profileAlert') || document.getElementById('passwordAlert');
            if (alert) {
                const errors = @json($errors -> all());
                alert.className = 'alert error show';
                alert.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errors.join('<br>');
                setTimeout(() => alert.classList.remove('show'), 6000);
            }
        })();
    @endif
</script>
@endpush