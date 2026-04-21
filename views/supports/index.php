<?php
$pageTitle    = 'Soportes técnicos';
$flash_notice = \app\helpers\Session::flash('notice');
$flash_error  = \app\helpers\Session::flash('error');

require __DIR__ . '/_badges.php';

// Detect if any filter is currently active (to auto-expand the panel)
$hasFilters = array_filter(array_map('strval', array_values($filters)));
?>

<?php if ($flash_notice): ?>
<script>
  document.addEventListener('DOMContentLoaded', () =>
    Swal.fire({ icon:'success', title:'Listo', text: <?= json_encode($flash_notice) ?>,
                timer:2500, showConfirmButton:false }));
</script>
<?php endif; ?>

<?php if ($flash_error): ?>
<script>
  document.addEventListener('DOMContentLoaded', () =>
    Swal.fire({ icon:'error', title:'Error', text: <?= json_encode($flash_error) ?>,
                confirmButtonColor:'#e3342f' }));
</script>
<?php endif; ?>

<!-- ── Filter panel ───────────────────────────────────────────────── -->
<div class="card card-secondary card-outline collapsed-card <?= $hasFilters ? 'show' : '' ?>" id="filterCard">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-filter mr-2"></i>Filtros
      <?php if ($hasFilters): ?>
        <span class="badge badge-warning ml-1">Activos</span>
      <?php endif; ?>
    </h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-<?= $hasFilters ? 'minus' : 'plus' ?>"></i>
      </button>
    </div>
  </div>

  <div class="card-body <?= $hasFilters ? '' : 'd-none' ?>" id="filterBody">
    <form action="<?= APP_URL ?>/supports" method="GET" id="filterForm">
      <div class="row align-items-end">

        <div class="col-md-3 col-sm-6">
          <div class="form-group mb-2">
            <label class="small mb-1">Colaborador</label>
            <select name="collaborator_id" class="form-control select2"
                    data-placeholder="Todos">
              <option value="">Todos</option>
              <?php foreach ($collaborators as $c): ?>
                <option value="<?= $c['id'] ?>"
                  <?= (int)($filters['collaborator_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-2 col-sm-6">
          <div class="form-group mb-2">
            <label class="small mb-1">Nivel</label>
            <select name="level" class="form-control select2"
                    data-placeholder="Todos" data-minimum-results-for-search="-1">
              <option value="">Todos</option>
              <?php
                $levelLabel = ['low'=>'Bajo','medium'=>'Medio','high'=>'Alto','critical'=>'Crítico'];
                foreach (\app\models\Support::LEVELS as $lv):
              ?>
                <option value="<?= $lv ?>"
                  <?= ($filters['level'] ?? '') === $lv ? 'selected' : '' ?>>
                  <?= $levelLabel[$lv] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-2 col-sm-6">
          <div class="form-group mb-2">
            <label class="small mb-1">Estado</label>
            <select name="status" class="form-control select2"
                    data-placeholder="Todos" data-minimum-results-for-search="-1">
              <option value="">Todos</option>
              <?php
                $statusLabel = ['open'=>'Abierto','in_progress'=>'En proceso','closed'=>'Cerrado'];
                foreach (\app\models\Support::STATUSES as $st):
              ?>
                <option value="<?= $st ?>"
                  <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>>
                  <?= $statusLabel[$st] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-2 col-sm-6">
          <div class="form-group mb-2">
            <label class="small mb-1">Desde</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
          </div>
        </div>

        <div class="col-md-2 col-sm-6">
          <div class="form-group mb-2">
            <label class="small mb-1">Hasta</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
          </div>
        </div>

        <div class="col-md-1 col-sm-12">
          <div class="form-group mb-2 d-flex">
            <button type="submit" class="btn btn-primary btn-sm mr-1" title="Aplicar">
              <i class="fas fa-search"></i>
            </button>
            <a href="<?= APP_URL ?>/supports" class="btn btn-secondary btn-sm" title="Limpiar">
              <i class="fas fa-times"></i>
            </a>
          </div>
        </div>

      </div><!-- /.row -->
    </form>
  </div>
</div>

<!-- ── Main table card ────────────────────────────────────────────── -->
<div class="card card-primary card-outline">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title mb-0">
      <i class="fas fa-headset mr-2"></i>Tickets
      <span class="badge badge-secondary ml-1"><?= count($supports) ?></span>
    </h3>
    <div class="ml-auto">
      <a href="<?= APP_URL ?>/supports/create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo ticket
      </a>
    </div>
  </div>

  <div class="card-body">
    <table id="supportsTable" class="table table-bordered table-hover table-sm w-100">
      <thead class="thead-light">
        <tr>
          <th>#</th>
          <th>Colaborador</th>
          <th>Título</th>
          <th>Nivel</th>
          <th>Estado</th>
          <th>Atendido por</th>
          <th>Fecha</th>
          <th class="text-center no-sort">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($supports as $i => $s): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <div class="font-weight-bold"><?= htmlspecialchars($s['collaborator_name']) ?></div>
            <small class="text-muted"><?= htmlspecialchars($s['collaborator_position']) ?></small>
          </td>
          <td>
            <a href="<?= APP_URL ?>/supports/<?= $s['id'] ?>" class="text-dark">
              <?= htmlspecialchars($s['title']) ?>
            </a>
          </td>
          <td data-order="<?= array_search($s['attention_level'], ['low','medium','high','critical']) ?>">
            <?= $levelBadgeHtml($s['attention_level']) ?>
          </td>
          <td><?= $statusBadgeHtml($s['status']) ?></td>
          <td>
            <?= $s['attended_by_name']
                ? htmlspecialchars($s['attended_by_name'])
                : '<span class="text-muted">—</span>' ?>
          </td>
          <td data-order="<?= $s['created_at'] ?>">
            <?= date('d/m/Y', strtotime($s['created_at'])) ?>
          </td>
          <td class="text-center text-nowrap">
            <a href="<?= APP_URL ?>/supports/<?= $s['id'] ?>"
               class="btn btn-xs btn-secondary" title="Ver">
              <i class="fas fa-eye"></i>
            </a>
            <a href="<?= APP_URL ?>/supports/<?= $s['id'] ?>/edit"
               class="btn btn-xs btn-info" title="Editar">
              <i class="fas fa-edit"></i>
            </a>
            <form method="POST"
                  action="<?= APP_URL ?>/supports/<?= $s['id'] ?>/delete"
                  class="d-inline form-delete">
              <?= \app\helpers\Csrf::field() ?>
              <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"
                      data-title="<?= htmlspecialchars($s['title']) ?>">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
$(function () {

  // ── DataTable ────────────────────────────────────────────────────
  $('#supportsTable').DataTable({
    language:   { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order:      [[6, 'desc']],
    pageLength: 15,
    lengthMenu: [10, 15, 25, 50],
    columnDefs: [{ orderable: false, targets: 'no-sort' }],
    responsive: true,
  });

  // ── Filter card toggle sync ──────────────────────────────────────
  $('#filterCard').on('expanded.lte.cardwidget', function () {
    $('#filterBody').removeClass('d-none');
  });

  // ── Delete confirm ───────────────────────────────────────────────
  $(document).on('submit', '.form-delete', function (e) {
    e.preventDefault();
    const form  = this;
    const title = $(form).find('button').data('title');

    Swal.fire({
      icon:               'warning',
      title:              '¿Eliminar ticket?',
      html:               `Esta acción es <strong>irreversible</strong>.<br>"<strong>${title}</strong>"`,
      showCancelButton:   true,
      confirmButtonText:  'Sí, eliminar',
      cancelButtonText:   'Cancelar',
      confirmButtonColor: '#e3342f',
    }).then(r => { if (r.isConfirmed) form.submit(); });
  });

});
</script>
