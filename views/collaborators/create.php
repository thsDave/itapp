<?php $pageTitle = 'Nuevo colaborador'; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-plus mr-2"></i>Registrar colaborador
        </h3>
      </div>

      <form action="<?= APP_URL ?>/collaborators/store" method="POST"
            id="createCollaboratorForm" novalidate>
        <div class="card-body">
          <?php
            $isEdit       = false;
            $collaborator = [];
            require __DIR__ . '/_form.php';
          ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/collaborators" class="btn btn-secondary">
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

<script>
  document.getElementById('createCollaboratorForm').addEventListener('submit', function (e) {
    const name      = document.getElementById('name').value.trim();
    const position  = document.getElementById('position').value.trim();
    const entryDate = document.getElementById('entry_date').value;
    const exitDate  = document.getElementById('exit_date').value;
    const email     = document.getElementById('collab_email').value.trim();
    const password  = document.getElementById('collab_password').value;
    const emailRe   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const msgs = [];

    if (!name)                   msgs.push('El nombre es requerido.');
    if (!position)               msgs.push('El puesto es requerido.');
    if (!entryDate)              msgs.push('La fecha de ingreso es requerida.');
    if (!emailRe.test(email))    msgs.push('Ingresa un correo institucional válido.');
    if (password.length < 8)     msgs.push('La contraseña debe tener al menos 8 caracteres.');

    if (exitDate && entryDate && exitDate < entryDate) {
      msgs.push('La fecha de egreso no puede ser anterior a la de ingreso.');
    }

    if (msgs.length) {
      e.preventDefault();
      Swal.fire({
        icon:               'warning',
        title:              'Formulario incompleto',
        html:               msgs.map(m => `• ${m}`).join('<br>'),
        confirmButtonColor: '#3085d6',
      });
    }
  });
</script>
