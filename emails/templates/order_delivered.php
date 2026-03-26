<!DOCTYPE html>
<html>
<head><style>body { font-family: sans-serif; background: #f4f4f5; } .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; }</style></head>
<body>
    <div class="container">
        <h2 style="color: #10B981;">Order Delivered</h2>
        <p>Hello <?= htmlspecialchars($order->customer_name) ?>,</p>
        <p>Your order <strong>#<?= htmlspecialchars($order->order_number) ?></strong> has been marked as delivered.</p>
        <p>We hope you enjoy your purchase!</p>
    </div>
</body>
</html>
