<?php
$pageTitle    = 'Detalle del colaborador';
$isActive     = empty($collaborator['exit_date']);
$flash_notice = \app\helpers\Session::flash('notice');
$flash_error  = \app\helpers\Session::flash('error');

$fmt = fn(?string $d): string => $d ? date('d/m/Y', strtotime($d)) : '—';
?>

<?php if ($flash_notice): ?>
<script>
  document.addEventListener('DOMContentLoaded', () =>
    Swal.fire({ icon: 'success', title: 'Listo', text: <?= json_encode($flash_notice) ?>,
                timer: 2500, showConfirmButton: false }));
</script>
<?php endif; ?>

<?php if ($flash_error): ?>
<script>
  document.addEventListener('DOMContentLoaded', () =>
    Swal.fire({ icon: 'error', title: 'Error', text: <?= json_encode($flash_error) ?>,
                confirmButtonColor: '#e3342f' }));
</script>
<?php endif; ?>

<div class="row">

  <!-- ── Info card ─────────────────────────────────────────────────── -->
  <div class="col-lg-4">
    <div class="card card-primary card-outline">
      <div class="card-body text-center pt-4 pb-3">

        <div class="mb-3">
          <span class="fa-stack fa-3x text-primary">
            <i class="fas fa-circle fa-stack-2x" style="opacity:.1"></i>
            <i class="fas fa-user fa-stack-1x"></i>
          </span>
        </div>

        <h4 class="mb-0"><?= htmlspecialchars($collaborator['name']) ?></h4>
        <p class="text-muted mb-2"><?= htmlspecialchars($collaborator['position']) ?></p>

        <?php if (!empty($collaborator['area_name'])): ?>
          <p class="mb-2">
            <span class="badge badge-info px-3 py-1">
              <i class="fas fa-sitemap mr-1"></i>
              <?= htmlspecialchars($collaborator['area_name']) ?>
            </span>
          </p>
        <?php endif; ?>

        <span class="badge badge-<?= $isActive ? 'success' : 'secondary' ?> px-3 py-1">
          <?= $isActive ? 'Activo' : 'Egresado' ?>
        </span>

      </div>
      <div class="card-footer text-center">
        <a href="<?= APP_URL ?>/collaborators/<?= $collaborator['id'] ?>/edit"
           class="btn btn-sm btn-warning mr-2">
          <i class="fas fa-edit mr-1"></i> Editar
        </a>
        <a href="<?= APP_URL ?>/collaborators" class="btn btn-sm btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
      </div>
    </div>

    <!-- ── Linked user card ──────────────────────────────────────── -->
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-shield mr-2"></i>Usuario del sistema
        </h3>
      </div>
      <div class="card-body py-2 px-3">
        <?php if (!empty($collaborator['linked_user_email'])): ?>
          <p class="mb-1">
            <small class="text-muted d-block">Correo de acceso</small>
            <strong><?= htmlspecialchars($collaborator['linked_user_email']) ?></strong>
          </p>
          <p class="mb-0">
            <span class="badge badge-info">rol: usuario</span>
          </p>
        <?php else: ?>
          <p class="text-muted mb-0 small">
            <i class="fas fa-info-circle mr-1"></i>
            Este colaborador no tiene usuario vinculado.
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── Detail card ───────────────────────────────────────────────── -->
  <div class="col-lg-8">
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-id-card mr-2"></i>Información general
        </h3>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
          <tbody>
            <tr>
              <th class="pl-3" style="width:200px">Nombre</th>
              <td><?= htmlspecialchars($collaborator['name']) ?></td>
            </tr>
            <tr>
              <th class="pl-3">Puesto</th>
              <td><?= htmlspecialchars($collaborator['position']) ?></td>
            </tr>
            <tr>
              <th class="pl-3">País</th>
              <td>
                <?= !empty($collaborator['country'])
                    ? htmlspecialchars($collaborator['country'])
                    : '<span class="text-muted">—</span>' ?>
              </td>
            </tr>
            <tr>
              <th class="pl-3">Área</th>
              <td>
                <?= !empty($collaborator['area_name'])
                    ? htmlspecialchars($collaborator['area_name'])
                    : '<span class="text-muted">—</span>' ?>
              </td>
            </tr>
            <tr>
              <th class="pl-3">Fecha de ingreso</th>
              <td><?= $fmt($collaborator['entry_date']) ?></td>
            </tr>
            <tr>
              <th class="pl-3">Fecha de egreso</th>
              <td>
                <?php if ($collaborator['exit_date']): ?>
                  <?= $fmt($collaborator['exit_date']) ?>
                <?php else: ?>
                  <span class="text-muted">Sigue activo</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th class="pl-3">Registrado</th>
              <td><?= $fmt($collaborator['created_at']) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Equipment card ──────────────────────────────────────────── -->
    <div class="card card-outline card-secondary">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-laptop mr-2"></i>Equipo asignado
        </h3>
      </div>
      <div class="card-body">
        <?php if (!empty($collaborator['assigned_equipment'])): ?>
          <?php
            $items = array_filter(
                array_map('trim', explode(',', $collaborator['assigned_equipment']))
            );
          ?>
          <?php if (count($items) > 1): ?>
            <ul class="list-unstyled mb-0">
              <?php foreach ($items as $item): ?>
                <li><i class="fas fa-check-circle text-success mr-2"></i><?= htmlspecialchars($item) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="mb-0"><?= htmlspecialchars($collaborator['assigned_equipment']) ?></p>
          <?php endif; ?>
        <?php else: ?>
          <p class="text-muted mb-0">Sin equipo asignado.</p>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- ── Support tickets section ────────────────────────────────────── -->
<?php require BASE_PATH . '/views/supports/_badges.php'; ?>

<div class="row">
  <div class="col-12">
    <div class="card card-outline card-primary">
      <div class="card-header d-flex align-items-center">
        <h3 class="card-title">
          <i class="fas fa-headset mr-2"></i>Tickets de soporte
          <span class="badge badge-secondary ml-1"><?= count($tickets) ?></span>
        </h3>
        <div class="ml-auto">
          <a href="<?= APP_URL ?>/supports/create?collaborator_id=<?= $collaborator['id'] ?>"
             class="btn btn-sm btn-primary mr-1">
            <i class="fas fa-plus mr-1"></i> Nuevo ticket
          </a>
          <a href="<?= APP_URL ?>/supports?collaborator_id=<?= $collaborator['id'] ?>"
             class="btn btn-sm btn-secondary">
            <i class="fas fa-list mr-1"></i> Ver todos
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($tickets)): ?>
          <p class="text-muted p-3 mb-0">Este colaborador no tiene tickets registrados.</p>
        <?php else: ?>
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th class="pl-3">Título</th>
                <th>Nivel</th>
                <th>Estado</th>
                <th>Atendido por</th>
                <th>Fecha</th>
                <th class="text-center">Ver</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets as $t): ?>
              <tr>
                <td class="pl-3"><?= htmlspecialchars($t['title']) ?></td>
                <td><?= $levelBadgeHtml($t['attention_level']) ?></td>
                <td><?= $statusBadgeHtml($t['status']) ?></td>
                <td><?= $t['attended_by_name'] ? htmlspecialchars($t['attended_by_name']) : '<span class="text-muted">—</span>' ?></td>
                <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                <td class="text-center">
                  <a href="<?= APP_URL ?>/supports/<?= $t['id'] ?>"
                     class="btn btn-xs btn-secondary">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Delete section -->
<div class="row">
  <div class="col-12">
    <div class="card card-outline card-danger">
      <div class="card-header">
        <h3 class="card-title text-danger">
          <i class="fas fa-exclamation-triangle mr-2"></i>Zona peligrosa
        </h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
        <p class="mb-0 text-muted">
          Eliminar este colaborador lo marcará como eliminado y dejará de aparecer en los listados.
          Sus tickets de soporte se conservan intactos.
        </p>
        <form method="POST"
              action="<?= APP_URL ?>/collaborators/<?= $collaborator['id'] ?>/delete"
              class="form-delete-show">
          <?= \app\helpers\Csrf::field() ?>
          <input type="hidden" name="_confirm_token" value="<?= htmlspecialchars($deleteToken['token']) ?>">
          <input type="hidden" name="_confirm_word"  value="">
          <input type="hidden" name="_from"          value="show">
          <button type="submit" class="btn btn-danger btn-sm"
                  data-name="<?= htmlspecialchars($collaborator['name']) ?>"
                  data-word="<?= htmlspecialchars($deleteToken['word']) ?>">
            <i class="fas fa-trash mr-1"></i> Eliminar colaborador
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
    const $form = $(this);
    const btn   = $form.find('button');
    const name  = btn.data('name');
    const word  = btn.data('word');

    Swal.fire({
      icon:               'warning',
      title:              '¿Eliminar colaborador?',
      html:               `El colaborador "<strong>${name}</strong>" quedará marcado como eliminado y desaparecerá del listado.<br>Escribe <strong>${word}</strong> para confirmar.`,
      input:              'text',
      inputPlaceholder:   word,
      showCancelButton:   true,
      confirmButtonText:  'Eliminar',
      cancelButtonText:   'Cancelar',
      confirmButtonColor: '#e3342f',
      inputValidator: (value) => {
        if (!value || value.trim().toUpperCase() !== word) {
          return `Debes escribir exactamente: ${word}`;
        }
      },
    }).then(r => {
      if (r.isConfirmed) {
        $form.find('input[name="_confirm_word"]').val(r.value.trim().toUpperCase());
        $form.off('submit');
        $form[0].submit();
      }
    });
  });
});
</script>
