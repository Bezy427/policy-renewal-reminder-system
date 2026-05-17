<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../partials/header.php';

$total   = (int) ($summary['total']   ?? 0);
$active  = (int) ($summary['active']  ?? 0);
$expired = (int) ($summary['expired'] ?? 0);
$pending = (int) ($summary['pending'] ?? 0);
$nearing = (int) ($summary['nearing'] ?? 0);
?>

<div class="page-header animate-in">
  <div class="page-header-text">
    <h2>Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>, <?= htmlspecialchars(explode(' ', $currentUser->fullName)[0]) ?>.</h2>
    <p>Here's an overview of your policy portfolio — <?= date('l, F j, Y') ?></p>
  </div>
</div>

<div class="stats-grid animate-in animate-in-delay-1">
  <div class="stat-card">
    <div class="stat-icon blue">📋</div>
    <div>
      <div class="stat-label">Total Policies</div>
      <div class="stat-value"><?= $total ?></div>
      <div class="stat-sub">All records in system</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green">✅</div>
    <div>
      <div class="stat-label">Active</div>
      <div class="stat-value text-green"><?= $active ?></div>
      <div class="stat-sub"><?= $total ? round($active/$total*100) : 0 ?>% of total</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon red">❌</div>
    <div>
      <div class="stat-label">Expired</div>
      <div class="stat-value text-red"><?= $expired ?></div>
      <div class="stat-sub">Require attention</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">⏳</div>
    <div>
      <div class="stat-label">Pending Renewal</div>
      <div class="stat-value text-amber"><?= $pending ?></div>
      <div class="stat-sub">Awaiting action</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon gold">🔔</div>
    <div>
      <div class="stat-label">Nearing Renewal</div>
      <div class="stat-value" style="color:var(--gold)"><?= $nearing ?></div>
      <div class="stat-sub">Within <?= RENEWAL_NOTICE_DAYS ?> days</div>
    </div>
  </div>
</div>

<div class="card animate-in animate-in-delay-2">
  <div class="card-header">
    <span>🔔</span>
    <h3>Policies Nearing Renewal <span class="badge badge-pending" style="margin-left:.5rem"><?= count($nearingPolicies) ?></span></h3>
    <a href="<?= APP_URL ?>/index.php?page=policies" class="btn btn-secondary btn-sm" style="margin-left:auto">View All</a>
  </div>

  <?php if (empty($nearingPolicies)): ?>
    <div class="empty-state">
      <div class="empty-icon">🎉</div>
      <p>No policies nearing renewal in the next <?= RENEWAL_NOTICE_DAYS ?> days.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Policy #</th>
            <th>Client</th>
            <th>Type</th>
            <th>Renewal Date</th>
            <th>Days Left</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($nearingPolicies as $p): ?>
            <?php
              $days = $p->getDaysUntilRenewal();
              $urgencyClass = $days <= 7 ? 'urgent' : ($days <= 14 ? 'warning' : 'normal');
            ?>
            <tr>
              <td class="td-bold"><?= htmlspecialchars($p->policyNumber) ?></td>
              <td><?= htmlspecialchars($p->clientName) ?></td>
              <td><?= htmlspecialchars($p->insuranceType) ?></td>
              <td><?= date('d M Y', strtotime($p->renewalDate)) ?></td>
              <td>
                <span class="renewal-days <?= $urgencyClass ?>">
                  ⏱ <?= $days ?>d
                </span>
              </td>
              <td><span class="badge <?= $p->getStatusBadgeClass() ?>"><?= $p->getStatusLabel() ?></span></td>
              <td>
                <a href="<?= APP_URL ?>/index.php?page=policies&action=view&id=<?= $p->id ?>"
                   class="btn btn-secondary btn-sm">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
