<!-- ===== SIDEBAR ===== -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="sidebar-header-text">
            <div><span class="edu">Edu</span><span class="adapt">Adapt</span></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('teacher.classes') }}" class="{{ request()->routeIs('teacher.classes*') ? 'active' : '' }}">
            <i class="fas fa-book"></i><span>Classes</span>
        </a>
        <a href="{{ route('teacher.content-library') }}" class="{{ request()->routeIs('teacher.content-library*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i><span>Content Library</span>
        </a>
        <a href="{{ route('teacher.global-analytics') }}" class="{{ request()->routeIs('teacher.global-analytics') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i><span>Analytics</span>
        </a>
        <a href="{{ route('teacher.profile') }}" class="{{ request()->routeIs('teacher.profile') ? 'active' : '' }}">
            <i class="fas fa-cog"></i><span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="#" onclick="event.preventDefault(); showLogoutModal();">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</div>

<!-- ===== LOGOUT CONFIRMATION MODAL ===== -->
<div class="logout-modal-overlay" id="logoutModalOverlay">
    <div class="logout-modal">
        <div class="logout-modal-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="logout-modal-title">Confirm Logout</div>
        <div class="logout-modal-text">Are you sure you want to log out of your account?</div>
        <div class="logout-modal-buttons">
            <button class="logout-modal-btn cancel" onclick="hideLogoutModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="logout-modal-btn confirm" onclick="confirmLogout()">
                <i class="fas fa-check"></i> Logout
            </button>
        </div>
    </div>
</div>