<div class="top-bar">
    <div class="top-bar-left">
        <button class="hamburger-btn" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
        <div class="top-bar-brand">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="logo-text">
                <div><span class="edu">Edu</span><span class="adapt">Adapt</span></div>
            </div>
        </div>
        <div class="top-bar-title">@yield('page_title', 'Dashboard')</div>
    </div>
    <div class="top-bar-right">
        <div class="notification-container">
            <button class="notification-btn" id="notificationBtn"><i class="fas fa-bell"></i>
                <div class="notification-badge" id="notificationBadge">{{ $notificationsCount ?? 0 }}</div>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">Notifications</div>
                @forelse($notifications ?? [] as $note)
                <div class="notification-item">
                    <div class="notification-item-title">{{ $note['title'] }}</div>
                    <div class="notification-item-text">{{ $note['text'] }}</div>
                </div>
                @empty
                <div class="notification-item">No notifications</div>
                @endforelse
            </div>
        </div>
        <div class="user-info">
            <div class="user-avatar" id="userAvatar">
                @php
                $teacher = auth()->user()->teacherProfile;
                $picture = $teacher ? $teacher->profile_picture : null;
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
                <div class="user-name" id="userName">{{ auth()->user()->full_name ?? 'User' }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role ?? '') }}</div>
            </div>
        </div>
    </div>
</div>