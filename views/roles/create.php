<?php $pageTitle = 'Nuevo rol'; ?>

<div class="row justify-content-center">
  <div class="col-md-10 col-lg-8">

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-plus-circle mr-2"></i>Crear nuevo rol
        </h3>
      </div>

      <form action="<?= APP_URL ?>/roles/store" method="POST" id="createRoleForm" novalidate>
        <div class="card-body">
          <?php require __DIR__ . '/_form.php'; ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/roles" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar rol
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
