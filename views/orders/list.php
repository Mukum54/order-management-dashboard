<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="main-content">
    <?php require __DIR__ . '/../layout/topbar.php'; ?>
    
    <div class="content-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2>Orders Management</h2>
            <?php if(in_array($role, ['admin', 'manager'])): ?>
                <button class="btn btn-secondary" onclick="exportCsv()">
                    <i data-lucide="download" size="16"></i> Export CSV
                </button>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <form id="filter-form" style="display: flex; gap: 16px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search</label>
                    <div style="position: relative;">
                        <i data-lucide="search" size="16" style="position: absolute; left: 10px; top: 11px; color: var(--text-muted);"></i>
                        <input type="text" name="search" class="form-control" style="padding-left: 36px;" placeholder="Order ID, Customer...">
                    </div>
                </div>
                
                <div class="form-group" style="width: 150px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <?php if($role === 'admin'): ?>
                        <option value="refunded">Refunded</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group" style="width: 150px;">
                    <label class="form-label">Date Range</label>
                    <select name="date_range" class="form-control">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>

                <?php if(in_array($role, ['admin', 'manager'])): ?>
                <div class="form-group" style="width: 150px;">
                    <label class="form-label">Assigned To</label>
                    <select name="assigned_to" class="form-control">
                        <option value="">All Staff</option>
                        <option value="unassigned">Unassigned</option>
                        <?php foreach($staffMembers as $u): if(in_array($u->role, ['staff','manager','admin'])): ?>
                            <option value="<?= $u->id ?>"><?= htmlspecialchars($u->name, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-group" style="width: 150px;">
                    <label class="form-label">Payment</label>
                    <select name="payment_status" class="form-control">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="card table-responsive" style="position: relative;">
            <div id="bulk-actions" style="display: none; padding: 12px; background: var(--bg-secondary); border-bottom: 1px solid var(--border-color); border-radius: 8px 8px 0 0; align-items: center; gap: 12px;">
                <span id="bulk-count" style="font-weight: 600; font-size: 14px;">0 selected</span>
                <select id="bulk-status" class="form-control" style="width: 150px; padding: 6px;">
                    <option value="">Set status to...</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button class="btn btn-primary" id="apply-bulk" style="padding: 6px 12px; font-size: 13px;">Apply</button>
            </div>
            <table class="table" id="orders-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="select-all"></th>
                        <th style="cursor: pointer;" onclick="toggleSort('order_number')">Order #</th>
                        <th style="cursor: pointer;" onclick="toggleSort('customer_name')">Customer</th>
                        <th style="cursor: pointer;" onclick="toggleSort('created_at')">Date</th>
                        <th style="cursor: pointer;" onclick="toggleSort('total')">Total</th>
                        <th style="cursor: pointer;" onclick="toggleSort('status')">Status</th>
                        <?php if(in_array($role, ['admin', 'manager'])): ?>
                        <th>Assigned To</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr><td colspan="8" style="text-align:center;">Loading orders...</td></tr>
                </tbody>
            </table>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                <div style="color: var(--text-secondary); font-size: 13px;" id="pagination-info">
                    Showing 0 orders
                </div>
                <div style="display: flex; gap: 8px;">
                    <select id="per-page" class="form-control" style="width: 80px; padding: 6px;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <button class="btn btn-secondary" id="prev-page" disabled>Prev</button>
                    <button class="btn btn-secondary" id="next-page" disabled>Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const userRole = '<?= $role ?>';
    
    let currentSort = 'created_at';
    let currentDir = 'DESC';
    
    function toggleSort(col) {
        if (currentSort === col) {
            currentDir = currentDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = col;
            currentDir = 'ASC';
        }
        if (typeof window.fetchOrders === 'function') window.fetchOrders();
    }

    function exportCsv() {
        const checked = Array.from(document.querySelectorAll('.order-cb:checked')).map(cb => cb.value);
        let url = '<?= APP_URL ?>/api/orders.php?action=export_csv';
        if (checked.length > 0) {
            url += '&ids=' + checked.join(',');
        } else {
            const formData = new FormData(document.getElementById('filter-form'));
            const params = new URLSearchParams(formData);
            url += '&' + params.toString();
        }
        window.location.href = url;
    }
</script>
<script src="<?= APP_URL ?>/public/js/orders.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
