<?php 
http_response_code(403);
require __DIR__ . '/../layout/header.php'; 
?>
<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; text-align: center; padding: 20px;">
    <i data-lucide="shield-alert" style="width: 80px; height: 80px; color: var(--text-muted); margin-bottom: 24px;"></i>
    <h1 style="font-size: 36px; margin-bottom: 16px;">403 - Access Denied</h1>
    <p style="color: var(--text-secondary); max-width: 400px; margin-bottom: 24px;">You do not have the required permissions to view this page or perform this action.</p>
    <a href="<?= APP_URL ?>/" class="btn btn-primary">Return to Dashboard</a>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
