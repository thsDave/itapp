<?php $pageTitle = 'Nuevo usuario'; ?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-plus mr-2"></i>Crear usuario
        </h3>
      </div>

      <form action="<?= APP_URL ?>/users/store" method="POST"
            id="createUserForm" novalidate>
        <div class="card-body">
          <?php $isEdit = false; $user = []; require __DIR__ . '/_form.php'; ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
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
  document.getElementById('createUserForm').addEventListener('submit', function (e) {
    const name  = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const pwd   = document.getElementById('password').value;
    const role     = document.getElementById('role').value;
    const idstatus = document.getElementById('idstatus').value;
    const emailRe  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const msgs = [];
    if (!name)               msgs.push('El nombre es requerido.');
    if (!emailRe.test(email))msgs.push('Ingresa un correo válido.');
    if (pwd.length < 6)      msgs.push('La contraseña debe tener al menos 6 caracteres.');
    if (!role)               msgs.push('Selecciona un rol.');
    if (!idstatus)           msgs.push('Selecciona un estado.');

    if (msgs.length) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Formulario incompleto',
        html: msgs.map(m => `• ${m}`).join('<br>'),
        confirmButtonColor: '#3085d6',
      });
    }
  });
</script>
