<?php
$pageTitle = 'Editar ticket #' . $support['id'];
require __DIR__ . '/_badges.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-9">

    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-edit mr-2"></i>
          Editar: <strong><?= htmlspecialchars($support['title']) ?></strong>
        </h3>
        <div class="card-tools">
          <?= $levelBadgeHtml($support['attention_level']) ?>
          <?= $statusBadgeHtml($support['status']) ?>
        </div>
      </div>

      <form action="<?= APP_URL ?>/supports/<?= $support['id'] ?>/update"
            method="POST" id="editSupportForm" novalidate>
        <div class="card-body">
          <?php $isEdit = true; require __DIR__ . '/_form.php'; ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/supports/<?= $support['id'] ?>"
             class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save mr-1"></i> Actualizar ticket
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
  document.getElementById('editSupportForm').addEventListener('submit', function (e) {
    const collaborator = document.getElementById('collaborator_id').value;
    const title        = document.getElementById('title').value.trim();
    const level        = document.getElementById('attention_level').value;
    const status       = document.getElementById('status').value;
    const msgs = [];

    if (!collaborator) msgs.push('Selecciona un colaborador.');
    if (!title)         msgs.push('El título es requerido.');
    if (!level)         msgs.push('Selecciona un nivel de atención.');
    if (!status)        msgs.push('Selecciona un estado.');

    if (msgs.length) {
      e.preventDefault();
      Swal.fire({
        icon:  'warning',
        title: 'Formulario incompleto',
        html:  msgs.map(m => `• ${m}`).join('<br>'),
        confirmButtonColor: '#3085d6',
      });
    }
  });
</script>
