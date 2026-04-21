<?php
/**
 * Shared form fields for support create and edit.
 *
 * Injected variables:
 *   $old           – previous POST values
 *   $support       – (edit only) DB row (already joined)
 *   $collaborators – all collaborators array
 *   $users         – all users array
 *   $isEdit        – bool
 *   $errors        – validation errors array
 */

$val = fn(string $key, string $fallback = '') =>
    htmlspecialchars($old[$key] ?? ($support[$key] ?? $fallback));

use app\models\Support;
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

<!-- Collaborator -->
<div class="form-group">
  <label for="collaborator_id">Colaborador <span class="text-danger">*</span></label>
  <select id="collaborator_id" name="collaborator_id" class="form-control select2"
          data-placeholder="— Seleccionar colaborador —" required>
    <option value="">— Seleccionar colaborador —</option>
    <?php foreach ($collaborators as $c): ?>
      <option value="<?= $c['id'] ?>"
        <?= (int)($old['collaborator_id'] ?? ($support['collaborator_id'] ?? 0)) === (int)$c['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['name']) ?>
        (<?= htmlspecialchars($c['position']) ?>)
      </option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Title -->
<div class="form-group">
  <label for="title">Título <span class="text-danger">*</span></label>
  <input type="text" id="title" name="title" class="form-control"
         value="<?= $val('title') ?>" maxlength="200" required autofocus>
</div>

<!-- Description -->
<div class="form-group">
  <label for="description">Descripción</label>
  <textarea id="description" name="description" class="form-control" rows="4"
            placeholder="Describe el problema o solicitud con el mayor detalle posible…"
  ><?= htmlspecialchars($old['description'] ?? ($support['description'] ?? '')) ?></textarea>
</div>

<div class="row">

  <!-- Attention level -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="attention_level">Nivel de atención <span class="text-danger">*</span></label>
      <select id="attention_level" name="attention_level" class="form-control select2"
              data-placeholder="— Seleccionar —" data-minimum-results-for-search="-1" required>
        <option value="">— Seleccionar —</option>
        <?php
          $levelLabel = ['low' => 'Bajo', 'medium' => 'Medio', 'high' => 'Alto', 'critical' => 'Crítico'];
          foreach (Support::LEVELS as $lv):
        ?>
          <option value="<?= $lv ?>"
            <?= $val('attention_level') === $lv ? 'selected' : '' ?>>
            <?= $levelLabel[$lv] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Status -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="status">Estado <span class="text-danger">*</span></label>
      <select id="status" name="status" class="form-control select2"
              data-placeholder="— Seleccionar —" data-minimum-results-for-search="-1" required>
        <option value="">— Seleccionar —</option>
        <?php
          $statusLabel = ['open' => 'Abierto', 'in_progress' => 'En proceso', 'closed' => 'Cerrado'];
          foreach (Support::STATUSES as $st):
        ?>
          <option value="<?= $st ?>"
            <?= $val('status') === $st ? 'selected' : '' ?>>
            <?= $statusLabel[$st] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Attended by -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="user_id">Atendido por</label>
      <select id="user_id" name="user_id" class="form-control select2"
              data-placeholder="— Sin asignar —">
        <option value="">— Sin asignar —</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>"
            <?= (int)($old['user_id'] ?? ($support['user_id'] ?? 0)) === (int)$u['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['name']) ?>
            (<?= htmlspecialchars($u['role']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

</div><!-- /.row -->

<!-- Notes -->
<div class="form-group">
  <label for="notes">Notas internas</label>
  <textarea id="notes" name="notes" class="form-control" rows="3"
            placeholder="Observaciones internas, pasos realizados, pendientes…"
  ><?= htmlspecialchars($old['notes'] ?? ($support['notes'] ?? '')) ?></textarea>
</div>
