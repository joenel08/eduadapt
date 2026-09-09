// ============ USER INFORMATION ============
// function initializeUserInfo() {
//     // For Laravel, we inject user data from Blade; but we can also use the DOM elements already populated by Blade.
//     // If you want to use localStorage, you can, but we'll rely on server-side rendering.
//     // We'll just set the avatar from the full name if needed.
//     const userNameEl = document.getElementById('userName');
//     const userAvatarEl = document.getElementById('userAvatar');
//     if (userNameEl && userAvatarEl) {
//         const name = userNameEl.textContent.trim();
//         const initials = name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().substring(0, 2);
//         userAvatarEl.textContent = initials || 'U';
//     }
// }

// ============ NOTIFICATION DROPDOWN ============
const notificationBtn = document.getElementById('notificationBtn');
const notificationDropdown = document.getElementById('notificationDropdown');

if (notificationBtn && notificationDropdown) {
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-container')) {
            notificationDropdown.classList.remove('active');
        }
    });
}

// ============ HAMBURGER MENU TOGGLE ============
const hamburgerBtn = document.getElementById('hamburgerBtn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const mainContent = document.getElementById('mainContent');

if (hamburgerBtn && sidebar && sidebarOverlay) {
    hamburgerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('active');
    });

    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
    });

    // Close sidebar on link click
    document.querySelectorAll('.sidebar-nav a, .sidebar-footer a').forEach(link => {
        link.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });
    });

    // Close sidebar on main content click (mobile)
    if (mainContent) {
        mainContent.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                if (sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                }
            }
        });
    }

    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                if (sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                }
            }
        }
    });
}
// ===== Logout Modal =====
function showLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.add('active');
}

function hideLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.remove('active');
}

function confirmLogout() {
    hideLogoutModal();
    // Submit the logout form
    document.getElementById('logout-form').submit();
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('logoutModalOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                hideLogoutModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideLogoutModal();
        }
    });
});

// Initialize user info on page load
// document.addEventListener('DOMContentLoaded', function() {
//     initializeUserInfo();
// });