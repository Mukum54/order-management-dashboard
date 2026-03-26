<!DOCTYPE html>
<html>
<head><style>body { font-family: sans-serif; background: #f4f4f5; } .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; }</style></head>
<body>
    <div class="container">
        <h2 style="color: #3B82F6;">Password Reset Request</h2>
        <p>Hello <?= htmlspecialchars($user->name) ?>,</p>
        <p>You requested a password reset. Click the following link to reset it:</p>
        <p><a href="<?= $reset_link ?>"><?= $reset_link ?></a></p>
        <p>This link expires at <?= $expires_at ?>.</p>
    </div>
</body>
</html>
