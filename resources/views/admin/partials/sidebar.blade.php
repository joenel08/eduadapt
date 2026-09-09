<style>
    /* ===== Logout Confirmation Modal ===== */
.logout-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 99999;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.2s ease;
}

.logout-modal-overlay.active {
    display: flex;
}

.logout-modal {
    background: white;
    border-radius: 16px;
    padding: 40px 35px 30px;
    max-width: 420px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
    text-align: center;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px) scale(0.95); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.logout-modal-icon {
    font-size: 48px;
    color: #FF6B6B;
    background: rgba(255, 107, 107, 0.12);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
}

.logout-modal-title {
    font-size: 22px;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}

.logout-modal-text {
    font-size: 15px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 28px;
}

.logout-modal-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.logout-modal-btn {
    padding: 12px 28px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.logout-modal-btn.cancel {
    background: #f0f0f0;
    color: #666;
}

.logout-modal-btn.cancel:hover {
    background: #e0e0e0;
    transform: translateY(-2px);
}

.logout-modal-btn.confirm {
    background: #FF6B6B;
    color: white;
}

.logout-modal-btn.confirm:hover {
    background: #E55A5A;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
}

@media (max-width: 480px) {
    .logout-modal {
        padding: 30px 20px 25px;
    }
    .logout-modal-icon {
        width: 60px;
        height: 60px;
        font-size: 36px;
    }
    .logout-modal-title {
        font-size: 18px;
    }
    .logout-modal-buttons {
        flex-direction: column;
    }
    .logout-modal-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="sidebar-header-text">
            <div class="brand-title"><span class="edu">Edu</span><span class="adapt">Adapt</span></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" data-page="dashboard" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('admin.school-years') }}" data-page="schoolyears" class="{{ request()->routeIs('admin.school-years') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i><span>School Years</span>
        </a>
        <a href="{{ route('admin.subjects') }}" data-page="subjects" class="{{ request()->routeIs('admin.subjects') ? 'active' : '' }}">
            <i class="fas fa-book"></i><span>Subjects</span>
        </a>
        <a href="{{ route('admin.master-data') }}" data-page="masterdata" class="{{ request()->routeIs('admin.master-data') ? 'active' : '' }}">
            <i class="fas fa-database"></i><span>Master Data Upload</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="#" onclick="event.preventDefault(); showLogoutModal();">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
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