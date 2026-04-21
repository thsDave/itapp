<?php $pageTitle = 'Editar rol'; ?>

<div class="row justify-content-center">
  <div class="col-md-10 col-lg-8">

    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-edit mr-2"></i>
          Editar: <strong><?= htmlspecialchars($role['role_name']) ?></strong>
        </h3>
      </div>

      <form action="<?= APP_URL ?>/roles/<?= $role['idrole'] ?>/update"
            method="POST" id="editRoleForm" novalidate>
        <div class="card-body">
          <?php $isEdit = true; require __DIR__ . '/_form.php'; ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/roles" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save mr-1"></i> Actualizar rol
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
