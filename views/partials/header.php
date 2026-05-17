<?php
$activePage  = $activePage  ?? '';
$pageTitle   = $pageTitle   ?? 'Dashboard';
$currentUser = $currentUser ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css" />
</head>
<body>

<?php if ($currentUser): ?>
<div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;"
     onclick="document.getElementById('sidebar').classList.remove('open');this.style.display='none'"></div>

<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo-mark">🛡️</div>
      <div>
        <div class="brand"><?= APP_NAME ?></div>
        <div class="tagline">Policy Renewal System</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>

      <a class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>"
         href="<?= APP_URL ?>/index.php?page=dashboard">
        <span class="nav-icon">📊</span> Dashboard
      </a>

      <a class="nav-item <?= $activePage === 'policies' ? 'active' : '' ?>"
         href="<?= APP_URL ?>/index.php?page=policies">
        <span class="nav-icon">📋</span> Policies
      </a>

      <?php if ($currentUser->canManagePolicies()): ?>
      <a class="nav-item <?= $activePage === 'policies_add' ? 'active' : '' ?>"
         href="<?= APP_URL ?>/index.php?page=policies&action=create">
        <span class="nav-icon">➕</span> Add Policy
      </a>
      <?php endif; ?>

      <?php if ($currentUser->canManageUsers()): ?>
      <div class="nav-section-label">Administration</div>
      <a class="nav-item <?= $activePage === 'users' ? 'active' : '' ?>"
         href="<?= APP_URL ?>/index.php?page=users">
        <span class="nav-icon">👥</span> User Management
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="user-pill">
        <div class="user-avatar">
          <?= strtoupper(substr($currentUser->fullName, 0, 1)) ?>
        </div>
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($currentUser->fullName) ?></div>
          <div class="user-role"><?= $currentUser->getRoleLabel() ?></div>
        </div>
        <a href="<?= APP_URL ?>/index.php?page=logout" class="logout-btn" title="Logout">⏻</a>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <button id="sidebarToggle" style="display:none;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;margin-right:.5rem;">☰</button>
      <span class="topbar-title"><?= htmlspecialchars($pageTitle) ?></span>
      <div class="topbar-actions">
        <span class="badge badge-<?= $currentUser->role === 'admin' ? 'admin' : ($currentUser->role === 'policy_officer' ? 'officer' : 'viewer') ?>">
          <?= $currentUser->getRoleLabel() ?>
        </span>
      </div>
    </div>
    <div class="page-body">
<?php else: ?>
<div class="auth-page">
<?php endif; ?>
