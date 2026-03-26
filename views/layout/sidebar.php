<aside class="sidebar">
    <div style="padding: 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color);">
        <i data-lucide="package" style="color: var(--accent);"></i>
        <h2 class="logo-text" style="font-size: 18px; color: var(--text-primary); margin:0;">Dashboard</h2>
    </div>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="user-info" style="padding: 20px; text-align: center; border-bottom: 1px solid var(--border-color);">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--bg-secondary); margin: 0 auto 10px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
            <i data-lucide="user" size="32" style="color: var(--text-muted);"></i>
        </div>
        <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($_SESSION['name'], ENT_QUOTES) ?></div>
        <div class="badge badge-processing" style="margin-top: 5px; text-transform: uppercase; font-size: 10px;"><?= htmlspecialchars($_SESSION['role'], ENT_QUOTES) ?></div>
    </div>
    
    <nav style="padding: 20px 0; flex-grow: 1;">
        <?php
        $role = $_SESSION['role'] ?? 'customer';
        $navItems = [
            ['icon' => 'shopping-cart', 'label' => 'Orders', 'url' => '/orders', 'roles' => ['admin', 'manager', 'staff']],
        ];
        if (in_array($role, ['admin', 'manager'])) {
            $navItems[] = ['icon' => 'bar-chart-2', 'label' => 'Reports', 'url' => '/reports', 'roles' => ['admin', 'manager']];
        }
        $navItems[] = ['icon' => 'user', 'label' => 'Profile', 'url' => '/profile', 'roles' => ['admin', 'manager', 'staff', 'customer']];
        
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = parse_url(APP_URL, PHP_URL_PATH);
        
        foreach ($navItems as $item): 
            if (!in_array($role, $item['roles'])) continue;
            
            $isActive = str_starts_with($currentUri, $base . $item['url']) ? 'active' : '';
        ?>
            <a href="<?= APP_URL . $item['url'] ?>" class="nav-item <?= $isActive ?>" style="display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: var(--text-secondary); <?= $isActive ? 'border-left: 3px solid var(--accent); background: var(--accent-light); color: var(--accent);' : '' ?>">
                <i data-lucide="<?= $item['icon'] ?>"></i>
                <span class="nav-text" style="font-weight: 600;"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div style="padding: 20px; border-top: 1px solid var(--border-color);">
        <a href="<?= APP_URL ?>/logout" style="display: flex; align-items: center; gap: 12px; color: #EF4444; font-weight: 600;">
            <i data-lucide="log-out"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>
    <?php endif; ?>
</aside>
