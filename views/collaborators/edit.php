<?php $pageTitle = 'Editar colaborador'; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">

    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-edit mr-2"></i>
          Editar: <strong><?= htmlspecialchars($collaborator['name']) ?></strong>
        </h3>
        <div class="card-tools">
          <?php if (!$collaborator['exit_date']): ?>
            <span class="badge badge-success">Activo</span>
          <?php else: ?>
            <span class="badge badge-secondary">Egresado</span>
          <?php endif; ?>
        </div>
      </div>

      <form action="<?= APP_URL ?>/collaborators/<?= $collaborator['id'] ?>/update"
            method="POST" id="editCollaboratorForm" novalidate>
        <div class="card-body">
          <?php $isEdit = true; require __DIR__ . '/_form.php'; ?>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="<?= APP_URL ?>/collaborators" class="btn btn-secondary">
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
  document.getElementById('editCollaboratorForm').addEventListener('submit', function (e) {
    const name      = document.getElementById('name').value.trim();
    const position  = document.getElementById('position').value.trim();
    const entryDate = document.getElementById('entry_date').value;
    const exitDate  = document.getElementById('exit_date').value;
    const msgs = [];

    if (!name)      msgs.push('El nombre es requerido.');
    if (!position)  msgs.push('El puesto es requerido.');
    if (!entryDate) msgs.push('La fecha de ingreso es requerida.');

    if (exitDate && entryDate && exitDate < entryDate) {
      msgs.push('La fecha de egreso no puede ser anterior a la de ingreso.');
    }

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
