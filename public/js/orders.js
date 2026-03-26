let currentPage = 1;
let debounceTimer;

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('orders-tbody');
    if (!tableBody) return; // not on orders list page

    const filterForm = document.getElementById('filter-form');
    const perPageSelect = document.getElementById('per-page');
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');

    function fetchOrders() {
        const urlParams = new URLSearchParams(new FormData(filterForm));
        urlParams.append('page', currentPage);
        urlParams.append('limit', perPageSelect.value);
        if (typeof currentSort !== 'undefined') {
            urlParams.append('sort', currentSort);
            urlParams.append('dir', currentDir);
        }

        tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Loading...</td></tr>';

        fetch(APP_URL + '/api/orders.php?action=list&' + urlParams.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.orders);
                    updatePagination(data.total, data.pages);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="8" style="color:red; text-align:center;">${data.error}</td></tr>`;
                }
            });
    }

    function renderTable(orders) {
        if (orders.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No orders found.</td></tr>';
            return;
        }

        let html = '';
        orders.forEach(o => {
            html += `
                <tr style="cursor: pointer;" onclick="window.location.href='${APP_URL}/orders/${o.id}'">
                    <td onclick="e.stopPropagation();"><input type="checkbox" class="order-cb" value="${o.id}"></td>
                    <td data-label="Order #"><strong>${o.order_number}</strong></td>
                    <td data-label="Customer">${o.customer_name}</td>
                    <td data-label="Date">${o.created_at.split(' ')[0]}</td>
                    <td data-label="Total">$${parseFloat(o.total).toFixed(2)}</td>
                    <td data-label="Status"><span class="badge badge-${o.status}">${capitalize(o.status)}</span></td>
                    ${userRole === 'admin' || userRole === 'manager' ? `<td data-label="Assigned"><span style="font-size:12px; color:var(--text-secondary);">${o.assigned_to ? 'Assigned' : 'Unassigned'}</span></td>` : ''}
                    <td data-label="Actions">
                        <button class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" onclick="event.stopPropagation(); window.location.href='${APP_URL}/orders/${o.id}'">View</button>
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;
        lucide.createIcons();
    }

    function updatePagination(total, pages) {
        document.getElementById('pagination-info').textContent = `Showing page ${currentPage} of ${pages} (${total} orders total)`;
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= pages;
    }

    function capitalize(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    // Listeners
    if (filterForm) {
        filterForm.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                fetchOrders();
            }, 300);
        });
        filterForm.addEventListener('change', () => {
            currentPage = 1;
            fetchOrders();
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
            currentPage = 1;
            fetchOrders();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) { currentPage--; fetchOrders(); }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentPage++; fetchOrders();
        });
    }

    // Bulk Actions Setup
    const selectAllCb = document.getElementById('select-all');
    const bulkActionsDiv = document.getElementById('bulk-actions');
    const bulkCountSpan = document.getElementById('bulk-count');
    const applyBulkBtn = document.getElementById('apply-bulk');
    const bulkStatusSelect = document.getElementById('bulk-status');

    function updateBulkActions() {
        if (!bulkActionsDiv) return;
        const checked = document.querySelectorAll('.order-cb:checked');
        if (checked.length > 0) {
            bulkActionsDiv.style.display = 'flex';
            bulkCountSpan.textContent = checked.length + ' selected';
        } else {
            bulkActionsDiv.style.display = 'none';
        }
        if (selectAllCb) {
            selectAllCb.checked = checked.length > 0 && checked.length === document.querySelectorAll('.order-cb').length;
        }
    }

    if (selectAllCb) {
        selectAllCb.addEventListener('change', (e) => {
            const cbs = document.querySelectorAll('.order-cb');
            cbs.forEach(cb => cb.checked = e.target.checked);
            updateBulkActions();
        });
    }

    document.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('order-cb')) {
            updateBulkActions();
        }
    });

    if (applyBulkBtn) {
        applyBulkBtn.addEventListener('click', () => {
            const status = bulkStatusSelect.value;
            if (!status) { Toast.show('Please select a status first.', 'warning'); return; }

            const checked = Array.from(document.querySelectorAll('.order-cb:checked')).map(cb => cb.value);
            if (checked.length === 0) return;

            applyBulkBtn.disabled = true;
            applyBulkBtn.textContent = 'Wait...';

            fetch(APP_URL + '/api/orders.php?action=bulk_update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    // The layout header usually sets a meta tag for csrf, assuming it exists
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: `status=${status}&order_ids=${JSON.stringify(checked)}&csrf_token=${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Toast.show(`Successfully updated ${data.updated} orders to ${status}`, 'success');
                        if (selectAllCb) selectAllCb.checked = false;
                        fetchOrders();
                        updateBulkActions();
                    } else {
                        Toast.show(data.error || 'Bulk update failed.', 'error');
                    }
                })
                .finally(() => {
                    applyBulkBtn.disabled = false;
                    applyBulkBtn.textContent = 'Apply';
                });
        });
    }

    // Initial fetch
    fetchOrders();
});
