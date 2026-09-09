<div class="top-bar">
    <div class="top-bar-left">
        <button class="hamburger-btn" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
        <div class="top-bar-brand">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="logo-text"><div class="brand-title"><span class="edu">Edu</span><span class="adapt">Adapt</span></div></div>
        </div>
    </div>
    <div class="top-bar-right">
        <div class="notification-container">
            <button class="notification-btn" id="notificationBtn"><i class="fas fa-bell"></i><span class="notification-badge" id="notificationBadge">{{ $pendingCount ?? 0 }}</span></button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">Notifications</div>
                @foreach($notifications ?? [] as $note)
                    <div class="notification-item">
                        <div class="notification-item-title">{{ $note['title'] }}</div>
                        <div class="notification-item-text">{{ $note['text'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="user-info">
            <div class="user-avatar text-uppercase">{{ substr(auth()->user()->full_name, 0, 2) }}</div>
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->full_name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
    </div>
</div>