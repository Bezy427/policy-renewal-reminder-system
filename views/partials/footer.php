<?php /** Shared layout footer */ ?>
<?php if (!empty($currentUser)): ?>
    </div>
  </main>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script>
  if (window.innerWidth <= 900) {
    const t = document.getElementById('sidebarToggle');
    if (t) t.style.display = 'inline-flex';
  }
</script>
</body>
</html>
