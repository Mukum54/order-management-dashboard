<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <!-- Inherit same styles inline for production, keeping short here -->
    <style>
        body { font-family: 'Calibri', sans-serif; background-color: #f4f4f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; margin-top: 20px; }
        .header { background-color: #3B82F6; padding: 20px; color: #ffffff; text-align: center; }
        .content { padding: 30px; color: #334155; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Order Shipped!</h2>
        </div>
        <div class="content">
            <p>Hello <?= htmlspecialchars($order->customer_name) ?>,</p>
            <p>Good news! Your order <strong>#<?= htmlspecialchars($order->order_number) ?></strong> has been shipped.</p>
            
            <?php if(!empty($tracking_number)): ?>
            <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-left: 4px solid #10B981;">
                <strong>Tracking Number:</strong> <?= htmlspecialchars($tracking_number) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
