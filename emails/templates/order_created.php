<!DOCTYPE html>
<html>
<head><style>body { font-family: sans-serif; background: #f4f4f5; } .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; }</style></head>
<body>
    <div class="container">
        <h2 style="color: #3B82F6;">Order Received!</h2>
        <p>Hello <?= htmlspecialchars($customer->name) ?>,</p>
        <p>Thank you for your order <strong>#<?= htmlspecialchars($order->order_number) ?></strong>.</p>
        <p>We're processing it now and will send you an update when it's shipped.</p>
        <h3>Total: $<?= number_format($order->total, 2) ?></h3>
        <p><a href="<?= APP_URL ?>/orders/<?= $order->id ?>" style="display:inline-block; padding:10px 20px; background:#3B82F6; color:#fff; text-decoration:none; border-radius:5px;">Track Order</a></p>
    </div>
</body>
</html>
