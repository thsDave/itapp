<?php
/**
 * Shared form fields for area create and edit.
 * Injected: $old, $area (edit only), $isEdit, $errors
 */
$val = fn(string $key, string $fallback = '') =>
    htmlspecialchars($old[$key] ?? ($area[$key] ?? $fallback));
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

<div class="form-group">
  <label for="name">Nombre del área <span class="text-danger">*</span></label>
  <input type="text" id="name" name="name" class="form-control"
         value="<?= $val('name') ?>" maxlength="100" required autofocus
         placeholder="Ej: Tecnología, Recursos Humanos, Contabilidad">
  <small class="form-text text-muted">El nombre debe ser único en el sistema.</small>
</div>
