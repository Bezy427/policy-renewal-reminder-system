<?php
$pageTitle  = 'User Management';
$activePage = 'users';
require __DIR__ . '/../partials/header.php';
?>

<div class="page-header animate-in">
  <div class="page-header-text">
    <h2>User Management</h2>
    <p><?= count($users) ?> system user<?= count($users) !== 1 ? 's' : '' ?></p>
  </div>
  <button class="btn btn-primary" onclick="openModal('createUserModal')">➕ Add User</button>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" data-auto-close>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>

<div class="card animate-in animate-in-delay-1">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="td-bold"><?= htmlspecialchars($u->fullName) ?></td>
            <td style="color:var(--text-muted)"><?= htmlspecialchars($u->email) ?></td>
            <td>
              <span class="badge badge-<?= $u->role === 'admin' ? 'admin' : ($u->role === 'policy_officer' ? 'officer' : 'viewer') ?>">
                <?= $u->getRoleLabel() ?>
              </span>
            </td>
            <td>
              <?php if ($u->isActive): ?>
                <span class="badge badge-active">Active</span>
              <?php else: ?>
                <span class="badge badge-expired">Inactive</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--text-muted)"><?= date('d M Y', strtotime($u->createdAt)) ?></td>
            <td>
              <div class="td-actions">
                <button class="btn btn-secondary btn-sm"
                        onclick="openEditModal(<?= $u->id ?>, '<?= htmlspecialchars(addslashes($u->fullName)) ?>',
                          '<?= htmlspecialchars(addslashes($u->email)) ?>', '<?= $u->role ?>',
                          <?= $u->isActive ? 1 : 0 ?>)">
                  Edit
                </button>
                <?php if ($u->id !== $currentUser->id): ?>
                  <a href="<?= APP_URL ?>/index.php?page=users&action=toggle&id=<?= $u->id ?>"
                     class="btn btn-<?= $u->isActive ? 'danger' : 'success' ?> btn-sm"
                     data-confirm="<?= $u->isActive ? 'Deactivate' : 'Activate' ?> this user?">
                    <?= $u->isActive ? 'Deactivate' : 'Activate' ?>
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="createUserModal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3>Create New User</h3>
      <button class="modal-close" onclick="closeModal('createUserModal')">✕</button>
    </div>
    <?php if (!empty($createErrors)): ?>
      <div class="alert alert-error" style="margin:1rem 1.5rem 0">
        <ul style="margin:0">
          <?php foreach ($createErrors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="POST" action="<?= APP_URL ?>/index.php?page=users&action=create">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input class="form-control" type="text" name="full_name"
                 value="<?= htmlspecialchars($createData['full_name'] ?? '') ?>" required />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input class="form-control" type="email" name="email"
                 value="<?= htmlspecialchars($createData['email'] ?? '') ?>" required />
        </div>
        <div class="form-group">
          <label class="form-label">Password * <span style="color:var(--text-muted);font-weight:400">(min. 8 characters)</span></label>
          <input class="form-control" type="password" name="password" required />
        </div>
        <div class="form-group">
          <label class="form-label">Role *</label>
          <select class="form-control" name="role" required>
            <option value="viewer">Viewer</option>
            <option value="policy_officer">Policy Officer</option>
            <option value="admin">Administrator</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create User</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editUserModal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit User</h3>
      <button class="modal-close" onclick="closeModal('editUserModal')">✕</button>
    </div>
    <?php if (!empty($editErrors)): ?>
      <div class="alert alert-error" style="margin:1rem 1.5rem 0">
        <ul style="margin:0">
          <?php foreach ($editErrors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="POST" action="<?= APP_URL ?>/index.php?page=users&action=edit" id="editUserForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
        <input type="hidden" name="id" id="editUserId" />
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input class="form-control" type="text" name="full_name" id="editFullName" required />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input class="form-control" type="email" name="email" id="editEmail" required />
        </div>
        <div class="form-group">
          <label class="form-label">New Password <span style="color:var(--text-muted);font-weight:400">(leave blank to keep current)</span></label>
          <input class="form-control" type="password" name="password" />
        </div>
        <div class="form-group">
          <label class="form-label">Role *</label>
          <select class="form-control" name="role" id="editRole">
            <option value="viewer">Viewer</option>
            <option value="policy_officer">Policy Officer</option>
            <option value="admin">Administrator</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-control" name="is_active" id="editIsActive">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($createErrors) || !empty($createData)): ?>
  <script>document.addEventListener('DOMContentLoaded', () => openModal('createUserModal'));</script>
<?php endif; ?>
<?php if (!empty($editErrors) || !empty($editData)): ?>
  <script>document.addEventListener('DOMContentLoaded', () => {
    openEditModal(<?= (int)($editData['id'] ?? 0) ?>,
      '<?= htmlspecialchars(addslashes($editData['full_name'] ?? '')) ?>',
      '<?= htmlspecialchars(addslashes($editData['email'] ?? '')) ?>',
      '<?= htmlspecialchars($editData['role'] ?? '') ?>',
      <?= (int)($editData['is_active'] ?? 1) ?>);
  });</script>
<?php endif; ?>

<script>
function openEditModal(id, fullName, email, role, isActive) {
  document.getElementById('editUserId').value   = id;
  document.getElementById('editFullName').value = fullName;
  document.getElementById('editEmail').value    = email;
  document.getElementById('editRole').value     = role;
  document.getElementById('editIsActive').value = isActive;
  openModal('editUserModal');
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
