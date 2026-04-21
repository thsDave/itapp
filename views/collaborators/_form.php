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

<script>
  (function () {
    const entry = document.getElementById('entry_date');
    const exit  = document.getElementById('exit_date');
    function syncMin() { if (entry.value) exit.min = entry.value; }
    entry.addEventListener('change', syncMin);
    syncMin();
  }());
</script>
