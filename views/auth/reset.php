<?php require __DIR__ . '/../layout/header.php'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h2 style="margin-bottom: 24px;">Reset Password</h2>
        <form id="reset-form" onsubmit="handleReset(event)">
            <input type="hidden" name="csrf_token" value="<?= Core\Auth::generateCsrf() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Save New Password</button>
        </form>
    </div>
</div>
<script>
function handleReset(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fetch('<?= APP_URL ?>/reset-password', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Toast.show('Password reset successfully! Redirecting...', 'success');
            setTimeout(() => window.location.href = data.redirect, 2000);
        } else {
            Toast.show(data.error || 'Failed', 'error');
        }
    });
}
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
