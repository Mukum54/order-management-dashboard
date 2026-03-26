<!DOCTYPE html>
<html>
<head><style>body { font-family: sans-serif; background: #f4f4f5; } .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; }</style></head>
<body>
    <div class="container">
        <h2 style="color: #EF4444;">Order Cancelled</h2>
        <p>Hello <?= htmlspecialchars($order->customer_name) ?>,</p>
        <p>Your order <strong>#<?= htmlspecialchars($order->order_number) ?></strong> has been cancelled.</p>
        <?php if(!empty($cancel_reason)): ?>
            <p>Reason: <?= htmlspecialchars($cancel_reason) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
