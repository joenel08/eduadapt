@extends('layouts.app')

@section('page_title', 'My Profile')
@section('page', 'profile')

@section('content')
<div class="content-page">

    <h1 class="page-title"><i class="fas fa-user-shield"></i> My Profile</h1>
    <p class="page-subtitle">Manage your admin account details</p>

    @if (session('success'))
        <div class="message-box" style="background:#eef2ff;color:#1d4ed8;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="message-box" style="background:#ffe4e6;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Avatar --}}
    <div class="profile-card">
        <div class="avatar-wrap">
            @if ($profile->profile_picture ?? false)
                <img src="{{ asset('storage/' . $profile->profile_picture) }}" alt="Avatar" class="avatar">
            @else
                <div class="avatar avatar-placeholder">
                    {{ strtoupper(substr($profile->full_name ?? $user->login_id, 0, 1)) }}
                </div>
            @endif
        </div>

        <form action="{{ route('admin.profile.picture') }}" method="POST" enctype="multipart/form-data" class="picture-form">
            @csrf
            <label for="profile_picture" class="btn btn-secondary btn-sm">
                <i class="fas fa-camera"></i> Change Picture
            </label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                   style="display:none" onchange="this.form.submit()">
        </form>
    </div>

    {{-- Profile Information --}}
    <div class="profile-card">
        <h3 class="section-title">Profile Information</h3>
        <form action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control"
                       value="{{ old('full_name', $profile->full_name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="{{ old('email', $profile->email ?? '') }}">
            </div>

            <div class="form-group">
                <label for="login_id">Username (Login ID)</label>
                <input type="text" name="login_id" id="login_id" class="form-control"
                       value="{{ old('login_id', $user->login_id) }}" required>
                <small style="color:#888;font-size:12px;">This is what you use to log in.</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

    {{-- Password --}}
    <div class="profile-card">
        <h3 class="section-title">Change Password</h3>
        <form action="{{ route('admin.profile.password') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password"
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password"
                       class="form-control" required minlength="8">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control" required minlength="8">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-key"></i> Update Password
            </button>
        </form>
    </div>
</div>

<style>
.content-page { max-width: 720px; margin: 0 auto; padding: 20px; }
.page-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
.page-title i { color: #0066CC; }
.page-subtitle { color: #888; font-size: 14px; margin-bottom: 24px; }

.profile-card { background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }

.avatar-wrap { display:flex; justify-content:center; margin-bottom:16px; }
.avatar { width:110px; height:110px; border-radius:50%; object-fit:cover; border:4px solid #0066CC; }
.avatar-placeholder { display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#0066CC,#004D99); color:#fff; font-size:44px; font-weight:700; }
.picture-form { text-align:center; }

.section-title { font-size:16px; font-weight:700; margin-bottom:16px; color:#333; }

.form-group { margin-bottom:16px; }
.form-group label { display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:#444; }
.form-control { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px; }
.form-control:focus { border-color:#0066CC; outline:none; box-shadow:0 0 0 3px rgba(0,102,204,0.1); }

.btn { padding:10px 20px; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s; }
.btn-primary { background:linear-gradient(135deg,#0066CC,#004D99); color:#fff; }
.btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(0,102,204,0.3); }
.btn-secondary { background:#f0f0f0; color:#333; border:1px solid #ddd; }
.btn-secondary:hover { background:#e4e4e4; }
.btn-sm { padding:8px 16px; font-size:13px; }
</style>
@endsection