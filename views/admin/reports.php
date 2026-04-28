<?php
/**
 * Admin Reports — 30-day transaction analytics.
 * Variables: $dailyVolume (from AdminController::reports())
 */

// Aggregate by day across all types
$dayMap = [];
foreach ($dailyVolume as $row) {
    $day = $row['day'];
    if (!isset($dayMap[$day])) {
        $dayMap[$day] = ['deposit' => 0, 'withdrawal' => 0, 'transfer' => 0, 'total' => 0, 'count' => 0];
    }
    $dayMap[$day][$row['type']] = (float)$row['vol'];
    $dayMap[$day]['total']     += (float)$row['vol'];
    $dayMap[$day]['count']     += (int)$row['count'];
}
krsort($dayMap); // newest first for table

// Totals
$grandTotal      = array_sum(array_column($dailyVolume, 'vol'));
$totalCount      = array_sum(array_column($dailyVolume, 'count'));
$totalDeposits   = array_sum(array_map(fn($r) => $r['type']==='deposit'    ? (float)$r['vol'] : 0, $dailyVolume));
$totalWithdrawals= array_sum(array_map(fn($r) => $r['type']==='withdrawal' ? (float)$r['vol'] : 0, $dailyVolume));
$totalTransfers  = array_sum(array_map(fn($r) => $r['type']==='transfer'   ? (float)$r['vol'] : 0, $dailyVolume));

// Chart data — last 30 days ordered ascending
ksort($dayMap);
$chartLabels = [];
$chartDeposit= [];
$chartWithdraw=[];
$chartTransfer=[];
foreach ($dayMap as $day => $vals) {
    $chartLabels[]  = date('d M', strtotime($day));
    $chartDeposit[] = $vals['deposit'];
    $chartWithdraw[]= $vals['withdrawal'];
    $chartTransfer[]= $vals['transfer'];
}
krsort($dayMap); // back to newest-first for table
?>

<!-- ── Page Header ───────────────────────────────────────────── -->
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <h1 class="mb-1"><i class="bi bi-bar-chart-line me-2 text-gold"></i>Banking Reports</h1>
    <p class="text-muted mb-0">30-day transaction analytics &nbsp;·&nbsp; Generated <?= date('d M Y, H:i') ?></p>
  </div>
  <a href="/banking-system/public/admin/audit/export" class="btn btn-navy btn-sm">
    <i class="bi bi-download me-1"></i>Export Audit CSV
  </a>
</div>

<!-- ── Summary KPI Cards ─────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-navy"><i class="bi bi-hash"></i></div>
        <div>
          <div class="stat-value"><?= number_format($totalCount) ?></div>
          <div class="stat-label">Total Transactions</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card fade-in-up" style="animation-delay:.05s">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-green"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
          <div class="stat-value" style="font-size:1.3rem">₹<?= number_format($totalDeposits/1000, 1) ?>K</div>
          <div class="stat-label">Total Deposits</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card fade-in-up" style="animation-delay:.1s">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-red"><i class="bi bi-arrow-up-circle"></i></div>
        <div>
          <div class="stat-value" style="font-size:1.3rem">₹<?= number_format($totalWithdrawals/1000, 1) ?>K</div>
          <div class="stat-label">Total Withdrawals</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card fade-in-up" style="animation-delay:.15s">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-gold"><i class="bi bi-arrow-left-right"></i></div>
        <div>
          <div class="stat-value" style="font-size:1.3rem">₹<?= number_format($totalTransfers/1000, 1) ?>K</div>
          <div class="stat-label">Total Transfers</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Line Chart ────────────────────────────────────────────── -->
<div class="card fade-in-up mb-4" style="animation-delay:.2s">
  <div class="card-header d-flex align-items-center">
    <i class="bi bi-graph-up me-2 text-gold"></i>
    <span class="fw-600">Daily Volume Trend (Last 30 Days)</span>
    <div class="ms-auto d-flex gap-2" style="font-size:0.75rem">
      <span class="badge" style="background:rgba(59,130,246,.15);color:#3b82f6">
        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem"></i>Deposits
      </span>
      <span class="badge" style="background:rgba(239,68,68,.15);color:#ef4444">
        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem"></i>Withdrawals
      </span>
      <span class="badge" style="background:rgba(202,155,51,.15);color:var(--gold)">
        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem"></i>Transfers
      </span>
    </div>
  </div>
  <div class="card-body" style="height:320px;position:relative">
    <?php if (empty($dayMap)): ?>
      <div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column gap-2">
        <i class="bi bi-bar-chart fs-1"></i>
        <p class="mb-0">No transaction data for the last 30 days.</p>
      </div>
    <?php else: ?>
    <canvas id="reportChart"></canvas>
    <?php endif; ?>
  </div>
</div>

<!-- ── Stacked Bar for Comparison ───────────────────────────── -->
<?php if (!empty($dayMap)): ?>
<div class="card fade-in-up mb-4" style="animation-delay:.25s">
  <div class="card-header">
    <i class="bi bi-bar-chart-steps me-2 text-gold"></i>
    <span class="fw-600">Daily Transaction Mix</span>
  </div>
  <div class="card-body" style="height:260px;position:relative">
    <canvas id="stackedChart"></canvas>
  </div>
</div>
<?php endif; ?>

<!-- ── Data Table ────────────────────────────────────────────── -->
<div class="card fade-in-up" style="animation-delay:.3s">
  <div class="card-header d-flex align-items-center">
    <i class="bi bi-table me-2 text-gold"></i>
    <span class="fw-600">Daily Breakdown</span>
    <span class="ms-2 badge" style="background:var(--gold);color:var(--navy)"><?= count($dayMap) ?> days</span>
    <span class="ms-auto fw-700 text-gold" style="font-size:0.9rem">
      Grand Total: ₹<?= number_format($grandTotal, 2) ?>
    </span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($dayMap)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
        No transaction data available for this period.
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th class="text-end" style="color:#3b82f6">Deposits</th>
            <th class="text-end" style="color:#ef4444">Withdrawals</th>
            <th class="text-end" style="color:var(--gold)">Transfers</th>
            <th class="text-end">Day Total</th>
            <th class="text-center">Txn Count</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($dayMap as $day => $vals): ?>
          <tr>
            <td>
              <span class="fw-600"><?= date('d M Y', strtotime($day)) ?></span>
              <small class="text-muted ms-1"><?= date('D', strtotime($day)) ?></small>
            </td>
            <td class="text-end" style="color:#3b82f6">
              <?= $vals['deposit'] > 0 ? '₹'.number_format($vals['deposit'], 2) : '<span class="text-muted">—</span>' ?>
            </td>
            <td class="text-end" style="color:#ef4444">
              <?= $vals['withdrawal'] > 0 ? '₹'.number_format($vals['withdrawal'], 2) : '<span class="text-muted">—</span>' ?>
            </td>
            <td class="text-end" style="color:var(--gold)">
              <?= $vals['transfer'] > 0 ? '₹'.number_format($vals['transfer'], 2) : '<span class="text-muted">—</span>' ?>
            </td>
            <td class="text-end fw-700">₹<?= number_format($vals['total'], 2) ?></td>
            <td class="text-center">
              <span class="badge status-pending"><?= $vals['count'] ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background:var(--navy);color:var(--gold);font-weight:700">
            <td>30-Day Total</td>
            <td class="text-end">₹<?= number_format($totalDeposits, 2) ?></td>
            <td class="text-end">₹<?= number_format($totalWithdrawals, 2) ?></td>
            <td class="text-end">₹<?= number_format($totalTransfers, 2) ?></td>
            <td class="text-end">₹<?= number_format($grandTotal, 2) ?></td>
            <td class="text-center"><?= number_format($totalCount) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($dayMap)): ?>
<!-- ── Chart.js ──────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels   = <?= json_encode(array_values($chartLabels)) ?>;
const deposits = <?= json_encode(array_values($chartDeposit)) ?>;
const withdraws= <?= json_encode(array_values($chartWithdraw)) ?>;
const transfers= <?= json_encode(array_values($chartTransfer)) ?>;

const gold  = '#ca9b33';
const blue  = '#3b82f6';
const red   = '#ef4444';
const green = '#10b981';

// ── Line Chart ───────────────────────────────────────────────
new Chart(document.getElementById('reportChart').getContext('2d'), {
  type: 'line',
  data: {
    labels,
    datasets: [
      {
        label: 'Deposits',
        data: deposits,
        borderColor: blue,
        backgroundColor: 'rgba(59,130,246,0.08)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: blue,
        pointRadius: 3,
        borderWidth: 2,
      },
      {
        label: 'Withdrawals',
        data: withdraws,
        borderColor: red,
        backgroundColor: 'rgba(239,68,68,0.06)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: red,
        pointRadius: 3,
        borderWidth: 2,
      },
      {
        label: 'Transfers',
        data: transfers,
        borderColor: gold,
        backgroundColor: 'rgba(202,155,51,0.06)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: gold,
        pointRadius: 3,
        borderWidth: 2,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN', {maximumFractionDigits:2})}`
        }
      }
    },
    scales: {
      x: {
        grid: { color: 'rgba(0,0,0,0.04)' },
        ticks: { color: '#9ca3af', font: { size: 11 }, maxTicksLimit: 15 },
      },
      y: {
        grid: { color: 'rgba(0,0,0,0.04)' },
        ticks: {
          color: '#9ca3af',
          font: { size: 11 },
          callback: v => '₹' + (v >= 100000 ? (v/100000).toFixed(1)+'L' : v >= 1000 ? (v/1000).toFixed(1)+'K' : v),
        },
        beginAtZero: true,
      }
    }
  }
});

// ── Stacked Bar ──────────────────────────────────────────────
new Chart(document.getElementById('stackedChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      { label: 'Deposits',    data: deposits, backgroundColor: 'rgba(59,130,246,0.75)', borderRadius: 2 },
      { label: 'Withdrawals', data: withdraws,backgroundColor: 'rgba(239,68,68,0.75)',  borderRadius: 2 },
      { label: 'Transfers',   data: transfers,backgroundColor: 'rgba(202,155,51,0.75)', borderRadius: 2 },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN', {maximumFractionDigits:2})}`
        }
      }
    },
    scales: {
      x: {
        stacked: true,
        grid: { color: 'rgba(0,0,0,0.04)' },
        ticks: { color: '#9ca3af', font: { size: 10 }, maxTicksLimit: 15 },
      },
      y: {
        stacked: true,
        grid: { color: 'rgba(0,0,0,0.04)' },
        ticks: {
          color: '#9ca3af', font: { size: 10 },
          callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
        },
        beginAtZero: true,
      }
    }
  }
});
</script>
<?php endif; ?>
