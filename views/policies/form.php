<?php
$isEdit    = !empty($policy);
$pageTitle = $isEdit ? 'Edit Policy' : 'Add Policy';
$activePage = 'policies_add';
require __DIR__ . '/../partials/header.php';

$v = $formData ?? ($isEdit ? [
  'policy_number'  => $policy->policyNumber,
  'client_name'    => $policy->clientName,
  'insurance_type' => $policy->insuranceType,
  'premium_amount' => $policy->premiumAmount,
  'start_date'     => $policy->startDate,
  'renewal_date'   => $policy->renewalDate,
  'status'         => $policy->status,
] : []);

function fv(array $v, string $key, string $default = ''): string {
  return htmlspecialchars((string)($v[$key] ?? $default));
}

$insuranceTypes = [
  'Commercial Property','Life Insurance','Agricultural','Motor Fleet',
  'Health Insurance','Cyber Liability','Public Liability',
  'Professional Indemnity','Travel Insurance','Home Insurance','Other',
];
?>

<div class="page-header animate-in">
  <div class="page-header-text">
    <h2><?= $isEdit ? 'Edit Policy' : 'New Policy' ?></h2>
    <p><?= $isEdit ? 'Update the policy details below.' : 'Fill in the details to create a new policy.' ?></p>
  </div>
  <a href="<?= APP_URL ?>/index.php?page=policies" class="btn btn-secondary">← Back</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-error animate-in">
    ⚠️ Please correct the following errors:
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST"
      action="<?= APP_URL ?>/index.php?page=policies&action=<?= $isEdit ? 'edit&id=' . $policy->id : 'create' ?>"
      class="animate-in animate-in-delay-1">

  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />

  <div class="card">
    <div class="card-header"><span>📋</span><h3>Policy Information</h3></div>
    <div class="card-body">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="policy_number">Policy Number *</label>
          <input class="form-control" type="text" id="policy_number" name="policy_number"
                 value="<?= fv($v, 'policy_number') ?>"
                 placeholder="e.g. POL-2025-001" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="client_name">Client Name *</label>
          <input class="form-control" type="text" id="client_name" name="client_name"
                 value="<?= fv($v, 'client_name') ?>"
                 placeholder="Full client / company name" required />
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="insurance_type">Insurance Type *</label>
          <select class="form-control" id="insurance_type" name="insurance_type" required>
            <option value="">Select type…</option>
            <?php foreach ($insuranceTypes as $type): ?>
              <option value="<?= htmlspecialchars($type) ?>"
                <?= fv($v, 'insurance_type') === htmlspecialchars($type) ? 'selected' : '' ?>>
                <?= htmlspecialchars($type) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="premium_amount">Premium Amount ($) *</label>
          <input class="form-control" type="number" id="premium_amount" name="premium_amount"
                 value="<?= fv($v, 'premium_amount') ?>"
                 step="0.01" min="0" placeholder="0.00" required />
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="start_date">Start Date *</label>
          <input class="form-control" type="date" id="start_date" name="start_date"
                 value="<?= fv($v, 'start_date') ?>" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="renewal_date">Renewal Date *</label>
          <input class="form-control" type="date" id="renewal_date" name="renewal_date"
                 value="<?= fv($v, 'renewal_date') ?>" required />
        </div>
      </div>

      <div class="form-group" style="max-width:260px">
        <label class="form-label" for="status">Status *</label>
        <select class="form-control" id="status" name="status" required>
          <option value="active"          <?= fv($v,'status') === 'active'          ? 'selected' : '' ?>>Active</option>
          <option value="pending_renewal" <?= fv($v,'status') === 'pending_renewal' ? 'selected' : '' ?>>Pending Renewal</option>
          <option value="expired"         <?= fv($v,'status') === 'expired'         ? 'selected' : '' ?>>Expired</option>
        </select>
      </div>

    </div>
  </div>

  <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1rem">
    <a href="<?= APP_URL ?>/index.php?page=policies" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
      <?= $isEdit ? '💾 Save Changes' : '➕ Create Policy' ?>
    </button>
  </div>

</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>
