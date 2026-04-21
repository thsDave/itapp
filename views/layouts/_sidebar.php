<?php
/**
 * Role-driven sidebar.
 * Reads Auth::navFor() so adding a new nav item only requires
 * editing Auth::NAV — nothing here changes.
 */

use app\helpers\Auth;

$role     = \app\helpers\Session::get('user_role', '');
$navItems = Auth::navFor($role);
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="<?= APP_URL . Auth::homeFor($role) ?>" class="brand-link text-center px-3">
    <i class="fas fa-laptop-code brand-image mr-2"></i>
    <span class="brand-text font-weight-bold"><?= APP_NAME ?></span>
  </a>

  <div class="sidebar">
    <!-- User panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <i class="fas fa-user-circle fa-2x text-white ml-1"></i>
      </div>
      <div class="info">
        <a href="<?= APP_URL ?>/profile" class="d-block text-truncate" style="max-width:140px">
          <?= htmlspecialchars(\app\helpers\Session::get('user_name', '')) ?>
        </a>
        <span class="badge badge-<?= match($role) {
          'admin'      => 'danger',
          'consultant' => 'warning',
          default      => 'secondary',
        } ?> text-xs">
          <?= htmlspecialchars($role) ?>
        </span>
      </div>
    </div>

    <nav class="mt-1">
      <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu">

        <?php foreach ($navItems as $item): ?>
          <?php
            // Mark active when the current URI path segment matches 'match'
            $isActive = str_contains($uri, '/' . $item['match']);
          ?>
          <li class="nav-item">
            <a href="<?= APP_URL . $item['url'] ?>"
               class="nav-link <?= $isActive ? 'active' : '' ?>">
              <i class="nav-icon <?= $item['icon'] ?>"></i>
              <p><?= $item['label'] ?></p>
            </a>
          </li>
        <?php endforeach; ?>

      </ul>
    </nav>
  </div>
</aside>
