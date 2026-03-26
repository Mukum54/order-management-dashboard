<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= defined('APP_NAME') ? APP_NAME : 'Dashboard' ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/components.css">
    <meta name="csrf-token" content="<?= Core\Auth::generateCsrf() ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .split-layout {
            display: flex;
            min-height: 100vh;
        }
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1E3A8A 0%, #0F172A 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .right-panel {
            flex: 1;
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
        }
        .particles {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px);
            background-size: 30px 30px;
            opacity: 0.3;
            animation: move-bg 20s linear infinite;
        }
        @keyframes move-bg {
            0% { background-position: 0 0; }
            100% { background-position: 100px 100px; }
        }
        @media (max-width: 768px) {
            .left-panel { display: none; }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <div style="position: absolute; top: 20px; right: 20px; z-index: 10;">
        <button id="theme-toggle" class="btn btn-secondary" style="border: none;">
            <i data-lucide="moon" size="20"></i>
        </button>
    </div>
    
    <div class="left-panel">
        <div class="particles"></div>
        <div style="z-index: 1; text-align: center;">
            <i data-lucide="package" style="color: #3B82F6; width: 64px; height: 64px; margin-bottom: 20px;"></i>
            <h1 style="font-size: 36px; margin-bottom: 10px;">Order Dashboard</h1>
            <p style="font-size: 18px; opacity: 0.8;">Manage Orders. Drive Growth.</p>
        </div>
    </div>
    
    <div class="right-panel">
        <div class="login-box card">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2>Welcome Back</h2>
                <p style="color: var(--text-secondary);">Please login to your account</p>
            </div>
            
            <div id="error-msg" style="display: none; background: #FEE2E2; color: #991B1B; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center;"></div>

            <form id="login-form">
                <input type="hidden" name="csrf_token" value="<?= Core\Auth::generateCsrf() ?>">
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required autofocus placeholder="name@example.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                        <button type="button" id="toggle-pwd" style="position: absolute; right: 10px; top: 10px; background: none; border: none; color: var(--text-secondary); cursor: pointer;">
                            <i data-lucide="eye" size="18"></i>
                        </button>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary);">
                        <input type="checkbox" name="remember_me"> Remember Me
                    </label>
                    <a href="<?= APP_URL ?>/forgot-password" style="font-size: 13px;">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">
                    <span id="btn-text">Sign In</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Toggle password
    const togglePwd = document.getElementById('toggle-pwd');
    const pwdInput = document.getElementById('password');
    togglePwd.addEventListener('click', () => {
        const type = pwdInput.getAttribute('type') === 'password' ? 'text' : 'password';
        pwdInput.setAttribute('type', type);
        togglePwd.innerHTML = type === 'password' ? '<i data-lucide="eye" size="18"></i>' : '<i data-lucide="eye-off" size="18"></i>';
        lucide.createIcons();
    });

    // Handle login AJAX
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const errorDiv = document.getElementById('error-msg');
        
        btn.disabled = true;
        btn.innerHTML = 'Signing in...';
        errorDiv.style.display = 'none';

        const formData = new FormData(this);

        fetch('<?= APP_URL ?>/login', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                errorDiv.textContent = data.error;
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = 'Sign In';
            }
        })
        .catch(err => {
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = 'Sign In';
        });
    });
</script>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
