<?php
/**
 * Shared form fields for collaborator create and edit.
 *
 * Injected variables:
 *   $old          – previous POST values (repopulation on error)
 *   $collaborator – (edit only) current DB row
 *   $areas        – array of area rows from DB
 *   $countries    – array of country name strings
 *   $isEdit       – bool
 *   $errors       – validation errors array
 */

$val = fn(string $key, string $fallback = '') =>
    htmlspecialchars($old[$key] ?? ($collaborator[$key] ?? $fallback));
?>

<?= \app\helpers\Csrf::field() ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <ul class="mb-0 pl-3">
    <?php foreach ($errors as $e): ?>
      <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="row">

  <!-- Name -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="name">Nombre completo <span class="text-danger">*</span></label>
      <input type="text" id="name" name="name" class="form-control"
             value="<?= $val('name') ?>" maxlength="100" required autofocus>
    </div>
  </div>

  <!-- Position -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="position">Puesto <span class="text-danger">*</span></label>
      <input type="text" id="position" name="position" class="form-control"
             value="<?= $val('position') ?>" maxlength="100" required>
    </div>
  </div>

</div><!-- /.row -->

<div class="row">

  <!-- Country -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="country">País</label>
      <select id="country" name="country" class="form-control select2"
              data-placeholder="— Seleccionar país —">
        <option value="">— Seleccionar país —</option>
        <?php foreach ($countries as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>"
            <?= $val('country') === htmlspecialchars($c) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Area -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="area_id">Área institucional</label>
      <select id="area_id" name="area_id" class="form-control select2"
              data-placeholder="— Seleccionar área —">
        <option value="">— Seleccionar área —</option>
        <?php foreach ($areas as $a): ?>
          <option value="<?= $a['id'] ?>"
            <?= (int)($old['area_id'] ?? ($collaborator['area_id'] ?? 0)) === (int)$a['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

</div><!-- /.row -->

<div class="row">

  <!-- Entry date -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="entry_date">Fecha de ingreso <span class="text-danger">*</span></label>
      <input type="date" id="entry_date" name="entry_date" class="form-control"
             value="<?= $val('entry_date') ?>" required>
    </div>
  </div>

  <!-- Exit date -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="exit_date">
        Fecha de egreso
        <small class="text-muted">(dejar vacío si sigue activo)</small>
      </label>
      <input type="date" id="exit_date" name="exit_date" class="form-control"
             value="<?= $val('exit_date') ?>">
    </div>
  </div>

</div><!-- /.row -->

<!-- Assigned equipment -->
<div class="form-group">
  <label for="assigned_equipment">Equipo asignado</label>
  <textarea id="assigned_equipment" name="assigned_equipment"
            class="form-control" rows="3"
            placeholder="Ej: Laptop Dell XPS 15, Monitor 27&quot;, Teclado mecánico"
  ><?= htmlspecialchars($old['assigned_equipment'] ?? ($collaborator['assigned_equipment'] ?? '')) ?></textarea>
  <small class="form-text text-muted">Lista el equipo separado por comas.</small>
</div>

<?php if (!($isEdit ?? false)): ?>
<!-- ── System access credentials (create only) ────────────────── -->
<hr class="my-3">
<h6 class="font-weight-bold text-secondary mb-3">
  <i class="fas fa-key mr-1"></i>Acceso al sistema
  <small class="font-weight-normal text-muted ml-2">
    Se creará un usuario con rol <em>usuario</em> automáticamente.
  </small>
</h6>

<div class="row">

  <!-- Institutional email -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="collab_email">
        Correo institucional <span class="text-danger">*</span>
      </label>
      <input type="email" id="collab_email" name="email" class="form-control"
             value="<?= htmlspecialchars($old['email'] ?? '') ?>"
             maxlength="150" required placeholder="usuario@empresa.com">
      <small class="form-text text-muted">
        Se usará como credencial de inicio de sesión.
      </small>
    </div>
  </div>

  <!-- Password -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="collab_password">
        Contraseña <span class="text-danger">*</span>
      </label>
      <div class="input-group">
        <input type="password" id="collab_password" name="password"
               class="form-control"
               minlength="8" maxlength="72" required
               placeholder="Mínimo 8 caracteres"
               autocomplete="new-password">
        <div class="input-group-append">
          <span class="input-group-text" id="toggleCollabPwd"
                style="cursor:pointer" title="Mostrar / ocultar">
            <i class="fas fa-eye" id="eyeIconCollab"></i>
          </span>
        </div>
      </div>
    </div>
  </div>

</div><!-- /.row -->

<script>
  document.getElementById('toggleCollabPwd').addEventListener('click', function () {
    const input = document.getElementById('collab_password');
    const icon  = document.getElementById('eyeIconCollab');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });
</script>
<?php endif; ?>

<script>
  (function () {
    const entry = document.getElementById('entry_date');
    const exit  = document.getElementById('exit_date');
    function syncMin() { if (entry.value) exit.min = entry.value; }
    entry.addEventListener('change', syncMin);
    syncMin();
  }());
</script>
