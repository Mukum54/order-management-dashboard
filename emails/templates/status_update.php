<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Calibri', sans-serif; background-color: #f4f4f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #3B82F6; padding: 20px; color: #ffffff; text-align: center; }
        .content { padding: 30px; color: #334155; line-height: 1.6; }
        .footer { padding: 20px; background-color: #f8fafc; color: #94A3B8; text-align: center; font-size: 13px; border-top: 1px solid #e2e8f0; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #3B82F6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 14px; font-weight: bold; text-transform: uppercase; background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Order Status Update</h2>
        </div>
        <div class="content">
            <p>Hello <?= htmlspecialchars($order->customer_name) ?>,</p>
            <p>The status of your order <strong>#<?= htmlspecialchars($order->order_number) ?></strong> has been updated.</p>
            
            <div style="margin: 20px 0; padding: 20px; background: #f8fafc; border-radius: 6px; text-align: center;">
                <p style="margin: 0 0 10px 0; color: #64748B;">New Status:</p>
                <div class="badge"><?= htmlspecialchars($new_status) ?></div>
            </div>

            <?php if(!empty($comment)): ?>
            <p style="font-style: italic; color: #64748B; border-left: 4px solid #3B82F6; padding-left: 10px;">
                "<?= htmlspecialchars($comment) ?>"
            </p>
            <?php endif; ?>

            <div style="text-align: center;">
                <a href="<?= APP_URL ?>/orders/<?= $order->id ?>" class="btn">View Order Details</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> Order Management Dashboard. All rights reserved.</p>
            <p>You received this email because you opted in for notifications.</p>
        </div>
    </div>
</body>
</html>
