<?php
/**
 * Shared badge maps for attention_level and status.
 * Include this file in any support view that needs badge rendering.
 */

$levelBadge = [
    'low'      => 'info',
    'medium'   => 'warning',
    'high'     => 'orange',   // custom style defined inline
    'critical' => 'danger',
];

$levelLabel = [
    'low'      => 'Bajo',
    'medium'   => 'Medio',
    'high'     => 'Alto',
    'critical' => 'Crítico',
];

$statusBadge = [
    'open'        => 'secondary',
    'in_progress' => 'primary',
    'closed'      => 'success',
];

$statusLabel = [
    'open'        => 'Abierto',
    'in_progress' => 'En proceso',
    'closed'      => 'Cerrado',
];

/**
 * @param string $level   attention_level value
 * @param array  $badge   $levelBadge map
 * @param array  $label   $levelLabel map
 */
$levelBadgeHtml = function (string $level) use ($levelBadge, $levelLabel): string {
    if ($level === 'high') {
        return '<span class="badge" style="background:#fd7e14;color:#fff;">'
             . htmlspecialchars($levelLabel[$level] ?? $level)
             . '</span>';
    }
    $cls = $levelBadge[$level] ?? 'secondary';
    return '<span class="badge badge-' . $cls . '">'
         . htmlspecialchars($levelLabel[$level] ?? $level)
         . '</span>';
};

$statusBadgeHtml = function (string $status) use ($statusBadge, $statusLabel): string {
    $cls = $statusBadge[$status] ?? 'secondary';
    return '<span class="badge badge-' . $cls . '">'
         . htmlspecialchars($statusLabel[$status] ?? $status)
         . '</span>';
};
