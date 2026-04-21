<?php
$pageTitle    = 'Colaboradores';
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
          <i class="fas fa-users mr-2"></i>Gestión de colaboradores
        </h3>
        <div class="ml-auto">
          <a href="<?= APP_URL ?>/collaborators/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Nuevo colaborador
          </a>
        </div>
      </div>

      <div class="card-body">
        <table id="collaboratorsTable"
               class="table table-bordered table-hover table-sm w-100">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Puesto</th>
              <th>Fecha ingreso</th>
              <th>Fecha egreso</th>
              <th>Estado</th>
              <th class="text-center no-sort">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($collaborators as $i => $c): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($c['name']) ?></td>
              <td><?= htmlspecialchars($c['position']) ?></td>
              <td data-order="<?= $c['entry_date'] ?>">
                <?= $c['entry_date'] ? date('d/m/Y', strtotime($c['entry_date'])) : '—' ?>
              </td>
              <td data-order="<?= $c['exit_date'] ?? '9999-99-99' ?>">
                <?= $c['exit_date'] ? date('d/m/Y', strtotime($c['exit_date'])) : '—' ?>
              </td>
              <td>
                <?php if ($c['is_active']): ?>
                  <span class="badge badge-success">Activo</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Egresado</span>
                <?php endif; ?>
              </td>
              <td class="text-center text-nowrap">

                <!-- View -->
                <a href="<?= APP_URL ?>/collaborators/<?= $c['id'] ?>"
                   class="btn btn-xs btn-secondary" title="Ver detalle">
                  <i class="fas fa-eye"></i>
                </a>

                <!-- Edit -->
                <a href="<?= APP_URL ?>/collaborators/<?= $c['id'] ?>/edit"
                   class="btn btn-xs btn-info" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>

                <!-- Delete -->
                <form method="POST"
                      action="<?= APP_URL ?>/collaborators/<?= $c['id'] ?>/delete"
                      class="d-inline form-delete">
                  <?= \app\helpers\Csrf::field() ?>
                  <input type="hidden" name="_confirm_token" value="<?= htmlspecialchars($deleteTokens[(int)$c['id']]['token']) ?>">
                  <input type="hidden" name="_confirm_word"  value="">
                  <input type="hidden" name="_from"          value="list">
                  <button type="submit"
                          class="btn btn-xs btn-danger"
                          title="Eliminar"
                          data-name="<?= htmlspecialchars($c['name']) ?>"
                          data-word="<?= htmlspecialchars($deleteTokens[(int)$c['id']]['word']) ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>

              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div><!-- /.card-body -->

    </div>
  </div>
</div>

<script>
$(function () {

  $('#collaboratorsTable').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order:      [[3, 'desc']],
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    columnDefs: [{ orderable: false, targets: 'no-sort' }],
    responsive: true,
  });

  $(document).on('submit', '.form-delete', function (e) {
    const $form = $(this);

    // Second pass after Swal confirmation — let the native submit go through.
    if ($form.data('swal-confirmed')) {
      $form.removeData('swal-confirmed');
      return;
    }

    e.preventDefault();
    const btn  = $form.find('button');
    const name = btn.data('name');
    const word = btn.data('word');

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
        $form.data('swal-confirmed', true); // flag guards re-entry on delegated handler
        $form[0].submit();                  // call native HTMLFormElement.submit() directly
      }
    });
  });

});
</script>
