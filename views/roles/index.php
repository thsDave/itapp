<?php
$pageTitle    = 'Roles y Accesos';
$flash_notice = \app\helpers\Session::flash('notice');
$flash_error  = \app\helpers\Session::flash('error');
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
          <i class="fas fa-shield-alt mr-2"></i>Gestión de roles y accesos
        </h3>
        <div class="ml-auto">
          <a href="<?= APP_URL ?>/roles/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Nuevo rol
          </a>
        </div>
      </div>

      <div class="card-body">
        <table id="rolesTable" class="table table-bordered table-hover table-sm w-100">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Nombre del rol</th>
              <th>Descripción</th>
              <th class="text-center">Usuarios</th>
              <th>Creado</th>
              <th class="text-center no-sort">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($roles as $i => $r): ?>
            <?php $isProtected = $r['role_name'] === \app\models\Role::PROTECTED_ROLE; ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <span class="font-weight-bold">
                  <?= htmlspecialchars($r['role_name']) ?>
                </span>
                <?php if ($isProtected): ?>
                  <span class="badge badge-danger ml-1" title="Este rol no puede editarse ni eliminarse">
                    <i class="fas fa-lock mr-1"></i>protegido
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-muted">
                <?= $r['description'] ? htmlspecialchars($r['description']) : '<em class="text-muted">—</em>' ?>
              </td>
              <td class="text-center">
                <span class="badge badge-<?= (int)$r['user_count'] > 0 ? 'info' : 'secondary' ?>">
                  <?= (int)$r['user_count'] ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
              <td class="text-center text-nowrap">

                <?php if (!$isProtected): ?>
                  <a href="<?= APP_URL ?>/roles/<?= $r['idrole'] ?>/edit"
                     class="btn btn-xs btn-info" title="Editar rol y permisos">
                    <i class="fas fa-edit"></i>
                  </a>

                  <form method="POST"
                        action="<?= APP_URL ?>/roles/<?= $r['idrole'] ?>/delete"
                        class="d-inline form-delete-role">
                    <?= \app\helpers\Csrf::field() ?>
                    <button type="submit"
                            class="btn btn-xs btn-danger"
                            title="Eliminar rol"
                            data-name="<?= htmlspecialchars($r['role_name']) ?>"
                            data-users="<?= (int)$r['user_count'] ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>

              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<script>
$(function () {

  $('#rolesTable').DataTable({
    language:   { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order:      [[0, 'asc']],
    pageLength: 10,
    columnDefs: [{ orderable: false, targets: 'no-sort' }],
    responsive: true,
  });

  $(document).on('submit', '.form-delete-role', function (e) {
    const $form    = $(this);
    if ($form.data('confirmed')) {
      $form.removeData('confirmed');
      return;
    }
    e.preventDefault();
    const btn      = $form.find('button');
    const name     = btn.data('name');
    const users    = parseInt(btn.data('users'), 10);

    if (users > 0) {
      Swal.fire({
        icon:  'warning',
        title: 'No se puede eliminar',
        html:  `El rol <strong>${name}</strong> tiene <strong>${users}</strong> usuario(s) asignado(s).<br>Reasigna los usuarios antes de eliminar el rol.`,
        confirmButtonColor: '#3085d6',
      });
      return;
    }

    Swal.fire({
      icon:               'warning',
      title:              '¿Eliminar rol?',
      html:               `Se eliminará el rol <strong>${name}</strong> y toda su configuración de acceso.`,
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
