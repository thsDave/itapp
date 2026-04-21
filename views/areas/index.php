<?php
$pageTitle    = 'Áreas institucionales';
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
          <i class="fas fa-sitemap mr-2"></i>Áreas institucionales
          <span class="badge badge-secondary ml-1"><?= count($areas) ?></span>
        </h3>
        <div class="ml-auto">
          <a href="<?= APP_URL ?>/areas/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Nueva área
          </a>
        </div>
      </div>

      <div class="card-body">
        <table id="areasTable"
               class="table table-bordered table-hover table-sm w-100">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Registrada</th>
              <th class="text-center no-sort">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($areas as $i => $a): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td class="font-weight-bold"><?= htmlspecialchars($a['name']) ?></td>
              <td data-order="<?= $a['created_at'] ?>">
                <?= date('d/m/Y', strtotime($a['created_at'])) ?>
              </td>
              <td class="text-center text-nowrap">

                <a href="<?= APP_URL ?>/areas/<?= $a['id'] ?>/edit"
                   class="btn btn-xs btn-info" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>

                <form method="POST"
                      action="<?= APP_URL ?>/areas/<?= $a['id'] ?>/delete"
                      class="d-inline form-delete-area">
                  <?= \app\helpers\Csrf::field() ?>
                  <button type="submit"
                          class="btn btn-xs btn-danger"
                          title="Eliminar"
                          data-name="<?= htmlspecialchars($a['name']) ?>">
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
  </div>
</div>

<script>
$(function () {

  $('#areasTable').DataTable({
    language:   { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order:      [[1, 'asc']],
    pageLength: 15,
    lengthMenu: [10, 15, 25, 50],
    columnDefs: [{ orderable: false, targets: 'no-sort' }],
    responsive: true,
  });

  $(document).on('submit', '.form-delete-area', function (e) {
    e.preventDefault();
    const form = this;
    const name = $(form).find('button').data('name');

    Swal.fire({
      icon:               'warning',
      title:              '¿Eliminar área?',
      html:               `El área "<strong>${name}</strong>" será eliminada.<br>
                           <small class="text-muted">Los colaboradores asignados quedarán sin área.</small>`,
      showCancelButton:   true,
      confirmButtonText:  'Sí, eliminar',
      cancelButtonText:   'Cancelar',
      confirmButtonColor: '#e3342f',
    }).then(r => { if (r.isConfirmed) form.submit(); });
  });

});
</script>
