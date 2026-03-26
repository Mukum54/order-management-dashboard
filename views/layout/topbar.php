<header class="topbar">
    <div style="display: flex; align-items: center; gap: 16px;">
        <button id="mobile-menu-toggle" style="background: none; border: none; color: var(--text-primary); cursor: pointer;">
            <i data-lucide="menu"></i>
        </button>
        <div style="font-weight: 600; font-size: 18px; color: var(--text-secondary);" id="breadcrumb">
            <?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES) ?>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 20px;">
        <button id="theme-toggle" style="background: none; border: none; color: var(--text-secondary); cursor: pointer;">
            <i data-lucide="moon"></i>
        </button>
        <div style="position: relative; cursor: pointer;" id="notification-bell">
            <i data-lucide="bell" style="color: var(--text-secondary);"></i>
            <span id="notif-badge" style="position: absolute; top: -5px; right: -5px; background: #EF4444; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 10px; display: none; align-items: center; justify-content: center; font-weight: bold;">0</span>
            
            <div id="notif-dropdown" class="card" style="display: none; position: absolute; right: -10px; top: 30px; width: 320px; padding: 0; z-index: 1000; overflow: hidden; box-shadow: var(--shadow-lg);">
                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-weight: 600; background: var(--bg-secondary);">Notifications</div>
                <div id="notif-list" style="max-height: 300px; overflow-y: auto;">
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 13px;">Loading...</div>
                </div>
                <div style="padding: 10px; text-align: center; border-top: 1px solid var(--border-color); font-size: 12px; color: var(--accent); background: var(--bg-primary); cursor: pointer;" onclick="Toast.show('Marked all as read', 'success')">
                    Mark all as read
                </div>
            </div>
        </div>
    </div>
</header>
