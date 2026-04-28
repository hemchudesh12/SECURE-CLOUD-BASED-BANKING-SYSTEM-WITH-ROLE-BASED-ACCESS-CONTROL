<?php /** @var array $account */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Spending Analytics</h1>
    <p class="page-subtitle">Visual overview of your finances over the last 30 days</p>
  </div>
</div>

<div class="row g-4">
  <!-- Spending by Category -->
  <div class="col-12 col-lg-5">
    <div class="card h-100">
      <div class="card-header"><div class="card-title"><i class="bi bi-pie-chart me-2"></i>Spending by Type</div></div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="spendingChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Balance History -->
  <div class="col-12 col-lg-7">
    <div class="card h-100">
      <div class="card-header"><div class="card-title"><i class="bi bi-graph-up me-2"></i>Balance History (30 Days)</div></div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="balanceChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Current Balance stat -->
<div class="row g-3 mt-2">
  <div class="col-12 col-md-4">
    <div class="kpi-card card kpi-success">
      <div class="kpi-value text-success" id="stat-balance">Loading…</div>
      <div class="kpi-label">Current Balance</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="kpi-card card kpi-danger">
      <div class="kpi-value text-danger" id="stat-debit">—</div>
      <div class="kpi-label">Total Debits (30d)</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="kpi-card card kpi-info">
      <div class="kpi-value text-primary" id="stat-count">—</div>
      <div class="kpi-label">Total Transactions (30d)</div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const COLORS = ['#c9a84c','#1a2744','#198754','#dc3545','#6f42c1','#0dcaf0','#fd7e14'];

    fetch('/banking-system/public/customer/analytics/data')
        .then(r => r.json())
        .then(data => {
            // Spending doughnut
            new Chart(document.getElementById('spendingChart'), {
                type: 'doughnut',
                data: {
                    labels: data.categories,
                    datasets: [{
                        data: data.amounts,
                        backgroundColor: COLORS,
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 12 } } } }
                }
            });

            // Balance history line
            new Chart(document.getElementById('balanceChart'), {
                type: 'line',
                data: {
                    labels: data.dates,
                    datasets: [{
                        label: 'Balance (₹)',
                        data: data.balances,
                        borderColor: '#c9a84c',
                        backgroundColor: 'rgba(201,168,76,0.12)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#c9a84c',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: false,
                            ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });

            const fmt = v => '₹' + parseFloat(v).toLocaleString('en-IN', {minimumFractionDigits:2});
            document.getElementById('stat-balance').textContent = fmt(data.current_balance || 0);

            // Compute total debit
            const totalDebit = data.amounts.reduce((s,v) => s + parseFloat(v), 0);
            document.getElementById('stat-debit').textContent = fmt(totalDebit);
            document.getElementById('stat-count').textContent = data.amounts.length + ' types';
        })
        .catch(e => console.error('[Analytics]', e));
});
</script>
