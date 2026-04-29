import Chart from 'chart.js/auto';

const PRIMARY = '#0d6efd';
const PRIMARY_FILL_TOP = 'rgba(13, 110, 253, 0.22)';
const PRIMARY_FILL_BOT = 'rgba(13, 110, 253, 0.02)';

/** Line chart for admin Shop insights — only bundled when dynamically imported */
export function initAdminInsightsOrdersChart() {
    const script = document.getElementById('likha-insights-orders-data');
    const canvas = document.getElementById('likhaInsightsOrdersChart');
    if (!script || !canvas?.getContext) {
        return;
    }

    let payload;
    try {
        payload = JSON.parse(script.textContent || '{}');
    } catch {
        return;
    }

    const labels = Array.isArray(payload.labels) ? payload.labels : [];
    const values = Array.isArray(payload.values) ? payload.values.map((v) => Number(v)) : [];

    if (labels.length === 0 || labels.length !== values.length) {
        return;
    }

    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ?? false;
    const gridColor = prefersDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
    const textColor = prefersDark ? '#dee2e6' : '#495057';

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    const peak = Math.max(1, ...values);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Orders placed',
                    data: values,
                    borderColor: PRIMARY,
                    backgroundColor(context) {
                        const chart = context.chart;
                        const { chartArea, ctx: c } = chart;
                        if (!chartArea) {
                            return PRIMARY_FILL_TOP;
                        }
                        const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, PRIMARY_FILL_TOP);
                        gradient.addColorStop(1, PRIMARY_FILL_BOT);
                        return gradient;
                    },
                    borderWidth: 2,
                    pointRadius: labels.length > 31 ? 0 : 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35,
                    spanGaps: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: textColor,
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const n = context.parsed.y;
                            const unit = Number(n) === 1 ? 'order' : 'orders';

                            return ` ${Number(n)} ${unit}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: textColor,
                        maxRotation: 45,
                        minRotation: 0,
                    },
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.ceil(peak * 1.15) || 1,
                    grid: {
                        color: gridColor,
                    },
                    ticks: {
                        color: textColor,
                        precision: 0,
                        stepSize: peak <= 20 ? 1 : undefined,
                    },
                },
            },
        },
    });
}
