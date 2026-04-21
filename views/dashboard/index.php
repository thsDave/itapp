<?php
$pageTitle = 'Dashboard';

$levelColor = [
    'low'      => '#17a2b8',
    'medium'   => '#ffc107',
    'high'     => '#fd7e14',
    'critical' => '#dc3545',
];
$levelLabel  = ['low' => 'Bajo', 'medium' => 'Medio', 'high' => 'Alto', 'critical' => 'Crítico'];
$statusLabel = ['open' => 'Abierto', 'in_progress' => 'En proceso', 'closed' => 'Cerrado'];

?>

<?php /* ── Inject chart datasets before dashboard.js loads ── */ ?>
<script>
window.DashboardData = {
    ticketsByStatus: <?= $ticketsByStatus ?>,
    ticketsByLevel:  <?= $ticketsByLevel ?>,
    ticketsByMonth:  <?= $ticketsByMonth ?>,
};
</script>


<!-- ══════════════════════════════════════════════════════════════════
     ROW 1 — KPI small-boxes
═══════════════════════════════════════════════════════════════════ -->
<div class="row">

  <div class="col-lg-2 col-md-4 col-sm-6">
    <div class="small-box bg-teal">
      <div class="inner">
        <h3><?= $collabTotal ?></h3>
        <p>Colaboradores</p>
      </div>
      <div class="icon"><i class="fas fa-users"></i></div>
      <a href="<?= APP_URL ?>/collaborators" class="small-box-footer">
        Ver todos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-2 col-md-4 col-sm-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?= $collabActive ?></h3>
        <p>Activos</p>
      </div>
      <div class="icon"><i class="fas fa-user-check"></i></div>
      <a href="<?= APP_URL ?>/collaborators" class="small-box-footer">
        Ver <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-2 col-md-4 col-sm-6">
    <div class="small-box bg-secondary">
      <div class="inner">
        <h3><?= $collabInactive ?></h3>
        <p>Egresados</p>
      </div>
      <div class="icon"><i class="fas fa-user-times"></i></div>
      <a href="<?= APP_URL ?>/collaborators" class="small-box-footer">
        Ver <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-2 col-md-4 col-sm-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3><?= $supportTotal ?></h3>
        <p>Tickets totales</p>
      </div>
      <div class="icon"><i class="fas fa-headset"></i></div>
      <a href="<?= APP_URL ?>/supports" class="small-box-footer">
        Ver todos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-2 col-md-4 col-sm-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3><?= $supportOpen ?></h3>
        <p>Tickets abiertos</p>
      </div>
      <div class="icon"><i class="fas fa-folder-open"></i></div>
      <a href="<?= APP_URL ?>/supports?status=open" class="small-box-footer">
        Ver abiertos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-2 col-md-4 col-sm-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3><?= $supportCritical ?></h3>
        <p>Tickets críticos</p>
      </div>
      <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
      <a href="<?= APP_URL ?>/supports?level=critical" class="small-box-footer">
        Ver críticos <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

</div><!-- /.row KPIs -->


<!-- ══════════════════════════════════════════════════════════════════
     ROW 2 — Status doughnut | Level bar | Recent urgent tickets
═══════════════════════════════════════════════════════════════════ -->
<div class="row">

  <!-- Tickets por estado — doughnut -->
  <div class="col-lg-4 col-md-6">
    <div class="card card-outline card-primary h-100">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-pie mr-2"></i>Tickets por estado
        </h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center"
           style="min-height:260px">
        <canvas id="ticketsByStatusChart" style="max-height:230px"></canvas>
      </div>
    </div>
  </div>

  <!-- Tickets por nivel — bar -->
  <div class="col-lg-4 col-md-6">
    <div class="card card-outline card-warning h-100">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-layer-group mr-2"></i>Tickets por nivel
        </h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center"
           style="min-height:260px">
        <canvas id="ticketsByLevelChart" style="max-height:230px"></canvas>
      </div>
    </div>
  </div>

  <!-- Pendientes urgentes -->
  <div class="col-lg-4 col-md-12">
    <div class="card card-outline card-danger h-100">
      <div class="card-header d-flex align-items-center">
        <h3 class="card-title">
          <i class="fas fa-fire mr-2 text-danger"></i>Pendientes urgentes
        </h3>
        <div class="ml-auto">
          <a href="<?= APP_URL ?>/supports?status=open"
             class="btn btn-xs btn-outline-secondary">Ver todos</a>
        </div>
      </div>
      <div class="card-body p-0" style="overflow-y:auto;max-height:260px">
        <?php if (empty($recentOpen)): ?>
          <p class="text-muted p-3 mb-0">No hay tickets abiertos.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($recentOpen as $t): ?>
            <?php
              $lvlColor = $levelColor[$t['attention_level']] ?? '#6c757d';
            ?>
            <li class="list-group-item px-3 py-2">
              <div class="d-flex align-items-start">
                <span class="badge mr-2 mt-1 flex-shrink-0"
                      style="background:<?= $lvlColor ?>;color:#fff;
                             min-width:52px;text-align:center">
                  <?= $levelLabel[$t['attention_level']] ?? $t['attention_level'] ?>
                </span>
                <div style="min-width:0">
                  <a href="<?= APP_URL ?>/supports/<?= $t['id'] ?>"
                     class="d-block text-truncate font-weight-bold text-dark"
                     title="<?= htmlspecialchars($t['title']) ?>">
                    <?= htmlspecialchars($t['title']) ?>
                  </a>
                  <small class="text-muted">
                    <?= htmlspecialchars($t['collaborator_name']) ?>
                    &middot; <?= date('d/m/Y', strtotime($t['created_at'])) ?>
                  </small>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div><!-- /.row 2 -->


<!-- ══════════════════════════════════════════════════════════════════
     ROW 3 — Monthly line chart | Top 5 collaborators table
═══════════════════════════════════════════════════════════════════ -->
<div class="row mt-3">
  <!-- Tickets por mes — line chart -->
  <div class="col-lg-8">
    <div class="card card-outline card-info">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-line mr-2"></i>Tickets por mes
          <small class="text-muted">(últimos 12 meses)</small>
        </h3>
      </div>
      <div class="card-body">
        <div class="dashboard-chart-wrapper">
          <canvas id="ticketsByMonthChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Top 5 colaboradores del mes -->
  <div class="col-lg-4">
    <div class="card card-outline card-success dashboard-top-card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-trophy mr-2"></i>Top colaboradores del mes
        </h3>
      </div>
      <div class="card-body p-0">
        <?php if (empty($topCollaboratorsMonth)): ?>
          <p class="text-muted p-3 mb-0">
            <i class="fas fa-inbox mr-1"></i>
            Sin tickets registrados este mes.
          </p>
        <?php else: ?>
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th class="pl-3" style="width:40px">#</th>
                <th>Colaborador</th>
                <th class="text-center" style="width:80px">Tickets</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($topCollaboratorsMonth as $rank => $row): ?>
              <tr>
                <td class="pl-3">
                  <?php if ($rank === 0): ?>
                    <span class="text-warning font-weight-bold">
                      <i class="fas fa-medal"></i>
                    </span>
                  <?php else: ?>
                    <span class="text-muted"><?= $rank + 1 ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td class="text-center">
                  <span class="badge badge-primary"><?= (int) $row['total'] ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
      <div class="card-footer text-muted small text-right py-1">
        <?= date('F Y') ?>
      </div>
    </div>
  </div>
</div>


<?php /* ── Load dashboard charts (must come after canvas elements) ── */ ?>
<script src="<?= APP_URL ?>/assets/js/dashboard.js"></script>
