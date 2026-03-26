<?php require __DIR__ . '/../layout/header.php'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h2 style="margin-bottom: 24px;">Forgot Password</h2>
        <form id="forgot-form" onsubmit="handleForgot(event)">
            <input type="hidden" name="csrf_token" value="<?= Core\Auth::generateCsrf() ?>">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
            <div style="margin-top: 16px; text-align: center;">
                <a href="<?= APP_URL ?>/login" style="color: var(--accent); font-size: 14px;">Back to Login</a>
            </div>
        </form>
    </div>
</div>
<script>
function handleForgot(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fetch('<?= APP_URL ?>/forgot-password', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Toast.show(data.message, 'success');
        } else {
            Toast.show(data.error || 'Failed', 'error');
        }
    });
}
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
