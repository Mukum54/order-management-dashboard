<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-content">
    <?php require __DIR__ . '/../layout/topbar.php'; ?>
    
    <div class="content-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2>Analytics & Reports</h2>
            <div style="display: flex; gap: 12px; align-items: center;">
                <select id="report-date-range" class="form-control" style="width: 200px;">
                    <option value="today">Today</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d" selected>Last 30 Days</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                </select>
                <button class="btn btn-secondary no-print" onclick="exportReportCsv()"><i data-lucide="file-spreadsheet" size="16"></i> Export CSV</button>
                <button class="btn btn-secondary no-print" onclick="window.print()"><i data-lucide="download" size="16"></i> Export PDF</button>
            </div>
        </div>

<script>
function exportReportCsv() {
    const range = document.getElementById('report-date-range').value;
    window.location.href = '<?= APP_URL ?>/api/reports.php?action=export_csv&range=' + range;
}
</script>

<style>
.analytics-kpi-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.analytics-kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
.chart-container {
    position: relative;
    height: 320px;
    width: 100%;
}
</style>

        <!-- KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 24px;">
            <div class="card analytics-kpi-card" style="position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, rgba(59,130,246,0.1) 0%, rgba(59,130,246,0.02) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="color: var(--text-secondary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Orders</div>
                    <div style="color: #3B82F6; background: rgba(59, 130, 246, 0.15); padding: 8px; border-radius: 8px;"><i data-lucide="shopping-cart"></i></div>
                </div>
                <div style="font-size: 32px; font-family: 'Poppins'; font-weight: 700; color: var(--text-primary);" id="kpi-total">0</div>
            </div>
            <div class="card analytics-kpi-card" style="position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(16,185,129,0.02) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="color: var(--text-secondary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Revenue</div>
                    <div style="color: #10B981; background: rgba(16, 185, 129, 0.15); padding: 8px; border-radius: 8px;"><i data-lucide="dollar-sign"></i></div>
                </div>
                <div style="font-size: 32px; font-family: 'Poppins'; font-weight: 700; color: var(--text-primary);" id="kpi-revenue">$0</div>
            </div>
            <div class="card analytics-kpi-card" style="position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(245,158,11,0.02) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="color: var(--text-secondary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pending Orders</div>
                    <div style="color: #F59E0B; background: rgba(245, 158, 11, 0.15); padding: 8px; border-radius: 8px;"><i data-lucide="clock"></i></div>
                </div>
                <div style="font-size: 32px; font-family: 'Poppins'; font-weight: 700; color: var(--text-primary);" id="kpi-pending">0</div>
            </div>
            <div class="card analytics-kpi-card" style="position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, rgba(139,92,246,0.1) 0%, rgba(139,92,246,0.02) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="color: var(--text-secondary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Avg Order Value</div>
                    <div style="color: #8B5CF6; background: rgba(139, 92, 246, 0.15); padding: 8px; border-radius: 8px;"><i data-lucide="bar-chart-2"></i></div>
                </div>
                <div style="font-size: 32px; font-family: 'Poppins'; font-weight: 700; color: var(--text-primary);" id="kpi-aov">$0</div>
            </div>
            <div class="card analytics-kpi-card" style="position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, rgba(239,68,68,0.1) 0%, rgba(239,68,68,0.02) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="color: var(--text-secondary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Cancelled Rate</div>
                    <div style="color: #EF4444; background: rgba(239, 68, 68, 0.15); padding: 8px; border-radius: 8px;"><i data-lucide="x-circle"></i></div>
                </div>
                <div style="font-size: 32px; font-family: 'Poppins'; font-weight: 700; color: var(--text-primary);" id="kpi-cancel">0%</div>
            </div>
            <div class="card analytics-kpi-card" style="position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, rgba(249,115,22,0.1) 0%, rgba(249,115,22,0.02) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="color: var(--text-secondary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Refund Total</div>
                    <div style="color: #F97316; background: rgba(249, 115, 22, 0.15); padding: 8px; border-radius: 8px;"><i data-lucide="refresh-cw"></i></div>
                </div>
                <div style="font-size: 32px; font-family: 'Poppins'; font-weight: 700; color: var(--text-primary);" id="kpi-refund">$0</div>
            </div>
        </div>

        <div class="responsive-grid-2" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
            <!-- Revenue Over Time -->
            <div class="card">
                <h3 style="margin-bottom: 16px;">Revenue Over Time</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <!-- Orders By Status -->
            <div class="card">
                <h3 style="margin-bottom: 16px;">Orders by Status</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="responsive-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Orders By Staff -->
            <div class="card">
                <h3 style="margin-bottom: 16px;">Orders Handled by Staff</h3>
                <div class="chart-container">
                    <canvas id="staffChart"></canvas>
                </div>
            </div>
            <!-- Top Products -->
            <div class="card">
                <h3 style="margin-bottom: 16px;">Top Products by Revenue</h3>
                <div class="chart-container">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="<?= APP_URL ?>/public/js/reports.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
