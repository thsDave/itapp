<?php
/**
 * Shared form fields for create and edit.
 *
 * Expected variables injected by the parent view:
 *   $old      – array of previous POST values (for repopulation)
 *   $roles    – array of valid role strings
 *   $statuses – array of [{idstatus, status}] rows from Status::forUsers()
 *   $user     – (edit only) current user row from DB
 *   $isEdit   – bool
 */

$val    = fn(string $key, string $fallback = '') =>
    htmlspecialchars((string) ($old[$key] ?? ($user[$key] ?? $fallback)));

$isSelf = ($isEdit ?? false) && (int)($user['id'] ?? 0) === (int)\app\helpers\Session::get('user_id');

$statusLabels = [1 => 'Activo', 2 => 'Inactivo'];
?>

<?= \app\helpers\Csrf::field() ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <ul class="mb-0">
    <?php foreach ($errors as $e): ?>
      <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<!-- Name -->
<div class="form-group">
  <label for="name">Nombre completo <span class="text-danger">*</span></label>
  <input type="text" id="name" name="name" class="form-control"
         value="<?= $val('name') ?>" maxlength="100" required autofocus>
</div>

<!-- Email -->
<div class="form-group">
  <label for="email">Correo electrónico <span class="text-danger">*</span></label>
  <input type="email" id="email" name="email" class="form-control"
         value="<?= $val('email') ?>" maxlength="150" required>
</div>

<!-- Password -->
<div class="form-group">
  <label for="password">
    Contraseña
    <?php if ($isEdit ?? false): ?>
      <small class="text-muted">(dejar vacío para no cambiar)</small>
    <?php else: ?>
      <span class="text-danger">*</span>
    <?php endif; ?>
  </label>
  <div class="input-group">
    <input type="password" id="password" name="password" class="form-control"
           minlength="6" maxlength="72" autocomplete="new-password"
           placeholder="••••••••"
           <?= ($isEdit ?? false) ? '' : 'required' ?>>
    <div class="input-group-append">
      <span class="input-group-text" id="togglePwd" style="cursor:pointer">
        <i class="fas fa-eye" id="eyeIcon"></i>
      </span>
    </div>
  </div>
</div>

<!-- Role -->
<div class="form-group">
  <label for="role">Rol <span class="text-danger">*</span></label>
  <select id="role" name="role" class="form-control select2"
          data-placeholder="— Seleccionar —" data-minimum-results-for-search="-1" required
          <?= $isSelf ? 'disabled' : '' ?>>
    <option value="">— Seleccionar —</option>
    <?php foreach ($roles as $r): ?>
      <option value="<?= $r ?>" <?= $val('role') === $r ? 'selected' : '' ?>>
        <?= ucfirst($r) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <?php if ($isSelf): ?>
    <small class="text-muted">No puedes cambiar tu propio rol.</small>
    <input type="hidden" name="role" value="<?= $val('role') ?>">
  <?php endif; ?>
</div>

<!-- Status -->
<div class="form-group">
  <label for="idstatus">Estado <span class="text-danger">*</span></label>
  <select id="idstatus" name="idstatus" class="form-control select2"
          data-placeholder="— Seleccionar —" data-minimum-results-for-search="-1" required
          <?= $isSelf ? 'disabled' : '' ?>>
    <option value="">— Seleccionar —</option>
    <?php foreach ($statuses as $s): ?>
      <option value="<?= (int)$s['idstatus'] ?>"
        <?= (int)$val('idstatus') === (int)$s['idstatus'] ? 'selected' : '' ?>>
        <?= $statusLabels[(int)$s['idstatus']] ?? htmlspecialchars($s['status']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <?php if ($isSelf): ?>
    <small class="text-muted">No puedes cambiar tu propio estado.</small>
    <input type="hidden" name="idstatus" value="<?= (int)($user['idstatus'] ?? 1) ?>">
  <?php endif; ?>
</div>

<script>
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
</script>
