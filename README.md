# Order Management Dashboard

A full-stack, production-ready Order Management Dashboard built with PHP 8.1+ and MariaDB. Features a dynamic dark/light UI theme, role-based access control, Chart.js analytics, DOM-based AJAX status updates, dynamic PDF invoicing via DomPDF, full audit logging, and PHPMailer email notifications.

## Features Added & Verified
- **Phase 1 Security:** BCRYPT (cost 12) passwords, brute-force locking (`failed_login_attempts`), strict session expiration configs, XSS prevention via `htmlspecialchars`, and full CSRF protection on forms & AJAX requests.
- **Phase 2 Functionality:** Advanced order pagination/filtering, date range selections, real-time status UI updating, CSV Exports, and a responsive custom reporting engine.
- **Phase 3 Advanced Additions:**
  - **Setup Wizard:** An automated `/install.php` installer to configure standard Database Schemas and an `admin@example.com` account instantly.
  - **Invoicing:** Server-rendered PDF downloads mapped to `Order Details` via the `dompdf/dompdf` integration.
  - **Audit Logs:** Direct database tracking for system actions (e.g., login activity and status adjustments).
  - **Inventory Sync:** Auto-increments product stock quantities upon flagging an order as `Refunded`.
  - **Error Pages:** Custom 403 Forbidden and 404 Not Found templates injected directly into the routing engine.

## Requirements
- Kali Linux (or any Ubuntu/Debian derivative)
- Apache 2.4+
- MariaDB 10.6+
- PHP 8.1+
- Composer

## Installation Commands

**1. Install AMP Stack and Composer:**
```bash
sudo apt update
sudo apt install apache2 mariadb-server php8.1 php8.1-mysql php-mbstring curl -y
sudo systemctl enable apache2 mariadb
sudo systemctl start apache2 mariadb
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**2. Setup Application Dependencies:**
Navigate into your cloned directory to install PHP resources.
```bash
cd /path/to/order-dashboard
composer require phpmailer/phpmailer
composer require dompdf/dompdf
```

**3. Configure The Server/Database:**
Create the physical database and root user:
```bash
sudo mysql -u root -p
```
Inside MySQL:
```sql
CREATE DATABASE order_dashboard_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dashboard_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON order_dashboard_db.* TO 'dashboard_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**4. Run Automatically via Auto-Installer Wizard:**
Instead of manually mapping the `.sql` schema, host the app locally and run the internal wizard!

```bash
cd /path/to/order-dashboard
php -S localhost:8000
```
Visit `http://localhost:8000/install.php` in your browser. This will autonomously construct your `users`, `orders`, `products`, and `audit_logs` tables, bypassing manual command-line SQL imports.

*(Alternatively, to host via pure Apache virtual hosts, map your DocumentRoot to the `order-dashboard` folder, edit `/etc/hosts`, and browse to your predefined `ServerName` route like `http://order-dashboard.local/install.php`)*

**Access Control:**
For security reasons, default passwords are not listed in this public documentation. 

- **Testing Credentials:** Please request the administrative and test account passwords directly from the project owner.
- **Setup Wizard:** If running a fresh installation, you will be prompted to define your own secure credentials during the `/install.php` process.

## Configuration Notes
- **SMTP Emails:** Edit `config/mail.php` with actual provider credentials to utilize automated tracking/status alerts.
- **Security Check:** Remember to remove `install.php` once your server reaches a production environment to disable schema overwrites!
