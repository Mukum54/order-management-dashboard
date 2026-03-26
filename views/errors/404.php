<?php 
http_response_code(404);
require __DIR__ . '/../layout/header.php'; 
?>
<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; text-align: center; padding: 20px;">
    <div style="font-size: 100px; font-weight: 800; color: var(--text-muted); opacity: 0.5; line-height: 1;">404</div>
    <h1 style="font-size: 28px; margin-bottom: 16px; margin-top: -10px;">Page Not Found</h1>
    <p style="color: var(--text-secondary); max-width: 400px; margin-bottom: 24px;">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
    <a href="<?= APP_URL ?>/" class="btn btn-primary">Return to Dashboard</a>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
