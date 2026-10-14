import Chart from 'chart.js/auto';

function readPayload(id) {
    const el = document.getElementById(id);
    if (!el?.textContent?.trim()) {
        return null;
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function destroyChartOnCanvas(canvas) {
    const existing = Chart.getChart(canvas);
    if (existing) {
        existing.destroy();
    }
}

/**
 * Horizontal grouped bars: AE vs CP by wilaya (M DA).
 *
 * @param {HTMLCanvasElement} canvas
 * @param {{ labels: string[], ae_m: number[], cp_m: number[], operations: number[] }} wilaya
 */
function createWilayaBarChart(canvas, wilaya) {
    destroyChartOnCanvas(canvas);

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: wilaya.labels,
            datasets: [
                {
                    label: 'AE (M DA)',
                    data: wilaya.ae_m,
                    backgroundColor: 'rgba(4, 120, 87, 0.82)',
                    borderColor: '#047857',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.85,
                    categoryPercentage: 0.75,
                },
                {
                    label: 'CP (M DA)',
                    data: wilaya.cp_m,
                    backgroundColor: 'rgba(99, 102, 241, 0.55)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.85,
                    categoryPercentage: 0.75,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { font: { size: 10 } },
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, autoSkip: false },
                },
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 12, usePointStyle: true },
                },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            return ` ${ctx.dataset.label}: ${ctx.raw} M DA`;
                        },
                        afterLabel(ctx) {
                            const i = ctx.dataIndex;
                            const n = wilaya.operations?.[i];
                            if (n == null) {
                                return '';
                            }
                            return ` Operations: ${n}`;
                        },
                    },
                },
            },
        },
    });
}

const gridColor = 'rgba(15, 23, 42, 0.06)';
const fontStack = "'Plus Jakarta Sans', 'DM Sans', system-ui, sans-serif";

/** Abort previous region-select listeners when Livewire re-inits charts */
let overviewWilayaRegionAbort = null;
let financialWilayaRegionAbort = null;

Chart.defaults.font.family = fontStack;
Chart.defaults.color = '#64748b';

function initOverviewCharts() {
    const payload = readPayload('chart-payload-overview');
    if (!payload) {
        return;
    }

    const spuCanvas = document.getElementById('chart-overview-spu');
    if (spuCanvas && payload.spuPie) {
        destroyChartOnCanvas(spuCanvas);
        new Chart(spuCanvas, {
            type: 'doughnut',
            data: {
                labels: payload.spuPie.labels,
                datasets: [
                    {
                        data: payload.spuPie.data,
                        backgroundColor: ['#059669', '#d97706', '#dc2626'],
                        hoverBackgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 12, usePointStyle: true },
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const v = ctx.raw ?? 0;
                                const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = sum ? ((v / sum) * 100).toFixed(1) : '0';
                                return `${ctx.label}: ${v} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    const envCanvas = document.getElementById('chart-overview-envelope');
    if (envCanvas && payload.envelopePie) {
        destroyChartOnCanvas(envCanvas);
        new Chart(envCanvas, {
            type: 'pie',
            data: {
                labels: payload.envelopePie.labels,
                datasets: [
                    {
                        data: payload.envelopePie.data,
                        backgroundColor: ['#047857', '#6366f1', '#f59e0b'],
                        hoverBackgroundColor: ['#10b981', '#818cf8', '#fbbf24'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 10, usePointStyle: true },
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const label = ctx.label || '';
                                const val = ctx.raw ?? 0;
                                return ` ${label}: ${val} M DA`;
                            },
                        },
                    },
                },
            },
        });
    }

    const lineCanvas = document.getElementById('chart-overview-monthly');
    if (lineCanvas && payload.monthlyLine) {
        const m = payload.monthlyLine;
        destroyChartOnCanvas(lineCanvas);
        new Chart(lineCanvas, {
            type: 'line',
            data: {
                labels: m.labels,
                datasets: [
                    {
                        label: 'CP (M DA)',
                        data: m.cp_m,
                        borderColor: '#047857',
                        backgroundColor: 'rgba(4, 120, 87, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'AE engaged (M DA)',
                        data: m.ae_engaged_m,
                        borderColor: '#6366f1',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        fill: false,
                        borderDash: [6, 4],
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 0, font: { size: 10 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { font: { size: 10 } },
                    },
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 12, usePointStyle: true },
                    },
                },
            },
        });
    }

    const overviewWilaya = document.getElementById('chart-overview-wilaya');
    const overviewRegionSelect = document.getElementById('chart-region-select-overview');
    const regions = payload.wilayaByRegion;
    if (overviewWilaya && overviewRegionSelect && Array.isArray(regions) && regions.length) {
        if (overviewWilayaRegionAbort) {
            overviewWilayaRegionAbort.abort();
        }
        overviewWilayaRegionAbort = new AbortController();
        const { signal } = overviewWilayaRegionAbort;
        const drawWilaya = () => {
            const idx = parseInt(overviewRegionSelect.value, 10);
            const r = regions[Number.isFinite(idx) ? idx : 0] ?? regions[0];
            if (r) {
                createWilayaBarChart(overviewWilaya, {
                    labels: r.labels,
                    ae_m: r.ae_m,
                    cp_m: r.cp_m,
                    operations: r.operations,
                });
            }
        };
        overviewRegionSelect.addEventListener('change', drawWilaya, { signal });
        drawWilaya();
    }
}

function initFinancialCharts() {
    const payload = readPayload('chart-payload-financial');
    if (!payload) {
        return;
    }

    const barCanvas = document.getElementById('chart-financial-monthly');
    if (barCanvas && payload.monthly) {
        const m = payload.monthly;
        destroyChartOnCanvas(barCanvas);

        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: m.labels,
                datasets: [
                    {
                        label: 'CP consumed (M DA)',
                        data: m.cp_m,
                        backgroundColor: 'rgba(4, 120, 87, 0.78)',
                        borderColor: '#047857',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'AE engaged monthly (M DA)',
                        data: m.ae_engaged_m,
                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 0, font: { size: 10 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { font: { size: 10 } },
                    },
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 12, usePointStyle: true },
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                return ` ${ctx.dataset.label}: ${ctx.raw} M DA`;
                            },
                        },
                    },
                },
            },
        });
    }

    const financialWilaya = document.getElementById('chart-financial-wilaya');
    const financialRegionSelect = document.getElementById('chart-region-select-financial');
    const regions = payload.wilayaByRegion;
    if (financialWilaya && financialRegionSelect && Array.isArray(regions) && regions.length) {
        if (financialWilayaRegionAbort) {
            financialWilayaRegionAbort.abort();
        }
        financialWilayaRegionAbort = new AbortController();
        const { signal } = financialWilayaRegionAbort;
        const drawWilaya = () => {
            const idx = parseInt(financialRegionSelect.value, 10);
            const r = regions[Number.isFinite(idx) ? idx : 0] ?? regions[0];
            if (r) {
                createWilayaBarChart(financialWilaya, {
                    labels: r.labels,
                    ae_m: r.ae_m,
                    cp_m: r.cp_m,
                    operations: r.operations,
                });
            }
        };
        financialRegionSelect.addEventListener('change', drawWilaya, { signal });
        drawWilaya();
    }
}

export function initDashboardCharts() {
    initOverviewCharts();
    initFinancialCharts();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}

document.addEventListener('livewire:navigated', initDashboardCharts);
