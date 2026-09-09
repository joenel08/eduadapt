<div class="top-bar">
    <div class="top-bar-left">
        <button class="hamburger-btn" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
        <div class="top-bar-brand">
            <div class="logo-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="logo-text"><div class="brand-title"><span class="edu">Edu</span><span class="adapt">Adapt</span></div></div>
        </div>
        <span class="top-bar-title">Student Panel</span>
    </div>
    <div class="top-bar-right">
        <div class="notification-container">
            <button class="notification-btn" id="notificationBtn"><i class="fas fa-bell"></i></button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">Notifications</div>
                <div class="notification-item">No new notifications.</div>
            </div>
        </div>
        <div class="user-info">
            <div class="user-avatar" id="userAvatar">
                @php
                $student = auth()->user()->studentProfile;
                $picture = $student ? $student->profile_picture : null;
                $hasPicture = $picture && Storage::disk('public')->exists($picture);
                @endphp
                @if($hasPicture)
                <img src="{{ asset('storage/' . $picture) }}"
                    alt="Avatar"
                    style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                @else
                {{ $initials ?? 'T' }}
                @endif
            </div>
          
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->full_name }}</div>
                <div class="user-role">Student</div>
            </div>
        </div>
    </div>
</div>