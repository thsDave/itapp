<?php
$pageTitle = 'Acceso denegado';
$home      = APP_URL . \app\helpers\Auth::homeFor(\app\helpers\Session::get('user_role', ''));
?>

<div class="row justify-content-center mt-5">
  <div class="col-md-6 text-center">
    <div class="error-page">
      <h2 class="headline text-warning" style="font-size:5rem;">403</h2>
      <div class="error-content mt-3">
        <h3>
          <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
          Acceso denegado
        </h3>
        <p class="text-muted">No tienes permisos para acceder a esta sección.</p>
        <a href="<?= $home ?>" class="btn btn-warning">
          <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
        </a>
      </div>
    </div>
  </div>
</div>
