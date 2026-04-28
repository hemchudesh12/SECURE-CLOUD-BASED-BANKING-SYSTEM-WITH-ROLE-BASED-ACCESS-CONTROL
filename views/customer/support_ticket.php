<?php /** @var array $ticket, $replies */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Ticket #<?= $ticket['id'] ?></h1>
    <p class="page-subtitle"><?= htmlspecialchars($ticket['subject']) ?></p>
  </div>
  <a href="javascript:history.back()" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
  <div class="col-12 col-lg-8">
    <!-- Original message -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title"><i class="bi bi-chat-text me-2"></i>Original Request</div>
        <span class="badge bg-<?= ['open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'secondary'][$ticket['status']]??'secondary' ?>">
          <?= ucfirst(str_replace('_',' ',$ticket['status'])) ?>
        </span>
      </div>
      <div class="card-body">
        <p><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
        <div class="text-muted" style="font-size:0.78rem"><?= date('d M Y H:i', strtotime($ticket['created_at'])) ?></div>
      </div>
    </div>

    <!-- Replies -->
    <?php foreach ($replies as $r): ?>
    <?php $isStaff = in_array($r['role_name'], ['administrator']); ?>
    <div class="card mb-3 <?= $isStaff ? 'border-start border-4' : '' ?>" style="<?= $isStaff ? 'border-color:var(--gold)!important' : '' ?>">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-2">
          <strong><?= htmlspecialchars($r['full_name']) ?></strong>
          <span class="badge <?= $isStaff ? 'badge-role-admin' : 'badge-role-customer' ?>"><?= ucfirst($r['role_name']) ?></span>
          <span class="text-muted ms-auto" style="font-size:0.78rem"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></span>
        </div>
        <p class="mb-0"><?= nl2br(htmlspecialchars($r['message'])) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($replies)): ?>
    <div class="text-muted text-center py-3"><i class="bi bi-chat-dots me-2"></i>No replies yet</div>
    <?php endif; ?>

    <!-- Reply form -->
    <?php if (!in_array($ticket['status'], ['resolved','closed'])): ?>
    <div class="card">
      <div class="card-header"><div class="card-title"><i class="bi bi-reply me-2"></i>Post Reply</div></div>
      <div class="card-body">
        <form method="POST" action="/banking-system/public/support/ticket/<?= $ticket['id'] ?>/reply">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <div class="mb-3">
            <textarea class="form-control" name="message" rows="4" required placeholder="Write your reply..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Post Reply</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header"><div class="card-title"><i class="bi bi-info-circle me-2"></i>Ticket Info</div></div>
      <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Priority</span>
          <span class="badge priority-<?= $ticket['priority'] ?>"><?= ucfirst($ticket['priority']) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Status</span>
          <span><?= ucfirst(str_replace('_',' ',$ticket['status'])) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Created</span>
          <span><?= date('d M Y', strtotime($ticket['created_at'])) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Replies</span>
          <span><?= count($replies) ?></span>
        </div>

        <?php if (in_array(Session::get('role'), ['administrator'])): ?>
        <hr>
        <form method="POST" action="/banking-system/public/admin/support/<?= $ticket['id'] ?>/status">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <div class="mb-2">
            <label class="form-label form-label-sm">Update Status</label>
            <select class="form-select form-select-sm" name="status">
              <option value="open" <?= $ticket['status']==='open'?'selected':'' ?>>Open</option>
              <option value="in_progress" <?= $ticket['status']==='in_progress'?'selected':'' ?>>In Progress</option>
              <option value="resolved" <?= $ticket['status']==='resolved'?'selected':'' ?>>Resolved</option>
              <option value="closed" <?= $ticket['status']==='closed'?'selected':'' ?>>Closed</option>
            </select>
          </div>
          <button class="btn btn-sm btn-primary w-100">Update Status</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
