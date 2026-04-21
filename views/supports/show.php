<?php
$pageTitle    = 'Ticket #' . $support['id'];
$flash_notice = \app\helpers\Session::flash('notice');

require __DIR__ . '/_badges.php';

$fmt = fn(?string $d, bool $withTime = false): string =>
    $d ? date($withTime ? 'd/m/Y H:i' : 'd/m/Y', strtotime($d)) : '—';
?>

<?php if ($flash_notice): ?>
<script>
  document.addEventListener('DOMContentLoaded', () =>
    Swal.fire({ icon:'success', title:'Listo', text: <?= json_encode($flash_notice) ?>,
                timer:2500, showConfirmButton:false }));
</script>
<?php endif; ?>

<!-- ── Ticket header ─────────────────────────────────────────────── -->
<div class="card card-primary card-outline mb-3">
  <div class="card-body py-3">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
      <div>
        <h4 class="mb-1"><?= htmlspecialchars($support['title']) ?></h4>
        <div class="d-flex flex-wrap gap-2">
          <?= $levelBadgeHtml($support['attention_level']) ?>
          <?= $statusBadgeHtml($support['status']) ?>
          <span class="text-muted small ml-1">
            <i class="fas fa-clock mr-1"></i><?= $fmt($support['created_at'], true) ?>
          </span>
          <?php if ($support['created_at'] !== $support['updated_at']): ?>
            <span class="text-muted small">
              · Actualizado: <?= $fmt($support['updated_at'], true) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="text-nowrap">
        <a href="<?= APP_URL ?>/supports/<?= $support['id'] ?>/edit"
           class="btn btn-sm btn-warning mr-1">
          <i class="fas fa-edit mr-1"></i>Editar
        </a>
        <a href="<?= APP_URL ?>/supports" class="btn btn-sm btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row">

  <!-- ── Left column: details ──────────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- Collaborator -->
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user mr-2"></i>Colaborador</h3>
      </div>
      <div class="card-body pb-2">
        <p class="mb-1 font-weight-bold">
          <a href="<?= APP_URL ?>/collaborators/<?= $support['collaborator_id'] ?>">
            <?= htmlspecialchars($support['collaborator_name']) ?>
          </a>
        </p>
        <p class="text-muted small mb-0"><?= htmlspecialchars($support['collaborator_position']) ?></p>
      </div>
    </div>

    <!-- Ticket meta -->
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información</h3>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tbody>
            <tr>
              <th class="pl-3" style="width:120px">Nivel</th>
              <td><?= $levelBadgeHtml($support['attention_level']) ?></td>
            </tr>
            <tr>
              <th class="pl-3">Estado</th>
              <td><?= $statusBadgeHtml($support['status']) ?></td>
            </tr>
            <tr>
              <th class="pl-3">Atendido por</th>
              <td>
                <?= $support['attended_by_name']
                    ? htmlspecialchars($support['attended_by_name'])
                    : '<span class="text-muted">Sin asignar</span>' ?>
              </td>
            </tr>
            <tr>
              <th class="pl-3">Creado</th>
              <td><?= $fmt($support['created_at'], true) ?></td>
            </tr>
            <tr>
              <th class="pl-3">Actualizado</th>
              <td><?= $fmt($support['updated_at'], true) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- ── Right column: content ─────────────────────────────────────── -->
  <div class="col-lg-8">

    <!-- Description -->
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-align-left mr-2"></i>Descripción</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($support['description'])): ?>
          <p class="mb-0" style="white-space:pre-wrap"><?= htmlspecialchars($support['description']) ?></p>
        <?php else: ?>
          <p class="text-muted mb-0">Sin descripción.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Notes -->
    <div class="card card-outline card-<?= !empty($support['notes']) ? 'warning' : 'secondary' ?>">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sticky-note mr-2"></i>Notas internas</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($support['notes'])): ?>
          <p class="mb-0" style="white-space:pre-wrap"><?= htmlspecialchars($support['notes']) ?></p>
        <?php else: ?>
          <p class="text-muted mb-0">Sin notas.</p>
        <?php endif; ?>
      </div>
    </div>

  </div>

</div><!-- /.row -->

<!-- ── Danger zone ───────────────────────────────────────────────── -->
<div class="row">
  <div class="col-12">
    <div class="card card-outline card-danger">
      <div class="card-header">
        <h3 class="card-title text-danger">
          <i class="fas fa-exclamation-triangle mr-2"></i>Zona peligrosa
        </h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
        <p class="mb-0 text-muted">Eliminar este ticket es una acción irreversible.</p>
        <form method="POST"
              action="<?= APP_URL ?>/supports/<?= $support['id'] ?>/delete"
              class="form-delete-show">
          <?= \app\helpers\Csrf::field() ?>
          <button type="submit" class="btn btn-danger btn-sm"
                  data-title="<?= htmlspecialchars($support['title']) ?>">
            <i class="fas fa-trash mr-1"></i>Eliminar ticket
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  $('.form-delete-show').on('submit', function (e) {
    e.preventDefault();
    const form  = this;
    const title = $(form).find('button').data('title');

    Swal.fire({
      icon:               'warning',
      title:              '¿Eliminar ticket?',
      html:               `"<strong>${title}</strong>" será eliminado permanentemente.`,
      showCancelButton:   true,
      confirmButtonText:  'Sí, eliminar',
      cancelButtonText:   'Cancelar',
      confirmButtonColor: '#e3342f',
    }).then(r => { if (r.isConfirmed) form.submit(); });
  });
});
</script>
