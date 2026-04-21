<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>IT</b>App</a>
  </div>

  <div class="card shadow">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Inicia sesión para continuar</p>

      <form action="<?= APP_URL ?>/login" method="POST" id="loginForm" novalidate>
        <?= \app\helpers\Csrf::field() ?>

        <div class="input-group mb-3">
          <input
            type="email"
            name="email"
            id="email"
            class="form-control"
            placeholder="Correo electrónico"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            autocomplete="email"
            required
          >
          <div class="input-group-append">
            <div class="input-group-text"><i class="fas fa-envelope"></i></div>
          </div>
        </div>

        <div class="input-group mb-3">
          <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            placeholder="Contraseña"
            autocomplete="current-password"
            required
          >
          <div class="input-group-append">
            <div class="input-group-text" id="togglePassword" style="cursor:pointer">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-sign-in-alt mr-1"></i> Iniciar sesión
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<?php if (!empty($error)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
      icon: 'error',
      title: 'Acceso denegado',
      text: <?= json_encode($error) ?>,
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'Entendido'
    });
  });
</script>
<?php endif; ?>

<?php if (!empty($notice)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
      icon: 'success',
      title: 'Listo',
      text: <?= json_encode($notice) ?>,
      timer: 2500,
      showConfirmButton: false
    });
  });
</script>
<?php endif; ?>

<script>
  // Toggle password visibility
  document.getElementById('togglePassword').addEventListener('click', function () {
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

  // Client-side validation before submit
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const emailRe  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!email || !emailRe.test(email)) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Campo inválido',
        text: 'Ingresa un correo electrónico válido.',
        confirmButtonColor: '#3085d6'
      });
      return;
    }

    if (password.length < 6) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Campo inválido',
        text: 'La contraseña debe tener al menos 6 caracteres.',
        confirmButtonColor: '#3085d6'
      });
    }
  });
</script>
