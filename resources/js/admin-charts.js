import Chart from 'chart.js/auto';

function cssVar(name, fallback) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    return value || fallback;
}

function palette() {
    return {
        plum: cssVar('--plum', '#3D0A4B'),
        violet: cssVar('--violet', '#8B23A8'),
        deep: cssVar('--deep-purple', '#6D1580'),
        muted: cssVar('--muted', '#7A6A80'),
        blush: cssVar('--blush', '#E9C6CE'),
        rose: '#e11d48',
        emerald: '#059669',
        amber: '#d97706',
    };
}

function withAlpha(hex, alpha) {
    const clean = hex.replace('#', '');
    if (clean.length !== 6) {
        return hex;
    }

    const r = parseInt(clean.slice(0, 2), 16);
    const g = parseInt(clean.slice(2, 4), 16);
    const b = parseInt(clean.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function baseOptions(rtl) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                display: false,
                rtl,
                labels: { color: palette().muted, boxWidth: 12, font: { weight: '600' } },
            },
            tooltip: {
                rtl,
                backgroundColor: palette().plum,
                titleFont: { weight: '700' },
                bodyFont: { weight: '600' },
                padding: 10,
                cornerRadius: 10,
            },
        },
        scales: {
            x: {
                reverse: rtl,
                grid: { display: false },
                ticks: { color: palette().muted, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                border: { display: false },
            },
            y: {
                position: rtl ? 'right' : 'left',
                beginAtZero: true,
                grid: { color: withAlpha(palette().blush, 0.55) },
                ticks: { color: palette().muted },
                border: { display: false },
            },
        },
    };
}

function emptyState(canvas) {
    const wrap = canvas.closest('[data-chart-card]');
    if (!wrap) {
        return;
    }

    const empty = wrap.querySelector('[data-chart-empty]');
    const body = wrap.querySelector('[data-chart-body]');
    if (empty) {
        empty.classList.remove('hidden');
    }
    if (body) {
        body.classList.add('hidden');
    }
}

function createLine(canvas, labels, values, rtl) {
    if (!labels.length || values.every((v) => Number(v) === 0)) {
        emptyState(canvas);

        return;
    }

    const colors = palette();

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: colors.violet,
                backgroundColor: withAlpha(colors.violet, 0.12),
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: colors.plum,
                fill: true,
                tension: 0.35,
            }],
        },
        options: baseOptions(rtl),
    });
}

function createBar(canvas, labels, values, rtl, color) {
    if (!labels.length) {
        emptyState(canvas);

        return;
    }

    const colors = palette();
    const fill = color || colors.deep;
    const horizontal = labels.length > 4;
    const options = baseOptions(rtl);

    if (horizontal) {
        options.indexAxis = 'y';
        options.scales = {
            x: {
                beginAtZero: true,
                reverse: false,
                grid: { color: withAlpha(colors.blush, 0.55) },
                ticks: { color: colors.muted },
                border: { display: false },
            },
            y: {
                reverse: rtl,
                grid: { display: false },
                ticks: { color: colors.muted },
                border: { display: false },
            },
        };
    }

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: withAlpha(fill, 0.78),
                hoverBackgroundColor: fill,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 42,
            }],
        },
        options,
    });
}

function createDoughnut(canvas, labels, values, rtl) {
    if (!labels.length) {
        emptyState(canvas);

        return;
    }

    const colors = palette();
    const fills = [
        colors.violet,
        colors.deep,
        colors.plum,
        colors.amber,
        colors.emerald,
        colors.rose,
        withAlpha(colors.violet, 0.55),
        withAlpha(colors.deep, 0.55),
    ];

    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: fills.slice(0, labels.length),
                borderWidth: 0,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl,
                    labels: {
                        color: colors.muted,
                        boxWidth: 10,
                        padding: 14,
                        font: { weight: '600', size: 11 },
                    },
                },
                tooltip: {
                    rtl,
                    backgroundColor: colors.plum,
                    padding: 10,
                    cornerRadius: 10,
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('admin-analytics');
    if (!root) {
        return;
    }

    let payload = {};
    try {
        payload = JSON.parse(root.dataset.analytics || '{}');
    } catch {
        payload = {};
    }

    const rtl = document.documentElement.dir === 'rtl';
    const colors = palette();

    const salesTrend = root.querySelector('#chart-sales-trend');
    if (salesTrend) {
        createLine(salesTrend, payload.salesTrend?.labels || [], payload.salesTrend?.values || [], rtl);
    }

    const topProducts = root.querySelector('#chart-top-products');
    if (topProducts) {
        createBar(topProducts, payload.topProducts?.labels || [], payload.topProducts?.revenues || [], rtl, colors.violet);
    }

    const topCustomers = root.querySelector('#chart-top-customers');
    if (topCustomers) {
        createDoughnut(topCustomers, payload.topCustomers?.labels || [], payload.topCustomers?.revenues || [], rtl);
    }

    const topItems = root.querySelector('#chart-top-items');
    if (topItems) {
        createBar(topItems, payload.topItems?.labels || [], payload.topItems?.quantities || [], rtl, colors.deep);
    }

    const restock = root.querySelector('#chart-restock');
    if (restock) {
        createBar(restock, payload.restock?.labels || [], payload.restock?.values || [], rtl, colors.rose);
    }
});
