<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="sidebar-header-text">
            <div class="brand-title"><span class="edu">Edu</span><span class="adapt">Adapt</span></div>
            <div style="font-size:12px;color:var(--muted);">Student</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('student.dashboard') }}" data-page="dashboard" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('student.classes') }}" class="{{ request()->routeIs('student.classes*') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-user"></i>
            <span>My Classes</span>
        </a>
        <a href="{{ route('student.progress') }}" class="{{ request()->routeIs('student.progress*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Progress</span>
        </a>
        <a href="{{ route('student.profile') }}" data-page="profile" class="{{ request()->routeIs('student.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i><span>Profile</span>
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
</aside>

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