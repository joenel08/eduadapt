<script>
(function () {
    console.log('🔔 Notification script running');

    async function loadNotifications() {
        console.log('🔔 Loading...');
        try {
            const res = await fetch('/notifications', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            console.log('🔔 Status:', res.status);

            if (!res.ok) return;
            const data = await res.json();
            console.log('🔔 Data:', data);

            const list  = document.getElementById('notificationList');
            const badge = document.getElementById('notificationBadge');

            if (!list) {
                console.warn('🔔 #notificationList not found');
                return;
            }

            if (badge) {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            if (!data.notifications || !data.notifications.length) {
                list.innerHTML = '<div style="padding:30px 20px;text-align:center;color:#999;font-size:13px;">No notifications yet</div>';
                return;
            }

            list.innerHTML = data.notifications.map(function (n) {
                return '<a class="notification-item ' + (n.read ? '' : 'unread') + '" ' +
                       'href="' + (n.link || '#') + '" ' +
                       'data-id="' + n.id + '" ' +
                       'data-link="' + (n.link || '') + '" ' +
                       'onclick="return window._notifClick(event, this)">' +
                       '<div class="notification-icon-circle ' + n.color + '">' +
                       '<i class="fas ' + n.icon + '"></i></div>' +
                       '<div class="notification-body">' +
                       '<div class="notification-item-title">' + (n.title || '') + '</div>' +
                       '<div class="notification-item-text">' + (n.message || '') + '</div>' +
                       '<div class="notification-item-time">' + (n.time || '') + '</div>' +
                       '</div></a>';
            }).join('');
        } catch (err) {
            console.error('🔔 Error:', err);
        }
    }

    window._notifClick = function (event, el) {
        event.preventDefault();
        fetch('/notifications/' + el.dataset.id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).finally(function () {
            loadNotifications();
            if (el.dataset.link) window.location.href = el.dataset.link;
        });
        return false;
    };

    window.markAllNotificationsRead = function () {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).finally(loadNotifications);
    };

    // Run immediately and every 30s
    loadNotifications();
    setInterval(loadNotifications, 30000);
})();
</script>
<style>
    .notification-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s ease;
    text-decoration: none;
    color: inherit;
}
.notification-item:hover { background: #f9f9f9; }
.notification-item.unread { background: #f0f7ff; }

.notification-icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.notification-icon-circle.blue   { background: #e0f2fe; color: #0369a1; }
.notification-icon-circle.green  { background: #dcfce7; color: #15803d; }
.notification-icon-circle.orange { background: #fef3c7; color: #b45309; }
.notification-icon-circle.red    { background: #fee2e2; color: #b91c1c; }

.notification-body { flex: 1; min-width: 0; }
.notification-item-title {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 3px;
}
.notification-item-text {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
    word-wrap: break-word;
}
.notification-item-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
}
</style>
<div class="top-bar">
    <div class="top-bar-left">
        <button class="hamburger-btn" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
        <div class="top-bar-brand">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="logo-text">
                <div class="brand-title">
                    <span class="edu">Edu</span><span class="adapt">Adapt</span>
                </div>
            </div>
        </div>
    </div>

    <div class="top-bar-right">
        <div class="notification-container">
            <button class="notification-btn" id="notificationBtn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge" style="display:none;">0</span>
            </button>

            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <span>Notifications</span>
                    <button class="notif-mark-all" onclick="markAllNotificationsRead()" style="background:none;border:none;color:#0066CC;font-size:12px;cursor:pointer;font-weight:600;">
                        Mark all as read
                    </button>
                </div>

                <div id="notificationList">
                    <div class="notification-empty" style="padding:30px 20px;text-align:center;color:#999;">
                        <i class="fas fa-bell-slash" style="font-size:24px;margin-bottom:8px;display:block;opacity:0.5;"></i>
                        No notifications yet
                    </div>
                </div>
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
