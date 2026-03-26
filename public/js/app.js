// app.js
document.addEventListener('DOMContentLoaded', () => {
    // Theme initialization
    const themeToggle = document.getElementById('theme-toggle');
    const htmlEl = document.documentElement;

    const currentTheme = localStorage.getItem('theme') || 'dark';
    htmlEl.setAttribute('data-theme', currentTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const newTheme = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            htmlEl.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            // Optional: send AJAX to profile/theme endpoint to save preference in DB
            fetch(APP_URL + '/api/profile.php?action=theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: `theme=${newTheme}&csrf_token=${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')}`
            }).catch(e => console.error(e));
        });
    }

    // Sidebar Mobile Toggle
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });
    }

    // Notifications Logic
    const notifBell = document.getElementById('notification-bell');
    const notifDropdown = document.getElementById('notif-dropdown');
    const notifBadge = document.getElementById('notif-badge');
    const notifList = document.getElementById('notif-list');

    if (notifBell && notifDropdown) {
        notifBell.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.style.display = notifDropdown.style.display === 'none' ? 'block' : 'none';
            if (notifDropdown.style.display === 'block') {
                fetchNotifications();
            }
        });

        document.addEventListener('click', (e) => {
            if (!notifBell.contains(e.target)) {
                notifDropdown.style.display = 'none';
            }
        });
    }

    function fetchNotifications() {
        if (!notifBadge) return;
        fetch(APP_URL + '/api/notifications.php?action=list', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.unread_count > 0) {
                        notifBadge.style.display = 'flex';
                        notifBadge.textContent = data.unread_count;
                    } else {
                        notifBadge.style.display = 'none';
                    }

                    if (notifList && data.notifications) {
                        if (data.notifications.length === 0) {
                            notifList.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 13px;">No new notifications</div>';
                            return;
                        }

                        let html = '';
                        data.notifications.forEach(n => {
                            html += `
                            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 13px;">
                                <div style="font-weight: 600; margin-bottom: 4px;">${n.title}</div>
                                <div style="color: var(--text-secondary); margin-bottom: 4px;">${n.message}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">${n.created_at}</div>
                            </div>
                        `;
                        });
                        notifList.innerHTML = html;
                    }
                }
            }).catch(e => console.error(e));
    }

    // Initial check
    if (notifBell) fetchNotifications();
});
