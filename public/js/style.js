function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const hamburgerBtn = document.getElementById('hamburgerBtn');

    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function () {
            sidebar?.classList.toggle('open');
            overlay?.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar?.classList.remove('open');
            overlay?.classList.remove('active');
        });
    }
}


function initNotificationDropdown() {
    const notificationBtn = document.getElementById('notificationBtn');
    const dropdown = document.getElementById('notificationDropdown');

    if (notificationBtn) {
        notificationBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdown?.classList.toggle('active');
        });
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.notification-container')) {
            dropdown?.classList.remove('active');
        }
    });
}


function initModal() {
    const backdrop = document.getElementById('popupBackdrop');
    const closeBtn = document.getElementById('popupClose');

    if (!backdrop || !closeBtn) return;

    closeBtn.addEventListener('click', closeModal);

    backdrop.addEventListener('click', function (event) {
        if (event.target === backdrop) {
            closeModal();
        }
    });
}


function openModal(title, bodyHtml) {
    const backdrop = document.getElementById('popupBackdrop');
    const titleEl = document.getElementById('popupTitle');
    const body = document.getElementById('popupBody');

    if (!backdrop || !titleEl || !body) return;

    titleEl.textContent = title;
    body.innerHTML = bodyHtml;

    backdrop.classList.add('active');
}


function closeModal() {
    document.getElementById('popupBackdrop')?.classList.remove('active');
}


function setActiveNav(page) {
    document.querySelectorAll('.sidebar-nav a').forEach(link => {

        const isActive =
            link.dataset.page === page ||
            (
                page === 'sectiondetails' &&
                link.dataset.page === 'masterdata'
            );

        link.classList.toggle('active', isActive);
    });
}


function getQueryParam(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
}


function showToast(message, isError = false) {

    const toast = document.createElement('div');

    toast.className = 'message-box';

    toast.style.background = isError
        ? '#ffe4e6'
        : '#eef2ff';

    toast.style.color = isError
        ? '#991b1b'
        : '#1d4ed8';

    toast.textContent = message;

    document.querySelector('.content')?.prepend(toast);

    setTimeout(() => {
        toast.remove();
    }, 4500);
}


function showLogoutModal() {
    document
        .getElementById('logoutModalOverlay')
        ?.classList.add('active');
}


function hideLogoutModal() {
    document
        .getElementById('logoutModalOverlay')
        ?.classList.remove('active');
}


function confirmLogout() {

    hideLogoutModal();

    const logoutForm = document.getElementById('logout-form');

    if (logoutForm) {
        logoutForm.submit();
    }
}


/* ==========================================
   INITIALIZE EVERYTHING ONCE
========================================== */

document.addEventListener('DOMContentLoaded', function () {

    initSidebar();
    initNotificationDropdown();
    initModal();

    // Logout modal
    const logoutOverlay =
        document.getElementById('logoutModalOverlay');

    if (logoutOverlay) {

        logoutOverlay.addEventListener('click', function (event) {

            if (event.target === this) {
                hideLogoutModal();
            }

        });
    }

    // Close logout modal with Escape
    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            hideLogoutModal();
        }

    });

});