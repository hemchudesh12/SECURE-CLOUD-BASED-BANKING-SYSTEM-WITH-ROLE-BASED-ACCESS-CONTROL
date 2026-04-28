<?php /** @var array $flags */ ?>

<div class="page-header" style="margin-bottom:1rem">
  <div class="page-title">Fraud Dashboard</div>
  <div class="page-sub">SecureBank / Fraud — Review flagged transactions</div>
</div>

<?php
$pending  = count(array_filter($flags, fn($f) => $f['review_status'] === 'pending'));
$cleared  = count(array_filter($flags, fn($f) => $f['review_status'] === 'cleared'));
$confirmed = count(array_filter($flags, fn($f) => $f['review_status'] === 'confirmed'));
?>

<!-- Summary pills -->
<div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
  <span class="badge-failure" style="padding:5px 12px;font-size:12px">🚨 <?= $pending ?> Pending</span>
  <span class="badge-success" style="padding:5px 12px;font-size:12px">✓ <?= $cleared ?> Cleared</span>
  <span class="badge-blue" style="padding:5px 12px;font-size:12px">⚡ <?= $confirmed ?> Confirmed</span>
</div>

<div class="tbl-card fade-up">
  <div style="padding:.75rem 1rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:13px;font-weight:600">🚨 All Fraud Flags</div>
  <?php if (empty($flags)): ?>
  <div style="padding:2.5rem;text-align:center;font-size:13px;color:var(--color-text-muted)">✅ No fraud flags found. All transactions look clean.</div>
  <?php else: ?>
  <table class="tbl" aria-label="Fraud flags table">
    <thead>
      <tr><th>Reference</th><th>Amount</th><th>Account Holder</th><th>Rules Triggered</th><th>Risk Score</th><th>Review Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($flags as $f):
      $risk = (int)$f['risk_score'];
      if ($risk >= 71)      { $riskClass = 'badge-failure'; $riskLabel = '🔴 High'; }
      elseif ($risk >= 41)  { $riskClass = 'badge-gold';    $riskLabel = '🟡 Med';  }
      else                  { $riskClass = 'badge-success'; $riskLabel = '🟢 Low';  }

      $reviewColors = ['pending'=>'badge-failure','cleared'=>'badge-success','confirmed'=>'badge-blue'];
      $rc = $reviewColors[$f['review_status']] ?? 'badge-gray';
    ?>
    <tr class="<?= $f['review_status'] === 'pending' ? 'row-failure' : '' ?>">
      <td class="td-mono"><?= htmlspecialchars($f['reference_number']) ?></td>
      <td style="font-weight:600;font-size:13px">₹<?= number_format((float)$f['amount'], 2) ?></td>
      <td style="font-size:13px"><?= htmlspecialchars($f['from_holder'] ?? '—') ?></td>
      <td>
        <?php foreach (explode(',', $f['rule_triggered']) as $rule): ?>
        <span class="badge-gold" style="margin-right:3px;margin-bottom:2px;font-size:10px"><?= htmlspecialchars(trim($rule)) ?></span>
        <?php endforeach; ?>
      </td>
      <td>
        <span class="<?= $riskClass ?>" style="font-size:12px;min-width:60px;display:inline-block;text-align:center" title="Risk score: <?= $risk ?>">
          <?= $riskLabel ?> (<?= $risk ?>)
        </span>
      </td>
      <td><span class="<?= $rc ?>"><?= ucfirst($f['review_status']) ?></span></td>
      <td>
        <?php if ($f['review_status'] === 'pending'): ?>
        <button class="btn-pill gold" style="padding:3px 10px;font-size:11px" onclick="openReview(<?= $f['id'] ?>)">
          🔍 Review
        </button>
        <?php else: ?>
        <span style="font-size:11px;color:var(--color-text-muted)">By <?= htmlspecialchars($f['reviewed_by_name'] ?? '—') ?></span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Review Modal (pure CSS/JS, no Bootstrap) -->
<div id="reviewOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:1rem">
  <div style="background:var(--color-background-primary);border-radius:var(--border-radius-lg);width:100%;max-width:440px;overflow:hidden;border:.5px solid var(--color-border-tertiary)">
    <div style="background:#0f1623;padding:.85rem 1.1rem;display:flex;align-items:center;justify-content:space-between">
      <span style="color:#c9a84c;font-weight:700;font-size:.9rem">🔍 Review Fraud Flag</span>
      <button onclick="closeReview()" style="background:none;border:none;color:rgba(255,255,255,.6);cursor:pointer;font-size:1rem">×</button>
    </div>
    <form method="POST" id="reviewForm" style="padding:1.1rem;display:flex;flex-direction:column;gap:.75rem">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div style="display:flex;flex-direction:column;gap:.3rem">
        <label class="form-label">Decision</label>
        <select name="review_status" class="form-select">
          <option value="cleared">✅ Clear — Legitimate transaction</option>
          <option value="confirmed">🚨 Confirm — Fraudulent transaction</option>
        </select>
      </div>
      <div style="display:flex;flex-direction:column;gap:.3rem">
        <label class="form-label">Review Notes</label>
        <textarea name="notes" class="form-input" rows="3" placeholder="Add review notes…" style="resize:vertical"></textarea>
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;padding-top:.5rem;border-top:.5px solid var(--color-border-tertiary)">
        <button type="button" onclick="closeReview()" class="btn-pill">Cancel</button>
        <button type="submit" class="btn-pill gold">Submit Review</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReview(id) {
    document.getElementById('reviewForm').action = `/banking-system/public/admin/fraud/${id}/review`;
    const overlay = document.getElementById('reviewOverlay');
    overlay.style.display = 'flex';
}
function closeReview() {
    document.getElementById('reviewOverlay').style.display = 'none';
}
document.getElementById('reviewOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeReview();
});
</script>
