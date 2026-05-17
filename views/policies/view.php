<?php
$pageTitle  = 'Policy — ' . $policy->policyNumber;
$activePage = 'policies';
require __DIR__ . '/../partials/header.php';
$days = $policy->getDaysUntilRenewal();
$daysClass = $days < 0 ? 'past' : ($days <= 7 ? 'urgent' : ($days <= 14 ? 'warning' : 'normal'));
?>

<div class="page-header animate-in">
  <div class="page-header-text">
    <h2><?= htmlspecialchars($policy->policyNumber) ?></h2>
    <p><?= htmlspecialchars($policy->clientName) ?> · <?= htmlspecialchars($policy->insuranceType) ?></p>
  </div>
  <div style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/index.php?page=policies" class="btn btn-secondary">← Back</a>
    <?php if ($currentUser->canManagePolicies()): ?>
      <a href="<?= APP_URL ?>/index.php?page=policies&action=edit&id=<?= $policy->id ?>"
         class="btn btn-primary">✏️ Edit</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" data-auto-close>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start" class="animate-in animate-in-delay-1">

  <div>
    <div class="card mb-2">
      <div class="card-header">
        <span>📋</span>
        <h3>Policy Details</h3>
        <span class="badge <?= $policy->getStatusBadgeClass() ?>" style="margin-left:auto">
          <?= $policy->getStatusLabel() ?>
        </span>
      </div>
      <div class="card-body">
        <div class="detail-grid">
          <div class="detail-item">
            <div class="detail-label">Policy Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($policy->policyNumber) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Client Name</div>
            <div class="detail-value"><?= htmlspecialchars($policy->clientName) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Insurance Type</div>
            <div class="detail-value"><?= htmlspecialchars($policy->insuranceType) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Premium Amount</div>
            <div class="detail-value mono">$<?= number_format($policy->premiumAmount, 2) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Start Date</div>
            <div class="detail-value"><?= date('d F Y', strtotime($policy->startDate)) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Renewal Date</div>
            <div class="detail-value"><?= date('d F Y', strtotime($policy->renewalDate)) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Created By</div>
            <div class="detail-value"><?= htmlspecialchars($policy->creatorName ?? '—') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Last Updated</div>
            <div class="detail-value"><?= date('d M Y, H:i', strtotime($policy->updatedAt)) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card animate-in animate-in-delay-2">
      <div class="card-header">
        <span>📎</span>
        <h3>Documents <span class="badge badge-viewer" style="margin-left:.4rem"><?= count($documents) ?></span></h3>
        <?php if ($currentUser->canManagePolicies()): ?>
          <button class="btn btn-primary btn-sm" style="margin-left:auto"
                  onclick="openModal('uploadModal')">+ Upload</button>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (empty($documents)): ?>
          <div class="empty-state" style="padding:1.5rem">
            <div class="empty-icon">📁</div>
            <p>No documents uploaded yet.</p>
          </div>
        <?php else: ?>
          <div class="doc-list">
            <?php foreach ($documents as $doc): ?>
              <div class="doc-item">
                <div class="doc-icon"><?= $doc->getFileIcon() ?></div>
                <div class="doc-info">
                  <div class="doc-name"><?= htmlspecialchars($doc->fileName) ?></div>
                  <div class="doc-meta">
                    <?= $doc->getFileSizeFormatted() ?> ·
                    Uploaded by <?= htmlspecialchars($doc->uploaderName ?? 'Unknown') ?> ·
                    <?= date('d M Y', strtotime($doc->uploadedAt)) ?>
                  </div>
                </div>
                <div class="doc-actions">
                  <a href="<?= APP_URL ?>/index.php?page=documents&action=download&id=<?= $doc->id ?>"
                     class="btn btn-secondary btn-sm">⬇ Download</a>
                  <?php if ($currentUser->canManagePolicies()): ?>
                    <a href="<?= APP_URL ?>/index.php?page=documents&action=delete&id=<?= $doc->id ?>&policy_id=<?= $policy->id ?>"
                       class="btn btn-danger btn-sm"
                       data-confirm="Delete this document?">✕</a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div>
    <div class="card animate-in animate-in-delay-1">
      <div class="card-header"><span>⏱</span><h3>Renewal Status</h3></div>
      <div class="card-body" style="text-align:center;padding:2rem 1.5rem">
        <div style="font-size:3rem;margin-bottom:.5rem">
          <?= $days < 0 ? '⚠️' : ($days <= 7 ? '🔴' : ($days <= 14 ? '🟡' : '🟢')) ?>
        </div>
        <div style="font-family:var(--font-mono);font-size:2.5rem;font-weight:700;color:<?=
          $days < 0 ? 'var(--red)' : ($days <= 7 ? 'var(--red)' : ($days <= 14 ? 'var(--amber)' : 'var(--green)'))
        ?>">
          <?= abs($days) ?>
        </div>
        <div style="color:var(--text-muted);font-size:.82rem;margin-top:.25rem">
          <?= $days < 0 ? 'days overdue' : 'days until renewal' ?>
        </div>
        <div style="margin-top:1.25rem">
          <span class="renewal-days <?= $daysClass ?>" style="font-size:.85rem;padding:.4rem .9rem">
            <?= date('d M Y', strtotime($policy->renewalDate)) ?>
          </span>
        </div>
      </div>
    </div>

    <?php if ($currentUser->canManagePolicies()): ?>
    <div class="card" style="margin-top:1rem">
      <div class="card-header"><span>⚡</span><h3>Quick Actions</h3></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:.6rem">
        <a href="<?= APP_URL ?>/index.php?page=policies&action=edit&id=<?= $policy->id ?>"
           class="btn btn-secondary btn-full">✏️ Edit Policy</a>
        <button class="btn btn-success btn-full" onclick="openModal('uploadModal')">📎 Upload Document</button>
        <a href="<?= APP_URL ?>/index.php?page=policies&action=delete&id=<?= $policy->id ?>"
           class="btn btn-danger btn-full"
           data-confirm="Permanently delete policy <?= htmlspecialchars($policy->policyNumber) ?>?">
          🗑 Delete Policy
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php if ($currentUser->canManagePolicies()): ?>
<div class="modal-overlay" id="uploadModal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3>Upload Document</h3>
      <button class="modal-close" onclick="closeModal('uploadModal')">✕</button>
    </div>
    <form method="POST"
          action="<?= APP_URL ?>/index.php?page=documents&action=upload"
          enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="policy_id" value="<?= $policy->id ?>" />
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />

        <label class="upload-area" id="uploadArea" for="fileInput">
          <input type="file" id="fileInput" name="document"
                 accept=".jpg,.jpeg,.png,.pdf" required />
          <div class="upload-icon">📤</div>
          <div class="upload-label">
            <span id="fileLabel">Click or drag a file here</span>
          </div>
          <div class="upload-hint">Accepted: JPG, PNG, PDF — Max <?= UPLOAD_MAX_MB ?>MB</div>
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('uploadModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Upload Document</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
