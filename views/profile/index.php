<?php $pageTitle = 'Mi Perfil'; ?>

<?php
$flash_error  = \app\helpers\Session::flash('error');
$flash_notice = \app\helpers\Session::flash('notice');
?>

<?php if ($flash_error): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ icon: 'error', title: 'Error', text: <?= json_encode($flash_error) ?>, confirmButtonColor: '#e3342f' });
  });
</script>
<?php endif; ?>

<?php if ($flash_notice): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({ icon: 'success', title: 'Listo', text: <?= json_encode($flash_notice) ?>, timer: 2500, showConfirmButton: false });
  });
</script>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-md-7 col-lg-6">

    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-edit mr-2"></i>Información de cuenta
        </h3>
      </div>

      <form action="<?= APP_URL ?>/profile/update" method="POST" id="profileForm" novalidate>
        <?= \app\helpers\Csrf::field() ?>
        <div class="card-body">

          <!-- Read-only fields -->
          <div class="form-group">
            <label class="text-muted small">Correo electrónico</label>
            <input type="text" class="form-control-plaintext font-weight-bold"
                   value="<?= htmlspecialchars($user['email']) ?>" readonly>
          </div>

          <div class="form-group">
            <label class="text-muted small">Rol</label>
            <div>
              <span class="badge badge-<?= match($user['role']) {
                'admin'      => 'danger',
                'consultant' => 'warning',
                default      => 'secondary',
              } ?> px-3 py-1">
                <?= htmlspecialchars($user['role']) ?>
              </span>
            </div>
          </div>

          <hr>

          <!-- Editable fields -->
          <div class="form-group">
            <label for="name">Nombre completo <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control"
                   value="<?= htmlspecialchars($user['name']) ?>" required maxlength="100">
          </div>

          <div class="form-group">
            <label for="password">Nueva contraseña
              <small class="text-muted">(dejar vacío para no cambiar)</small>
            </label>
            <div class="input-group">
              <input type="password" name="password" id="password"
                     class="form-control" minlength="6" maxlength="72"
                     autocomplete="new-password" placeholder="••••••••">
              <div class="input-group-append">
                <span class="input-group-text" id="togglePwd" style="cursor:pointer">
                  <i class="fas fa-eye" id="eyeIcon"></i>
                </span>
              </div>
            </div>
          </div>

        </div><!-- /.card-body -->

        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
  // Toggle password visibility
  document.getElementById('togglePwd').addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  });

  // Client-side validation
  document.getElementById('profileForm').addEventListener('submit', function (e) {
    const name = document.getElementById('name').value.trim();
    const pwd  = document.getElementById('password').value;

    if (!name) {
      e.preventDefault();
      Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre no puede estar vacío.' });
      return;
    }
    if (pwd && pwd.length < 6) {
      e.preventDefault();
      Swal.fire({ icon: 'warning', title: 'Contraseña muy corta', text: 'La contraseña debe tener al menos 6 caracteres.' });
    }
  });
</script>
