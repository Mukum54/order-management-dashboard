<!DOCTYPE html>
<html>
<head><style>body { font-family: sans-serif; background: #f4f4f5; } .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; }</style></head>
<body>
    <div class="container">
        <h2 style="color: #F97316;">Order Refunded</h2>
        <p>Hello <?= htmlspecialchars($order->customer_name) ?>,</p>
        <p>Your order <strong>#<?= htmlspecialchars($order->order_number) ?></strong> has been successfully refunded.</p>
        <p>Please allow 3-5 business days for the funds to appear in your account.</p>
    </div>
</body>
</html>
