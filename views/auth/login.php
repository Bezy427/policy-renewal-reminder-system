<?php
$pageTitle   = 'Sign In';
$currentUser = null;
require __DIR__ . '/../partials/header.php';
?>

<div class="auth-card animate-in">
  <div class="auth-logo">
    <div class="logo-mark">🛡️</div>
    <h1><?= APP_NAME ?></h1>
    <p>Policy Renewal Management System</p>
  </div>

  <h2>Welcome back</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      ⚠️ <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/index.php?page=login">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />

    <div class="form-group">
      <label class="form-label" for="email">Email Address</label>
      <input class="form-control" type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="you@company.com" required autocomplete="email" />
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <input class="form-control" type="password" id="password" name="password"
             placeholder="••••••••" required autocomplete="current-password" />
    </div>

    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:.5rem;">
      Sign In →
    </button>
  </form>

  <p style="text-align:center;font-size:.75rem;color:var(--text-muted);margin-top:1.5rem;">
    Demo — Admin: <code>admin@company.com</code> / <code>Admin@1234</code><br>
    Officer: <code>officer@company.com</code> / <code>Officer@1234</code>
  </p>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
