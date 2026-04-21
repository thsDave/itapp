<?php
$pageTitle = 'Editar usuario';
$selfId    = (int) \app\helpers\Session::get('user_id');
?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">

    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-edit mr-2"></i>
          Editar: <strong><?= htmlspecialchars($user['name']) ?></strong>
        </h3>
        <div class="card-tools">
          <?php
            $roleBadge = ['admin' => 'danger', 'consultant' => 'warning', 'user' => 'info'];
          ?>
          <span class="badge badge-<?= $roleBadge[$user['role']] ?? 'secondary' ?> px-2">
            <?= htmlspecialchars($user['role']) ?>
          </span>
        </div>
      </div>

      <form action="<?= APP_URL ?>/users/<?= $user['id'] ?>/update" method="POST"
            id="editUserForm" novalidate>
        <div class="card-body">
          <?php $isEdit = true; require __DIR__ . '/_form.php'; ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save mr-1"></i> Actualizar
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
  document.getElementById('editUserForm').addEventListener('submit', function (e) {
    const name  = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const pwd   = document.getElementById('password').value;
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const msgs = [];
    if (!name)               msgs.push('El nombre es requerido.');
    if (!emailRe.test(email))msgs.push('Ingresa un correo válido.');
    if (pwd && pwd.length < 6) msgs.push('La nueva contraseña debe tener al menos 6 caracteres.');

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
