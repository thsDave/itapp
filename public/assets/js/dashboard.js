'use strict';

/**
 * Dashboard charts — Chart.js 4.x
 *
 * Execution order:
 *  1. This file is linked at the bottom of views/dashboard/index.php.
 *     When parsed, it registers a DOMContentLoaded listener and returns.
 *  2. The browser continues loading main.php footer scripts
 *     (jQuery → Bootstrap → AdminLTE → DataTables → Chart.js).
 *  3. DOMContentLoaded fires *after* all blocking scripts are executed,
 *     so Chart is guaranteed to be defined when the callback runs.
 */
document.addEventListener('DOMContentLoaded', function () {

    // ── Guard: data must be injected by the view ──────────────────────
    if (typeof window.DashboardData === 'undefined') {
        console.warn('DashboardData not found — charts will not render.');
        return;
    }

    var data = window.DashboardData;

    // Shared defaults
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Source Sans Pro', sans-serif";
        Chart.defaults.font.size   = 12;
    }

    // ── Helper: get canvas context safely ────────────────────────────
    function ctx(id) {
        var el = document.getElementById(id);
        return el ? el.getContext('2d') : null;
    }

    // ── Helper: true if every value in an array is zero ──────────────
    function allZero(arr) {
        return Array.isArray(arr) && arr.every(function (v) { return v === 0; });
    }

    // ── Helper: show a "no data" message inside a card-body ──────────
    function showEmpty(canvasId, message) {
        var el = document.getElementById(canvasId);
        if (!el) return;
        el.style.display = 'none';
        var msg = document.createElement('p');
        msg.className   = 'text-muted text-center mt-3';
        msg.textContent = message || 'Sin datos disponibles.';
        el.parentNode.insertBefore(msg, el);
    }


    /* ─────────────────────────────────────────────────────────────────
     * 1. TICKETS POR ESTADO — Doughnut
     * ──────────────────────────────────────────────────────────────── */
    (function () {
        var canvasId = 'ticketsByStatusChart';
        var c        = ctx(canvasId);
        if (!c) return;

        var d = data.ticketsByStatus;
        if (!d || allZero(d.data)) {
            showEmpty(canvasId, 'Sin tickets registrados.');
            return;
        }

        new Chart(c, {
            type: 'doughnut',
            data: {
                labels:   d.labels,
                datasets: [{
                    data:            d.data,
                    backgroundColor: ['#6c757d', '#007bff', '#28a745'],
                    borderColor:     '#ffffff',
                    borderWidth:     3,
                    hoverOffset:     6,
                }],
            },
            options: {
                cutout:     '68%',
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.label + ': ' + context.parsed;
                            }
                        }
                    }
                }
            },
        });
    }());


    /* ─────────────────────────────────────────────────────────────────
     * 2. TICKETS POR NIVEL — Vertical bar
     * ──────────────────────────────────────────────────────────────── */
    (function () {
        var canvasId = 'ticketsByLevelChart';
        var c        = ctx(canvasId);
        if (!c) return;

        var d = data.ticketsByLevel;
        if (!d || allZero(d.data)) {
            showEmpty(canvasId, 'Sin tickets registrados.');
            return;
        }

        new Chart(c, {
            type: 'bar',
            data: {
                labels:   d.labels,
                datasets: [{
                    label:           'Tickets',
                    data:            d.data,
                    backgroundColor: ['#17a2b8', '#ffc107', '#fd7e14', '#dc3545'],
                    borderRadius:    4,
                    borderSkipped:   false,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid:  { color: 'rgba(0,0,0,.05)' },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }());


    /* ─────────────────────────────────────────────────────────────────
     * 3. TICKETS POR MES — Line with gradient fill
     * ──────────────────────────────────────────────────────────────── */
    (function () {
        var canvasId = 'ticketsByMonthChart';
        var c = ctx(canvasId);
        if (!c) return;

        var d = data.ticketsByMonth;
        if (!d || allZero(d.data)) {
            showEmpty(canvasId, 'Sin tickets en los últimos 12 meses.');
            return;
        }

        var canvas = document.getElementById(canvasId);
        var grad = c.createLinearGradient(0, 0, 0, 260);
        grad.addColorStop(0, 'rgba(0,123,255,0.35)');
        grad.addColorStop(1, 'rgba(0,123,255,0.00)');

        new Chart(c, {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [{
                    label: 'Tickets',
                    data: d.data,
                    borderColor: '#007bff',
                    backgroundColor: grad,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#007bff',
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: 'rgba(0,0,0,.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }());

});
