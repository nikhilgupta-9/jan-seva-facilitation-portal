  </main> <!-- End admin-main -->

  <footer class="admin-footer" style="background: #ffffff; border-top: 1px solid var(--adm-border); padding: 16px 24px; margin-top: 10px; font-size: 0.82rem; color: #64748b;">
    <div style="max-width: 1400px; margin: 0 auto; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px;">
      <span>&copy; <?php echo date('Y'); ?> <strong><?php echo htmlspecialchars(APP_NAME); ?></strong> &bull; Facilitator Admin Console (v<?php echo APP_VERSION; ?>)</span>
      <span>Last Updated: <?php echo date('d M Y, h:i A'); ?> &bull; <i class="fas fa-shield-alt text-success"></i> 256-Bit SSL Protected</span>
    </div>
  </footer>
</div> <!-- End admin-content-wrap -->

<!-- JavaScript Assets -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  var sidebar = document.getElementById('admin-sidebar');
  var backdrop = document.getElementById('admin-sidebar-backdrop');
  var toggleBtn = document.getElementById('btn-admin-nav-toggle');

  function openSidebar() {
    sidebar.classList.add('show');
    backdrop.classList.add('show');
  }
  function closeSidebar() {
    sidebar.classList.remove('show');
    backdrop.classList.remove('show');
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
    });
  }
  if (backdrop) {
    backdrop.addEventListener('click', closeSidebar);
  }

  // Auto-dismiss alerts after 5 seconds
  document.querySelectorAll('.alert-dismissible').forEach(function (alertEl) {
    setTimeout(function () {
      alertEl.style.transition = 'opacity 0.4s ease';
      alertEl.style.opacity = '0';
      setTimeout(function () { alertEl.remove(); }, 400);
    }, 5000);
  });
})();
</script>
</body>
</html>
