<?php /** @var array $stats, $auditLogs, $chartData, $topAccounts, $roleStats */ ?>
<?php $fraudPending = (int)($stats['fraud_pending'] ?? 0); ?>

<!-- Fraud Banner -->
<?php if ($fraudPending > 0): ?>
<div class="fraud-banner fade-up" id="fraud-banner">
  <div class="fraud-banner-left">
    <span>⚠️</span>
    <div>
      <div class="fraud-banner-title"><?= $fraudPending ?> Fraud Flag<?= $fraudPending > 1 ? 's' : '' ?> Require Review</div>
      <div class="fraud-banner-sub">Suspicious transactions have been detected and flagged by the fraud monitoring system.</div>
    </div>
  </div>
  <a href="/banking-system/public/admin/fraud" class="fraud-banner-link">View Details →</a>
  <button class="fraud-banner-dismiss" onclick="this.closest('.fraud-banner').remove()" aria-label="Dismiss">×</button>
</div>
<?php endif; ?>

<!-- KPI Stats -->
<div class="stat-grid fade-up">
  <div class="stat-card blue">
    <div class="stat-num"><?= (int)($stats['total_users'] ?? 0) ?></div>
    <div class="stat-label">Total Users</div>
    <div class="stat-sub">Active: <?= $stats['active_users'] ?? 0 ?> | New this week: <?= $stats['new_users_week'] ?? 0 ?></div>
  </div>
  <div class="stat-card green">
    <div class="stat-num"><?= (int)($stats['total_accounts'] ?? 0) ?></div>
    <div class="stat-label">Accounts</div>
    <div class="stat-sub">Active: <?= $stats['active_accounts'] ?? 0 ?> | Total deposits: ₹<?= number_format((float)($stats['total_deposits'] ?? 0), 0) ?></div>
  </div>
  <div class="stat-card purple">
    <div class="stat-num"><?= (int)($stats['today_txns'] ?? 0) ?></div>
    <div class="stat-label">Today's Transactions</div>
    <div class="stat-sub">Pending: <?= $stats['pending_txns'] ?? 0 ?> | Failures (24h): <?= $stats['failures_24h'] ?? 0 ?></div>
  </div>
  <div class="stat-card amber">
    <div class="stat-num">₹<?= number_format((float)($stats['today_transfer_volume'] ?? 0), 0) ?></div>
    <div class="stat-label">Today's Volume</div>
    <div class="stat-sub">Open tickets: <?= $stats['open_tickets'] ?? 0 ?> | Fraud pending: <?= $fraudPending ?></div>
  </div>
</div>

<!-- Bottom Grid -->
<div class="bottom-grid fade-up">

  <!-- 7-Day Chart -->
  <div class="card-box">
    <div class="card-box-title">📊 7-Day Transaction Activity</div>
    <div class="card-box-sub">Completed transactions over the past week</div>
    <div class="tab-row">
      <button class="tab-btn" onclick="filterChart(this,'deposit')">Deposits</button>
      <button class="tab-btn" onclick="filterChart(this,'withdrawal')">Withdrawals</button>
      <button class="tab-btn active" onclick="filterChart(this,'transfer')">Transfers</button>
    </div>
    <?php
    // Check if any chart data exists
    $hasData = !empty($chartData);
    ?>
    <?php if ($hasData): ?>
    <div class="chart-area"><canvas id="txnChart" aria-label="7-day transaction activity"></canvas></div>
    <?php else: ?>
    <div class="chart-placeholder" id="chart-placeholder">No transfer data in the last 7 days</div>
    <?php endif; ?>
  </div>

  <!-- Role Distribution -->
  <div class="card-box">
    <div class="card-box-title">👥 User Roles</div>
    <div class="card-box-sub">Distribution by role</div>
    <?php
    $dotColors = ['administrator' => '#c9a84c', 'customer' => '#639922'];
    foreach (($roleStats ?? []) as $rs):
      $col = $dotColors[$rs['name']] ?? '#8a94a6';
    ?>
    <div class="role-row">
      <span class="role-dot" style="background:<?= $col ?>" aria-hidden="true"></span>
      <span class="role-name"><?= ucfirst($rs['name']) ?></span>
      <span class="role-count"><?= (int)$rs['count'] ?></span>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:1rem;border-top:.5px solid var(--color-border-tertiary);padding-top:.75rem">
      <div class="card-box-title" style="margin-bottom:.5rem">🏆 Top Accounts</div>
      <?php foreach (array_slice($topAccounts ?? [], 0, 3) as $acc): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;font-size:12px;border-bottom:.5px solid var(--color-border-tertiary)">
        <div>
          <div style="font-weight:600;color:var(--color-text-primary)"><?= htmlspecialchars($acc['full_name']) ?></div>
          <div style="font-size:11px;color:var(--color-text-muted);font-family:var(--font-mono)"><?= htmlspecialchars($acc['account_number']) ?></div>
        </div>
        <div style="font-weight:600;color:#639922;font-size:12px">₹<?= number_format((float)$acc['balance'], 2) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Recent Activity -->
<div class="card-box fade-up" style="margin-top:16px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
    <div class="card-box-title">⚡ Recent Audit Activity</div>
    <a href="/banking-system/public/admin/audit" class="btn-pill" style="font-size:11px">View All →</a>
  </div>
  <?php foreach (array_slice($auditLogs ?? [], 0, 5) as $log):
    $init = strtoupper(substr($log['username'] ?? 'S', 0, 1));
    $ok   = ($log['outcome'] ?? '') === 'success';
  ?>
  <div class="feed-item">
    <div class="feed-av" aria-hidden="true"><?= $init ?></div>
    <div style="flex:1;min-width:0">
      <div class="feed-name"><?= htmlspecialchars($log['username'] ?? 'System') ?></div>
      <div class="feed-desc" style="font-family:var(--font-mono)"><?= htmlspecialchars($log['action'] ?? '') ?></div>
      <div class="feed-time"><?= date('d M Y H:i', strtotime($log['created_at'] ?? 'now')) ?></div>
    </div>
    <span class="<?= $ok ? 'badge-success' : 'badge-failure' ?>"><?= $ok ? 'success' : 'failure' ?></span>
  </div>
  <?php endforeach; ?>
  <?php if (empty($auditLogs)): ?>
  <div style="padding:1rem;text-align:center;font-size:12px;color:var(--color-text-muted)">No recent activity</div>
  <?php endif; ?>
</div>

<script>
const rawData = <?= json_encode($chartData ?? []) ?>;
let activeChart = null;
let activeType  = 'transfer';

function buildLabels() {
    const labels = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        labels.push(d.toISOString().split('T')[0]);
    }
    return labels;
}

function buildDataset(type) {
    const labels = buildLabels();
    return labels.map(day => {
        const r = rawData.find(x => x.day === day && x.type === type);
        return r ? parseFloat(r.vol) : 0;
    });
}

const chartColors = { transfer: '#c9a84c', deposit: '#639922', withdrawal: '#C0392B' };

const canvas = document.getElementById('txnChart');
if (canvas) {
    const ctx = canvas.getContext('2d');
    activeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: buildLabels().map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short'}); }),
            datasets: [{
                label: 'Transfers',
                data: buildDataset('transfer'),
                borderColor: '#c9a84c',
                backgroundColor: 'rgba(201,168,76,.08)',
                fill: true,
                tension: .4,
                pointBackgroundColor: '#c9a84c',
                pointRadius: 3,
                borderWidth: 1.5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { grid: { color: 'rgba(0,0,0,.04)', borderDash: [3,3] }, ticks: { color: '#8a94a6', font: { size: 10, family: "'JetBrains Mono'" } } },
                y: { grid: { color: 'rgba(0,0,0,.04)', borderDash: [3,3] }, ticks: { color: '#8a94a6', font: { size: 10 }, callback: v => '₹'+v.toLocaleString('en-IN') }, beginAtZero: true }
            },
            plugins: { legend: { display: false } }
        }
    });
}

function filterChart(btn, type) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeType = type;

    const placeholder = document.getElementById('chart-placeholder');
    const data = buildDataset(type);
    const hasAny = data.some(v => v > 0);

    if (placeholder) {
        placeholder.textContent = 'No ' + type + ' data in the last 7 days';
        return;
    }
    if (!activeChart) return;

    activeChart.data.datasets[0].data   = data;
    activeChart.data.datasets[0].label  = type.charAt(0).toUpperCase() + type.slice(1) + 's';
    activeChart.data.datasets[0].borderColor     = chartColors[type] || '#c9a84c';
    activeChart.data.datasets[0].backgroundColor = (chartColors[type] || '#c9a84c') + '14';
    activeChart.update();
}
</script>
