<?php
$pageTitle  = 'Policies';
$activePage = 'policies';
require __DIR__ . '/../partials/header.php';

$currentStatus = $_GET['status'] ?? '';
$currentSearch = $_GET['search'] ?? '';
?>

<div class="page-header animate-in">
  <div class="page-header-text">
    <h2>Insurance Policies</h2>
    <p><?= count($policies) ?> record<?= count($policies) !== 1 ? 's' : '' ?> found</p>
  </div>
  <?php if ($currentUser->canManagePolicies()): ?>
    <a href="<?= APP_URL ?>/index.php?page=policies&action=create" class="btn btn-primary">
      ➕ Add Policy
    </a>
  <?php endif; ?>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" data-auto-close>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>

<div class="d-flex gap-1 mb-2 animate-in animate-in-delay-1" style="flex-wrap:wrap">
  <?php
    $tabs = [''=>'All', 'active'=>'Active', 'pending_renewal'=>'Pending Renewal', 'expired'=>'Expired'];
    foreach ($tabs as $val => $label):
  ?>
    <button class="btn <?= $currentStatus === $val ? 'btn-primary' : 'btn-secondary' ?> btn-sm"
            data-filter="<?= $val ?>">
      <?= $label ?>
    </button>
  <?php endforeach; ?>
</div>

<div class="search-bar animate-in animate-in-delay-2">
  <div class="search-input-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" class="form-control" id="searchInput"
           placeholder="Search by policy number or client name…"
           value="<?= htmlspecialchars($currentSearch) ?>" />
  </div>
</div>

<div class="card animate-in animate-in-delay-3">
  <?php if (empty($policies)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <p>No policies found<?= $currentSearch ? ' matching "' . htmlspecialchars($currentSearch) . '"' : '' ?>.</p>
      <?php if ($currentUser->canManagePolicies()): ?>
        <a href="<?= APP_URL ?>/index.php?page=policies&action=create"
           class="btn btn-primary" style="margin-top:1rem">Add First Policy</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Policy #</th>
            <th>Client</th>
            <th>Type</th>
            <th>Premium</th>
            <th>Start Date</th>
            <th>Renewal Date</th>
            <th>Days Left</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($policies as $p): ?>
            <?php
              $days = $p->getDaysUntilRenewal();
              $daysClass = $days < 0 ? 'past' : ($days <= 7 ? 'urgent' : ($days <= 14 ? 'warning' : 'normal'));
            ?>
            <tr>
              <td class="td-bold"><?= htmlspecialchars($p->policyNumber) ?></td>
              <td><?= htmlspecialchars($p->clientName) ?></td>
              <td><?= htmlspecialchars($p->insuranceType) ?></td>
              <td class="mono">$<?= number_format($p->premiumAmount, 2) ?></td>
              <td><?= date('d M Y', strtotime($p->startDate)) ?></td>
              <td><?= date('d M Y', strtotime($p->renewalDate)) ?></td>
              <td>
                <span class="renewal-days <?= $daysClass ?>">
                  <?= $days >= 0 ? '⏱ ' . $days . 'd' : '⚠ Overdue' ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $p->getStatusBadgeClass() ?>"><?= $p->getStatusLabel() ?></span>
              </td>
              <td>
                <div class="td-actions">
                  <a href="<?= APP_URL ?>/index.php?page=policies&action=view&id=<?= $p->id ?>"
                     class="btn btn-secondary btn-sm">View</a>
                  <?php if ($currentUser->canManagePolicies()): ?>
                    <a href="<?= APP_URL ?>/index.php?page=policies&action=edit&id=<?= $p->id ?>"
                       class="btn btn-secondary btn-sm">Edit</a>
                    <a href="<?= APP_URL ?>/index.php?page=policies&action=delete&id=<?= $p->id ?>"
                       class="btn btn-danger btn-sm"
                       data-confirm="Delete policy <?= htmlspecialchars($p->policyNumber) ?>? This cannot be undone.">
                      Del
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
