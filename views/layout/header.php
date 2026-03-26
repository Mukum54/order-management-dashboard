<?php
$theme = $_SESSION['theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('APP_NAME') ? APP_NAME : 'Dashboard' ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/components.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/responsive.css">
    <meta name="csrf-token" content="<?= Core\Auth::generateCsrf() ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .topbar {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            background: rgba(var(--bg-card), 0.8);
            backdrop-filter: blur(8px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, transform 0.3s ease;
            z-index: 101;
        }
    </style>
    <script>
        const APP_URL = '<?= APP_URL ?>';
    </script>
</head>
<body>
