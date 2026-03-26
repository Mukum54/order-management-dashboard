<!-- Status Update Modal -->
<div class="modal-overlay" id="status-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Update Order Status</h3>
            <button class="modal-close" onclick="closeStatusModal()"><i data-lucide="x"></i></button>
        </div>
        
        <form id="status-form">
            <input type="hidden" name="csrf_token" value="<?= Core\Auth::generateCsrf() ?>">
            <input type="hidden" name="order_id" value="<?= $order->id ?>">
            
            <div class="form-group">
                <label class="form-label">Current Status</label>
                <div style="padding: 10px; background: var(--bg-secondary); border-radius: 8px;">
                    <span class="badge badge-<?= $order->status ?>"><?= ucfirst($order->status) ?></span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">New Status</label>
                <select name="status" class="form-control" id="new-status-select" required onchange="handleStatusChange(this.value)">
                    <option value="" disabled selected>Select status...</option>
                    
                    <?php if($role === 'customer'): ?>
                        <?php if($order->status === 'pending'): ?>
                        <option value="cancelled">Cancelled</option>
                        <?php endif; ?>
                        
                    <?php elseif($role === 'staff'): ?>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        
                    <?php else: // manager or admin ?>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <?php if($role === 'admin'): ?>
                        <option value="refunded">Refunded</option>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <?php if(in_array($role, ['admin','manager'])): ?>
            <div class="form-group">
                <label class="form-label">Assign To</label>
                <select name="assign_to" class="form-control">
                    <option value="">-- Unassigned --</option>
                    <?php foreach($staffMembers as $u): if(in_array($u->role, ['staff','manager','admin'])): ?>
                        <option value="<?= $u->id ?>" <?= $order->assigned_to == $u->id ? 'selected' : '' ?>><?= htmlspecialchars($u->name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="form-group" id="tracking-field" style="display: none;">
                <label class="form-label">Tracking Number</label>
                <input type="text" name="tracking_number" class="form-control" placeholder="e.g. 1Z9999999999999999">
            </div>
            
            <div class="form-group">
                <label class="form-label">Comment / Reason (Required for Cancel/Refund)</label>
                <textarea name="comment" id="status-comment" class="form-control" rows="3" placeholder="Add a comment for the timeline..."></textarea>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                    <input type="checkbox" name="notify_customer" checked>
                    Notify customer via email
                </label>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="status-submit-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal() {
    document.getElementById('status-modal').classList.add('active');
}

function closeStatusModal() {
    document.getElementById('status-modal').classList.remove('active');
}

function handleStatusChange(val) {
    const tracking = document.getElementById('tracking-field');
    const comment = document.getElementById('status-comment');
    
    if (val === 'shipped') {
        tracking.style.display = 'block';
    } else {
        tracking.style.display = 'none';
        tracking.querySelector('input').value = '';
    }
    
    if (val === 'cancelled' || val === 'refunded') {
        comment.required = true;
    } else {
        comment.required = false;
    }
}

document.getElementById('status-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('status-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    
    fetch('<?= APP_URL ?>/api/orders.php?action=update_status', {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Toast.show(data.message, 'success');
            closeStatusModal();
            
            if (data.html) {
                const timeline = document.getElementById('timeline');
                if (timeline && timeline.children.length > 1) {
                    // Update all previous dots to muted
                    const allDots = timeline.querySelectorAll('.new-dot');
                    allDots.forEach(d => d.style.background = 'var(--text-muted)');
                    
                    timeline.children[1].insertAdjacentHTML('beforebegin', data.html);
                }
                const badge = document.getElementById('detail-header-status');
                if(badge) {
                    badge.className = 'badge badge-' + data.new_status;
                    badge.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);
                }
            }
        } else {
            Toast.show(data.error, 'error');
            btn.disabled = false;
            btn.textContent = 'Save Changes';
        }
    })
    .catch(() => {
        Toast.show('An error occurred', 'error');
        btn.disabled = false;
        btn.textContent = 'Save Changes';
    });
});
</script>
