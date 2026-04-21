<?php $pageTitle = 'Nueva área'; ?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-sitemap mr-2"></i>Registrar área institucional
        </h3>
      </div>

      <form action="<?= APP_URL ?>/areas/store" method="POST" novalidate>
        <div class="card-body">
          <?php
            $isEdit = false;
            $area   = [];
            require __DIR__ . '/_form.php';
          ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/areas" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
