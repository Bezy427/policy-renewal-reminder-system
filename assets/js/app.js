document.addEventListener('DOMContentLoaded', () => {

  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('sidebar');
  const overlay       = document.getElementById('sidebarOverlay');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('show');
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }

  document.querySelectorAll('.alert[data-auto-close]').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    }, 4000);
  });

  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      const msg = btn.dataset.confirm || 'Are you sure you want to delete this item?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  const uploadArea = document.getElementById('uploadArea');
  const fileInput  = document.getElementById('fileInput');
  const fileLabel  = document.getElementById('fileLabel');

  if (uploadArea && fileInput) {
    uploadArea.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length) {
        fileLabel.textContent = fileInput.files[0].name;
      }
    });

    uploadArea.addEventListener('dragover', e => {
      e.preventDefault();
      uploadArea.classList.add('drag-over');
    });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
    uploadArea.addEventListener('drop', e => {
      e.preventDefault();
      uploadArea.classList.remove('drag-over');
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        fileLabel.textContent = e.dataTransfer.files[0].name;
      }
    });
  }

  document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      const url = new URL(window.location.href);
      const val = btn.dataset.filter;
      if (val) url.searchParams.set('status', val);
      else     url.searchParams.delete('status');
      window.location.href = url.toString();
    });
  });

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    let timer;
    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        const url = new URL(window.location.href);
        if (searchInput.value.trim()) url.searchParams.set('search', searchInput.value.trim());
        else url.searchParams.delete('search');
        window.location.href = url.toString();
      }, 500);
    });
  }

  window.openModal = (id) => {
    const m = document.getElementById(id);
    if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = (id) => {
    const m = document.getElementById(id);
    if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
  };

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) closeModal(overlay.id);
    });
  });

  const premiumInput = document.getElementById('premium_amount');
  if (premiumInput) {
    premiumInput.addEventListener('blur', () => {
      const v = parseFloat(premiumInput.value);
      if (!isNaN(v)) premiumInput.value = v.toFixed(2);
    });
  }

  const startDate   = document.getElementById('start_date');
  const renewalDate = document.getElementById('renewal_date');
  if (startDate && renewalDate) {
    startDate.addEventListener('change', () => {
      renewalDate.min = startDate.value;
    });
  }
});
