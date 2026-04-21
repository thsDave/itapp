<?php $pageTitle = 'Nuevo ticket'; ?>

<div class="row justify-content-center">
  <div class="col-lg-9">

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-plus-circle mr-2"></i>Registrar ticket de soporte
        </h3>
      </div>

      <form action="<?= APP_URL ?>/supports/store" method="POST"
            id="createSupportForm" novalidate>
        <div class="card-body">
          <?php
            $isEdit  = false;
            $support = [];
            require __DIR__ . '/_form.php';
          ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/supports" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar ticket
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
  document.getElementById('createSupportForm').addEventListener('submit', function (e) {
    const collaborator = document.getElementById('collaborator_id').value;
    const title        = document.getElementById('title').value.trim();
    const level        = document.getElementById('attention_level').value;
    const status       = document.getElementById('status').value;
    const msgs = [];

    if (!collaborator) msgs.push('Selecciona un colaborador.');
    if (!title)         msgs.push('El título es requerido.');
    if (!level)         msgs.push('Selecciona un nivel de atención.');
    if (!status)        msgs.push('Selecciona un estado.');

    if (msgs.length) {
      e.preventDefault();
      Swal.fire({
        icon:  'warning',
        title: 'Formulario incompleto',
        html:  msgs.map(m => `• ${m}`).join('<br>'),
        confirmButtonColor: '#3085d6',
      });
    }
  });
</script>
