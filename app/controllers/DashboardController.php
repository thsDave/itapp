<?php
declare(strict_types=1);

namespace app\controllers;

use app\helpers\View;
use app\models\Support;
use app\models\Collaborator;

class DashboardController
{
    private Support      $supportModel;
    private Collaborator $collabModel;

    private const MONTHS_ES = [
        '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
        '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
    ];

    public function __construct()
    {
        $this->supportModel = new Support();
        $this->collabModel  = new Collaborator();
    }

    public function index(): void
    {
        // ── KPI scalars ───────────────────────────────────────────────
        $collabStats  = $this->collabModel->countStats();
        $byStatus     = $this->supportModel->countByStatus();   // ['open'=>N,'in_progress'=>N,'closed'=>N]
        $byLevel      = $this->supportModel->countByLevel();    // ['low'=>N,'medium'=>N,'high'=>N,'critical'=>N]
        $supportTotal = array_sum($byStatus);

        // ── Monthly series (last 12 months, gap-filled) ───────────────
        [$monthLabels, $monthValues] = $this->buildMonthlySeries(
            $this->supportModel->perMonth(12)
        );

        // ── Top 5 collaborators of the current month ──────────────────
        $topCollaboratorsMonth = $this->supportModel->perCollaboratorTopCurrentMonth(5);

        View::render('dashboard/index', [
            'pageTitle' => 'Dashboard',

            // KPI cards
            'collabTotal'     => $collabStats['total'],
            'collabActive'    => $collabStats['active'],
            'collabInactive'  => $collabStats['inactive'],
            'supportTotal'    => $supportTotal,
            'supportOpen'     => (int) $byStatus['open'],
            'supportCritical' => (int) $byLevel['critical'],

            // Chart datasets — JSON strings consumed by dashboard.js
            'ticketsByStatus' => json_encode([
                'labels' => ['Abierto', 'En proceso', 'Cerrado'],
                'data'   => [
                    (int) $byStatus['open'],
                    (int) $byStatus['in_progress'],
                    (int) $byStatus['closed'],
                ],
            ], JSON_THROW_ON_ERROR),

            'ticketsByLevel' => json_encode([
                'labels' => ['Bajo', 'Medio', 'Alto', 'Crítico'],
                'data'   => [
                    (int) $byLevel['low'],
                    (int) $byLevel['medium'],
                    (int) $byLevel['high'],
                    (int) $byLevel['critical'],
                ],
            ], JSON_THROW_ON_ERROR),

            'ticketsByMonth' => json_encode([
                'labels' => $monthLabels,
                'data'   => $monthValues,
            ], JSON_THROW_ON_ERROR),

            // Top collaborators — raw array for the HTML table
            'topCollaboratorsMonth' => $topCollaboratorsMonth,

            // Pending urgent tickets table
            'recentOpen' => $this->supportModel->recentOpen(8),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────

    /**
     * Returns two parallel arrays (labels, values) for the last 12 months.
     * Months with no tickets are filled with zero so Chart.js always gets
     * a complete 12-point series.
     *
     * @param  array $rows  [['month' => 'YYYY-MM', 'total' => N], ...]
     * @return array        [string[] $labels, int[] $values]
     */
    private function buildMonthlySeries(array $rows): array
    {
        $skeleton = [];
        for ($i = 11; $i >= 0; $i--) {
            $key            = date('Y-m', strtotime("-{$i} months"));
            $skeleton[$key] = 0;
        }

        foreach ($rows as $row) {
            if (array_key_exists($row['month'], $skeleton)) {
                $skeleton[$row['month']] = (int) $row['total'];
            }
        }

        $labels = [];
        foreach (array_keys($skeleton) as $ym) {
            [$year, $month] = explode('-', $ym);
            $labels[] = (self::MONTHS_ES[$month] ?? $month) . ' ' . $year;
        }

        return [$labels, array_values($skeleton)];
    }
}
