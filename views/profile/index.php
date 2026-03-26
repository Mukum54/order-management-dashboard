<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="main-content">
    <?php require __DIR__ . '/../layout/topbar.php'; ?>
    
    <div class="content-wrapper">
        <h2 style="margin-bottom: 24px;">Profile & Settings</h2>

        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
            <!-- Sidebar Tabs -->
            <div class="card" id="profile-sidebar" style="width: 250px; flex-shrink: 0;">
                <ul id="profile-tabs" style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 8px;">
                        <a href="#my-profile" class="tab-btn active" style="display: block; padding: 10px 16px; border-radius: 8px; font-weight: 600;">My Profile</a>
                    </li>
                    <li style="margin-bottom: 8px;">
                        <a href="#preferences" class="tab-btn" style="display: block; padding: 10px 16px; border-radius: 8px; font-weight: 600;">Preferences</a>
                    </li>
                    <?php if($user->role === 'customer'): ?>
                    <li style="margin-bottom: 8px;">
                        <a href="#my-orders" class="tab-btn" style="display: block; padding: 10px 16px; border-radius: 8px; font-weight: 600;">My Orders</a>
                    </li>
                    <?php endif; ?>
                    <?php if($user->role === 'admin'): ?>
                    <li>
                        <a href="#user-management" class="tab-btn" style="display: block; padding: 10px 16px; border-radius: 8px; font-weight: 600;">User Management</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="card" style="flex-grow: 1;">
                <!-- Profile Tab -->
                <div id="my-profile" class="tab-pane active">
                    <h3 style="margin-bottom: 20px;">Personal Information</h3>
                    <form id="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= Core\Auth::generateCsrf() ?>">
                        <div style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap;">
                            <div>
                                <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--bg-secondary); overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <?php if($user->avatar): ?>
                                        <img src="<?= APP_URL ?>/public/img/<?= htmlspecialchars($user->avatar, ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i data-lucide="user" size="48" style="color: var(--text-muted);"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="responsive-grid-2" style="flex-grow: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email (Read Only)</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user->phone ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" value="<?= strtoupper(htmlspecialchars($user->role, ENT_QUOTES, 'UTF-8')) ?>" disabled>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i data-lucide="save" size="16"></i> Save Changes</button>
                    </form>

                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 32px 0;">

                    <h3 style="margin-bottom: 20px;">Change Password</h3>
                    <form id="password-form">
                        <input type="hidden" name="csrf_token" value="<?= Core\Auth::generateCsrf() ?>">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 16px; max-width: 400px;">
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>

                <!-- Preferences Tab -->
                <div id="preferences" class="tab-pane" style="display: none;">
                    <h3 style="margin-bottom: 20px;">Account Preferences</h3>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="pref-email-notif" <?= $user->email_notifications ? 'checked' : '' ?>> 
                            Receive Email Notifications
                        </label>
                        <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px; margin-left: 24px;">We'll email you about order status updates</p>
                    </div>
                </div>

                <?php if($user->role === 'customer'): ?>
                <!-- My Orders Tab -->
                <div id="my-orders" class="tab-pane" style="display: none;">
                    <h3 style="margin-bottom: 20px;">My Orders</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($myOrders as $ord): ?>
                                <tr>
                                    <td><a href="<?= APP_URL ?>/orders/<?= $ord->id ?>"><?= htmlspecialchars($ord->order_number, ENT_QUOTES, 'UTF-8') ?></a></td>
                                    <td><?= date('Y-m-d', strtotime($ord->created_at)) ?></td>
                                    <td>$<?= number_format($ord->total, 2) ?></td>
                                    <td><span class="badge badge-<?= $ord->status ?>"><?= ucfirst(htmlspecialchars($ord->status, ENT_QUOTES, 'UTF-8')) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($user->role === 'admin'): ?>
                <!-- User Mgmt Tab -->
                <div id="user-management" class="tab-pane" style="display: none;">
                    <h3 style="margin-bottom: 20px;">User Management</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($allUsers as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u->name, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($u->email, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge badge-processing"><?= strtoupper($u->role) ?></span></td>
                                    <td>
                                        <?php if($u->is_active): ?>
                                            <span style="color: #10B981;">Active</span>
                                        <?php else: ?>
                                            <span style="color: #EF4444;">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" onclick="toggleUserStatus(<?= $u->id ?>)">Toggle Status</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
.tab-btn {
    color: var(--text-secondary);
}
.tab-btn:hover {
    background: var(--bg-secondary);
}
.tab-btn.active {
    background: var(--accent-light);
    color: var(--accent);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tab switching logic
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.style.display = 'none');
            
            tab.classList.add('active');
            const target = document.querySelector(tab.getAttribute('href'));
            if(target) target.style.display = 'block';
        });
    });

    // Handle Profile Form Submit
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('<?= APP_URL ?>/api/profile.php?action=update', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Toast.show(data.message, 'success');
            } else {
                Toast.show(data.error || 'Update failed', 'error');
            }
        });
    });
    // Handle Password form
    document.getElementById('password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
        
        fetch('<?= APP_URL ?>/api/profile.php?action=update_password', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Toast.show(data.message, 'success');
                this.reset();
            } else {
                Toast.show(data.error || 'Update failed', 'error');
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Password';
        });
    });

    // Preferences toggle handling
    const prefToggle = document.getElementById('pref-email-notif');
    if (prefToggle) {
        prefToggle.addEventListener('change', (e) => {
            const formData = new FormData();
            formData.append('email_notifications', e.target.checked ? 1 : 0);
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            if(tokenEl) {
                formData.append('csrf_token', tokenEl.getAttribute('content'));
            }
            
            fetch('<?= APP_URL ?>/api/profile.php?action=update_preferences', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Toast.show('Preferences auto-saved', 'success');
                } else {
                    Toast.show(data.error || 'Failed to save', 'error');
                    e.target.checked = !e.target.checked;
                }
            });
        });
    }

});

function toggleUserStatus(userId) {
    if(!confirm('Are you sure you want to toggle this user\'s access?')) return;
    
    const formData = new FormData();
    formData.append('user_id', userId);
    const tokenEl = document.querySelector('meta[name="csrf-token"]');
    if(tokenEl) {
        formData.append('csrf_token', tokenEl.getAttribute('content'));
    }

    fetch('<?= APP_URL ?>/api/profile.php?action=toggle_user_status', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Toast.show('User status toggled successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            Toast.show(data.error || 'Failed to toggle user', 'error');
        }
    });
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
