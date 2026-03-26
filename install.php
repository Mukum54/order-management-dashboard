<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

try {
    $db = Core\Database::getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        die("<html><body><h2>Installation Already Complete</h2><p>The 'users' table already exists. To reinstall, please drop the tables in your database first or remove this install.php file for security.</p>
        <a href='/login'>Go to Login</a></body></html>");
    }
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Unknown database') === false && strpos($e->getMessage(), 'Access denied') === false) {
        // If it's a connection issue but not an missing DB, show error
        die("<html><body><h2>Database Connection Failed</h2><p>" . htmlspecialchars($e->getMessage()) . "</p></body></html>");
    }
}

// Generate the setup UI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic setup: read schema.sql and execute
    $schemaFile = __DIR__ . '/database/schema.sql';
    $seedFile = __DIR__ . '/database/seeder.sql';
    
    if (!file_exists($schemaFile)) {
        die("schema.sql not found!");
    }

    try {
        $db = Core\Database::getConnection();
        
        // Load schema
        $sql = file_get_contents($schemaFile);
        $db->exec($sql);
        
        // Attempt to load seeder if exists
        if (file_exists($seedFile)) {
            $seeder = file_get_contents($seedFile);
            if (!empty($seeder)) {
                $db->exec($seeder);
            }
        }
        
        // Hardcode an admin fallback if seeder fails or doesn't have an admin
        $stmt = $db->query("SELECT * FROM users WHERE role = 'admin'");
        if ($stmt->rowCount() === 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'admin', 1)")
               ->execute(['System Admin', 'admin@example.com', $hash]);
        }
        
        echo "<html><body style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
              <h2 style='color: green;'>Installation Successful!</h2>
              <p>Database tables created and seeded.</p>
              <p>Default Admin: <strong>admin@example.com</strong> / <strong>admin123</strong></p>
              <a href='/' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#3B82F6; color:#fff; text-decoration:none; border-radius:5px;'>Proceed to App</a>
              <p style='color:red; margin-top:20px;'><small>WARNING: Please delete install.php from your root directory immediately.</small></p>
              </body></html>";
              
        exit;
    } catch (\PDOException $e) {
        die("<html><body><h2>Installation Failed</h2><p>" . htmlspecialchars($e->getMessage()) . "</p></body></html>");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install Order Management Dashboard</title>
    <style>
        body { background: #0F172A; color: #fff; font-family: 'Inter', sans-serif; display: flex; center; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .install-box { background: #1E293B; padding: 40px; border-radius: 12px; max-width: 500px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .btn { padding: 12px 24px; background: #3B82F6; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; margin-top: 20px; }
        .btn:hover { background: #2563EB; }
    </style>
</head>
<body>
    <div class="install-box">
        <h1 style="margin-top:0;">Order Dashboard Setup</h1>
        <p style="color: #94A3B8; margin-bottom: 30px;">This wizard will initialize your database using <code>config/database.php</code> credentials, construct schemas, and inject default administrative roles.</p>
        
        <form method="POST">
            <button type="submit" class="btn">Run Installation</button>
        </form>
    </div>
</body>
</html>
