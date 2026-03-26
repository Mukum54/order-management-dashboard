// reports.js
document.addEventListener('DOMContentLoaded', () => {
    let revChart, statusChart, staffChart, prodChart;

    Chart.defaults.color = 'var(--text-secondary)';
    Chart.defaults.font.family = "'Poppins', sans-serif";

    const rangeSelect = document.getElementById('report-date-range');

    function fetchData() {
        const range = rangeSelect ? rangeSelect.value : '30d';

        // Load KPIs
        fetch(`${APP_URL}/api/reports.php?action=kpis&range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('kpi-total').textContent = data.data.total_orders;
                    document.getElementById('kpi-revenue').textContent = '$' + data.data.revenue;
                    document.getElementById('kpi-pending').textContent = data.data.pending_orders;
                    document.getElementById('kpi-aov').textContent = '$' + data.data.aov;
                    document.getElementById('kpi-cancel').textContent = data.data.cancelled_rate + '%';
                    document.getElementById('kpi-refund').textContent = '$' + data.data.refund_total;
                }
            });

        // Load Revenue Chart
        fetch(`${APP_URL}/api/reports.php?action=chart_revenue&range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (revChart) revChart.destroy();
                    const ctx = document.getElementById('revenueChart').getContext('2d');

                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
                    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

                    revChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Revenue ($)',
                                data: data.values,
                                borderColor: '#3B82F6',
                                backgroundColor: gradient,
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3,
                                pointBackgroundColor: '#3B82F6',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    titleFont: { size: 13, family: "'Poppins', sans-serif" },
                                    bodyFont: { size: 14, family: "'Poppins', sans-serif", weight: 'bold' },
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, border: { display: false } },
                                y: { grid: { color: 'var(--border-color)', drawBorder: false }, border: { display: false }, beginAtZero: true }
                            }
                        }
                    });
                }
            });

        // Load Status Chart (Doughnut)
        fetch(`${APP_URL}/api/reports.php?action=chart_status&range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (statusChart) statusChart.destroy();
                    statusChart = new Chart(document.getElementById('statusChart'), {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.values,
                                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#F97316'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: {
                                legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' } },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            }
                        }
                    });
                }
            });

        // Load Staff Chart (Bar)
        fetch(`${APP_URL}/api/reports.php?action=chart_staff&range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (staffChart) staffChart.destroy();
                    staffChart = new Chart(document.getElementById('staffChart'), {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Orders Handled',
                                data: data.values,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                hoverBackgroundColor: '#10B981',
                                borderRadius: 6,
                                barPercentage: 0.5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, border: { display: false } },
                                y: { grid: { color: 'var(--border-color)', drawBorder: false }, border: { display: false }, beginAtZero: true }
                            }
                        }
                    });
                }
            });

        // Load Products Chart (Horizontal Bar)
        fetch(`${APP_URL}/api/reports.php?action=chart_products&range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (prodChart) prodChart.destroy();
                    prodChart = new Chart(document.getElementById('productsChart'), {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Revenue ($)',
                                data: data.values,
                                backgroundColor: 'rgba(139, 92, 246, 0.85)',
                                hoverBackgroundColor: '#8B5CF6',
                                borderRadius: 6,
                                barPercentage: 0.6
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false
                                }
                            },
                            scales: {
                                x: { grid: { color: 'var(--border-color)', drawBorder: false }, border: { display: false }, beginAtZero: true },
                                y: { grid: { display: false }, border: { display: false } }
                            }
                        }
                    });
                }
            });
    }

    if (rangeSelect) {
        rangeSelect.addEventListener('change', fetchData);
        fetchData(); // init
    }
});
