<?php
/**
 * Shared form partial for create and edit role views.
 *
 * Expected variables:
 *   $old     – repopulation array from previous POST
 *   $errors  – validation error strings
 *   $modules – array of module rows with has_access flag (configurable only)
 *   $role    – (edit) current role row from DB
 *   $isEdit  – bool
 */

$val = fn(string $key, string $fallback = '') =>
    htmlspecialchars((string) ($old[$key] ?? ($role[$key] ?? $fallback)));
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

<!-- Role name -->
<div class="form-group">
  <label for="role_name">
    Nombre del rol <span class="text-danger">*</span>
    <small class="text-muted">(solo minúsculas, números y guión bajo)</small>
  </label>
  <input type="text" id="role_name" name="role_name" class="form-control"
         value="<?= $val('role_name') ?>"
         maxlength="50" required autofocus
         placeholder="ej: supervisor, analyst_2">
</div>

<!-- Description -->
<div class="form-group">
  <label for="description">Descripción <small class="text-muted">(opcional)</small></label>
  <textarea id="description" name="description" class="form-control"
            rows="2" maxlength="255"
            placeholder="Breve descripción del rol y sus responsabilidades"><?= $val('description') ?></textarea>
</div>

<!-- Module access -->
<div class="form-group mt-4">
  <label class="d-block font-weight-bold mb-2">
    <i class="fas fa-shield-alt mr-1 text-primary"></i>
    Módulos accesibles
  </label>
  <p class="text-muted small mb-3">
    Selecciona los módulos que los usuarios con este rol podrán ver y usar.
    Los módulos de administración del sistema siempre están reservados para el rol <strong>admin</strong>.
  </p>

  <?php if (!empty($modules)): ?>
  <div class="row">
    <?php foreach ($modules as $m): ?>
    <?php
      $checked = isset($old['modules'])
          ? in_array((string) $m['idmodule'], (array) $old['modules'], true)
          : (bool) $m['has_access'];
    ?>
    <div class="col-sm-6 col-md-4 mb-2">
      <div class="custom-control custom-checkbox">
        <input type="checkbox"
               class="custom-control-input"
               id="module_<?= $m['idmodule'] ?>"
               name="modules[]"
               value="<?= $m['idmodule'] ?>"
               <?= $checked ? 'checked' : '' ?>>
        <label class="custom-control-label" for="module_<?= $m['idmodule'] ?>">
          <i class="<?= htmlspecialchars($m['icon']) ?> mr-1 text-secondary"></i>
          <?= htmlspecialchars($m['module_name']) ?>
        </label>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <p class="text-muted"><em>No hay módulos configurables disponibles.</em></p>
  <?php endif; ?>
</div>
