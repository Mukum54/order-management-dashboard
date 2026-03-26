<!DOCTYPE html>
<html>
<head><style>body { font-family: sans-serif; background: #f4f4f5; } .container { max-width: 600px; margin: 20px auto; background: #fff; }</style></head>
<body>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #3B82F6;">Welcome to Order Dashboard</h2>
        <p>Hello <?= htmlspecialchars($user->name) ?>,</p>
        <p>Your account has been created successfully.</p>
        <p>You can login at <a href="<?= APP_URL ?>/login"><?= APP_URL ?>/login</a>.</p>
    </div>
</body>
</html>
