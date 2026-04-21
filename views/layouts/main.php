<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fas fa-user-circle mr-1"></i>
          <?= htmlspecialchars(\app\helpers\Session::get('user_name', '')) ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="<?= APP_URL ?>/profile">
            <i class="fas fa-id-card mr-2"></i> Mi Perfil
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
          </a>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Sidebar (role-driven) -->
  <?php require __DIR__ . '/_sidebar.php'; ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header px-3 pt-3">
      <h5 class="m-0 text-muted"><?= htmlspecialchars($pageTitle ?? '') ?></h5>
    </div>
    <section class="content p-3">
      <?= $content ?>
    </section>
  </div>

  <footer class="main-footer">
    <strong><?= APP_NAME ?></strong> &copy; <?= date('Y') ?>
  </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(function () {
  // Initialize all Select2 elements.
  // data-placeholder is read automatically from the element attribute.
  // data-minimum-results-for-search="-1" disables the search box on small lists.
  $('.select2').each(function () {
    $(this).select2({
      theme:                    'bootstrap4',
      width:                    '100%',
      minimumResultsForSearch:  parseInt($(this).data('minimum-results-for-search') ?? 6),
      placeholder:              $(this).data('placeholder') || '',
      allowClear:               true,
    });
  });
});
</script>
</body>
</html>
