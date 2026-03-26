<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="main-content">
    <?php require __DIR__ . '/../layout/topbar.php'; ?>
    
    <div class="content-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="margin-bottom: 8px; font-family: 'JetBrains Mono', monospace;"><?= htmlspecialchars($order->order_number, ENT_QUOTES, 'UTF-8') ?></h2>
                <div style="color: var(--text-secondary); font-size: 13px; display: flex; gap: 16px;">
                    <span><i data-lucide="calendar" size="14"></i> <?= date('M j, Y h:i A', strtotime($order->created_at)) ?></span>
                    <span class="badge badge-<?= $order->status ?>" id="detail-header-status"><?= ucfirst($order->status) ?></span>
                </div>
            </div>
            <div style="display: flex; gap: 12px;" class="no-print">
                <?php if($role !== 'customer'): ?>
                <button class="btn btn-primary" onclick="openStatusModal()">
                    <i data-lucide="edit" size="16"></i> Update Status
                </button>
                <?php endif; ?>
                <button class="btn btn-secondary" onclick="window.location.href='<?= APP_URL ?>/api/orders.php?action=export_pdf&id=<?= $order->id ?>'">
                    <i data-lucide="printer" size="16"></i> Download PDF
                </button>
                <?php if($role !== 'customer'): ?>
                <a href="mailto:<?= htmlspecialchars($order->customer_email, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">
                    <i data-lucide="mail" size="16"></i> Email Customer
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="responsive-grid-2" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- LEFT COLUMN -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Order Items -->
                <div class="card table-responsive">
                    <h3 style="margin-bottom: 16px;">Order Items</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: var(--bg-secondary); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                            <i data-lucide="package" size="20" style="color: var(--text-muted);"></i>
                                        </div>
                                        <?= htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td style="font-family: monospace;"><?= htmlspecialchars($item->product_sku, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format($item->unit_price, 2) ?></td>
                                <td><?= $item->quantity ?></td>
                                <td style="text-align: right; font-weight: 600;">$<?= number_format($item->subtotal, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; border: none; padding-top: 16px;">Subtotal</td>
                                <td style="text-align: right; border: none; padding-top: 16px;">$<?= number_format($order->subtotal, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; border: none; padding-top: 8px;">Discount</td>
                                <td style="text-align: right; border: none; padding-top: 8px; color: #EF4444;">-$<?= number_format($order->discount, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; border: none; padding-top: 8px;">Tax</td>
                                <td style="text-align: right; border: none; padding-top: 8px;">$<?= number_format($order->tax, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; border: none; padding-top: 16px; font-size: 18px; font-weight: 700;">TOTAL</td>
                                <td style="text-align: right; border: none; padding-top: 16px; font-size: 18px; font-weight: 700;">$<?= number_format($order->total, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="responsive-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- Customer Info -->
                    <div class="card">
                        <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i data-lucide="user" size="20"></i> Customer Info</h3>
                        <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8') ?></div>
                        <div style="color: var(--text-secondary); margin-bottom: 4px;">
                            <a href="mailto:<?= htmlspecialchars($order->customer_email, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($order->customer_email, ENT_QUOTES, 'UTF-8') ?></a>
                        </div>
                        <div style="color: var(--text-secondary);">
                            <a href="tel:<?= htmlspecialchars($order->customer_phone ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($order->customer_phone ?? 'No phone', ENT_QUOTES, 'UTF-8') ?></a>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="card">
                        <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i data-lucide="credit-card" size="20"></i> Payment Info</h3>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-secondary);">Method:</span>
                            <span style="font-weight: 600;"><?= htmlspecialchars($order->payment_method ?? 'Not specified', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-secondary);">Status:</span>
                            <?php
                                $pBadge = 'warning';
                                if($order->payment_status === 'paid') $pBadge = 'success';
                                if($order->payment_status === 'refunded') $pBadge = 'cancelled';
                            ?>
                            <span class="badge badge-<?= $pBadge ?>" style="text-transform: uppercase;"><?= htmlspecialchars($order->payment_status, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card">
                    <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i data-lucide="truck" size="20"></i> Shipping Address</h3>
                    <?php 
                        $addr = json_decode($order->shipping_address, true);
                        if ($addr) {
                            echo '<p>' . htmlspecialchars($addr['street'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
                            echo '<p>' . htmlspecialchars($addr['city'] ?? '', ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($addr['zip'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
                        } else {
                            echo '<p>' . htmlspecialchars($order->shipping_address, ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                    ?>
                    <?php if($order->tracking_number): ?>
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary);">Tracking Number:</span> 
                            <span style="font-weight: 600; font-family: monospace;"><?= htmlspecialchars($order->tracking_number, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div style="display: flex; flex-direction: column; gap: 24px;" class="no-print">
                
                <?php if($role !== 'customer'): ?>
                <!-- Internal Notes -->
                <div class="card">
                    <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i data-lucide="message-square" size="20"></i> Internal Notes</h3>
                    <div id="notes-container" style="max-height: 300px; overflow-y: auto; margin-bottom: 16px; font-size: 13px; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap;"><?= htmlspecialchars($order->notes ?? 'No internal notes yet.', ENT_QUOTES, 'UTF-8') ?></div>
                    <form id="add-note-form" style="display:flex; gap: 8px;">
                        <input type="text" id="new-note" class="form-control" placeholder="Add a note..." required>
                        <button type="submit" class="btn btn-secondary"><i data-lucide="send" size="16"></i></button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Status Timeline -->
                <div class="card">
                    <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i data-lucide="clock" size="20"></i> Timeline</h3>
                    <div style="position: relative; padding-left: 20px;" id="timeline">
                        <div style="position: absolute; top: 0; bottom: 0; left: 6px; width: 2px; background: var(--border-color);"></div>
                        
                        <?php foreach($history as $i => $h): ?>
                        <div style="position: relative; margin-bottom: 20px;">
                            <div style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: <?= $i === 0 ? 'var(--accent)' : 'var(--text-muted)' ?>; z-index: 2; border: 2px solid var(--bg-card);"></div>
                            <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">
                                Status changed to <span style="text-transform: lowercase;"><?= htmlspecialchars($h->new_status, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">
                                By <?= htmlspecialchars($h->user_name, ENT_QUOTES, 'UTF-8') ?> on <?= date('M j, Y h:i A', strtotime($h->created_at)) ?>
                            </div>
                            <?php if($h->comment): ?>
                            <div style="font-size: 13px; font-style: italic; background: var(--bg-secondary); padding: 8px; border-radius: 6px; margin-top: 8px;">
                                "<?= htmlspecialchars($h->comment, ENT_QUOTES, 'UTF-8') ?>"
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Initial placement -->
                        <div style="position: relative;">
                            <div style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--text-muted); z-index: 2; border: 2px solid var(--bg-card);"></div>
                            <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">Order Placed</div>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                By <?= htmlspecialchars($order->customer_name, ENT_QUOTES, 'UTF-8') ?> on <?= date('M j, Y h:i A', strtotime($order->created_at)) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include the modal inline
require __DIR__ . '/status.php'; 
?>

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none; box-shadow: none; padding: 0; }
    body { background: white; color: black; }
}
</style>

<script>
    const orderId = <?= $order->id ?>;
    const csrfToken = '<?= Core\Auth::generateCsrf() ?>';
    
    // Add Note logic
    const noteForm = document.getElementById('add-note-form');
    if(noteForm) {
        noteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const noteBtn = this.querySelector('button');
            const noteInput = document.getElementById('new-note');
            
            noteBtn.disabled = true;
            fetch('<?= APP_URL ?>/api/orders.php?action=add_note', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken
                },
                body: `order_id=${orderId}&note=${encodeURIComponent(noteInput.value)}&csrf_token=${csrfToken}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const container = document.getElementById('notes-container');
                    if(container.textContent.includes('No internal notes yet')) {
                        container.textContent = '';
                    }
                    container.textContent += data.note;
                    noteInput.value = '';
                    Toast.show('Note added', 'success');
                    container.scrollTop = container.scrollHeight;
                } else {
                    Toast.show(data.error, 'error');
                }
            })
            .finally(() => { noteBtn.disabled = false; });
        });
    }
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
