// temporary muna to
const revenueData = [
    { month: 'June', total: 9840 },
    { month: 'July', total: 11250 },
    { month: 'August', total: 12090 }
];

document.addEventListener('DOMContentLoaded', function () {
    const labels = revenueData.map(function (d) { return d.month; });
    const values = revenueData.map(function (d) { return d.total; });

    const total = values.reduce(function (a, b) { return a + b; }, 0);
    const avg = Math.round(total / values.length);
    const maxValue = Math.max.apply(null, values);
    const bestMonth = revenueData[values.indexOf(maxValue)].month;

    const canvas = document.getElementById('revenueChart');

    if (canvas && window.Chart) {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    backgroundColor: '#1d1d1d',
                    borderRadius: 2,
                    maxBarThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) {
                                return '\u20B1' + item.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#666' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#d2d2d2' },
                        ticks: {
                            font: { size: 10 },
                            color: '#666',
                            callback: function (value) {
                                return '\u20B1' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    const chartContainer = document.querySelector('.revenue-chart');
    if (chartContainer) {
        const summary = document.createElement('div');
        summary.className = 'revenue-summary';
        summary.innerHTML = `
            <span>TOTAL: &#8369;${total.toLocaleString()}</span>
            <span>AVG/MONTH: &#8369;${avg.toLocaleString()}</span>
            <span>BEST MONTH: ${bestMonth.toUpperCase()} (&#8369;${maxValue.toLocaleString()})</span>
        `;
        chartContainer.after(summary);
    }
});