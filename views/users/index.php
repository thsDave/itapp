<?php
$pageTitle    = 'Usuarios';
$flash_notice = \app\helpers\Session::flash('notice');
$flash_error  = \app\helpers\Session::flash('error');
$selfId       = (int) \app\helpers\Session::get('user_id');

$roleBadge   = ['admin' => 'danger', 'consultant' => 'warning', 'user' => 'info'];
$statusBadge = [1 => 'success', 2 => 'secondary'];
$statusLabel = [1 => 'Activo',  2 => 'Inactivo'];
$roleLabel   = ['admin' => 'Admin', 'consultant' => 'Consultor', 'user' => 'Usuario'];
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
  <div class="col-12">
    <div class="card card-primary card-outline">

      <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">
          <i class="fas fa-user-cog mr-2"></i>Gestión de usuarios
        </h3>
        <div class="ml-auto">
          <a href="<?= APP_URL ?>/users/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Nuevo usuario
          </a>
        </div>
      </div>

      <div class="card-body">
        <table id="usersTable" class="table table-bordered table-hover table-sm w-100">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Creado</th>
              <th class="text-center no-sort">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $i => $u): ?>
            <tr id="row-<?= $u['id'] ?>">
              <td><?= $i + 1 ?></td>
              <td>
                <?= htmlspecialchars($u['name']) ?>
                <?php if ((int)$u['id'] === $selfId): ?>
                  <span class="badge badge-light ml-1">Tú</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <span class="badge badge-<?= $roleBadge[$u['role']] ?? 'secondary' ?>">
                  <?= $roleLabel[$u['role']] ?? $u['role'] ?>
                </span>
              </td>
              <td>
                <span class="badge badge-<?= $statusBadge[(int)$u['idstatus']] ?? 'secondary' ?>">
                  <?= $statusLabel[(int)$u['idstatus']] ?? htmlspecialchars($u['status_name']) ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
              <td class="text-center text-nowrap">

                <!-- Edit -->
                <a href="<?= APP_URL ?>/users/<?= $u['id'] ?>/edit"
                   class="btn btn-xs btn-info" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>

                <?php if ((int)$u['id'] !== $selfId): ?>

                  <!-- Toggle status -->
                  <form method="POST"
                        action="<?= APP_URL ?>/users/<?= $u['id'] ?>/toggle-status"
                        class="d-inline form-toggle">
                    <?= \app\helpers\Csrf::field() ?>
                    <button type="submit"
                            class="btn btn-xs <?= (int)$u['idstatus'] === 1 ? 'btn-warning' : 'btn-success' ?>"
                            title="<?= (int)$u['idstatus'] === 1 ? 'Desactivar' : 'Activar' ?>"
                            data-name="<?= htmlspecialchars($u['name']) ?>"
                            data-action="<?= (int)$u['idstatus'] === 1 ? 'desactivar' : 'activar' ?>">
                      <i class="fas fa-<?= (int)$u['idstatus'] === 1 ? 'ban' : 'check' ?>"></i>
                    </button>
                  </form>

                  <!-- Delete (soft) -->
                  <form method="POST"
                        action="<?= APP_URL ?>/users/<?= $u['id'] ?>/delete"
                        class="d-inline form-delete">
                    <?= \app\helpers\Csrf::field() ?>
                    <button type="submit"
                            class="btn btn-xs btn-danger"
                            title="Eliminar"
                            data-name="<?= htmlspecialchars($u['name']) ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>

                <?php else: ?>
                  <span class="text-muted small ml-1">—</span>
                <?php endif; ?>

              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div><!-- /.card-body -->

    </div><!-- /.card -->
  </div>
</div>

<script>
$(function () {

  $('#usersTable').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order:        [[0, 'asc']],
    pageLength:   10,
    lengthMenu:   [5, 10, 25, 50],
    columnDefs: [{ orderable: false, targets: 'no-sort' }],
    responsive:   true,
  });

  $(document).on('submit', '.form-toggle', function (e) {
    e.preventDefault();
    const $form  = $(this);
    const btn    = $form.find('button');
    const name   = btn.data('name');
    const action = btn.data('action');

    Swal.fire({
      icon:               'question',
      title:              `¿${action.charAt(0).toUpperCase() + action.slice(1)} usuario?`,
      text:               `Se ${action}á la cuenta de "${name}".`,
      showCancelButton:   true,
      confirmButtonText:  'Sí, continuar',
      cancelButtonText:   'Cancelar',
      confirmButtonColor: action === 'desactivar' ? '#f59e0b' : '#10b981',
    }).then(r => {
      if (r.isConfirmed) {
        $form.off('submit');
        $form[0].submit();
      }
    });
  });

  $(document).on('submit', '.form-delete', function (e) {
    const $form = $(this);
    if ($form.data('confirmed')) {
      $form.removeData('confirmed');
      return;
    }
    e.preventDefault();
    const name = $form.find('button').data('name');

    Swal.fire({
      icon:               'warning',
      title:              '¿Eliminar usuario?',
      html:               `La cuenta de "<strong>${name}</strong>" quedará desactivada y no podrá iniciar sesión.`,
      showCancelButton:   true,
      confirmButtonText:  'Sí, eliminar',
      cancelButtonText:   'Cancelar',
      confirmButtonColor: '#e3342f',
    }).then(r => {
      if (r.isConfirmed) {
        $form.data('confirmed', true);
        $form[0].submit();
      }
    });
  });

});
</script>
