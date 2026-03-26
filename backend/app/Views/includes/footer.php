    </div><!-- /.page-content -->

    <footer class="footer pt-4 pb-3">
      <div class="footer-content">
        <p class="foot-text text-muted text-center">
          &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; All rights reserved.
        </p>
      </div>
    </footer>

  </div><!-- /#main -->
</div><!-- /#app -->

<!-- Mazer Core JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script src="<?= base_url('assets/js/dark.js') ?>"></script>
<script src="<?= base_url('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') ?>"></script>

<!-- jQuery + Validation (required by admin forms) -->
<script src="<?= base_url('assets/extensions/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('admin/js/jquery.validate.js') ?>"></script>
<script src="<?= base_url('admin/js/validation.js') ?>"></script>

<!-- Dark mode toggle button -->
<script>
  document.getElementById('dark-toggle')?.addEventListener('click', function(e) {
    e.preventDefault();
    var theme = document.documentElement.getAttribute('data-bs-theme');
    document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'light' : 'dark');
    localStorage.setItem('theme', document.documentElement.getAttribute('data-bs-theme'));
  });
</script>

<!-- Active nav highlight -->
<script>
  (function() {
    var path = window.location.pathname;
    document.querySelectorAll('#sidebar .sidebar-link, #sidebar .submenu-link').forEach(function(link) {
      if (link.href && path.endsWith(link.getAttribute('href').replace(baseURL.replace(/\/$/, ''), ''))) {
        link.closest('.sidebar-item, .submenu-item')?.classList.add('active');
      }
    });
  })();
</script>

</body>
</html>
