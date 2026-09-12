const revenueData = window.revenueData || [];

document.addEventListener('DOMContentLoaded', function () {
    const labels = revenueData.map(d => d.day);
    const values = revenueData.map(d => d.total);

    const total = values.reduce((a, b) => a + b, 0);
    const avg = values.length ? Math.round(total / values.length) : 0;
    const maxValue = Math.max(...values, 0);
    const bestDay = maxValue > 0 ? revenueData[values.indexOf(maxValue)].day : 'N/A';

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
                    maxBarThickness: 20
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
                                return 'Day ' + item.label + ': ₱' + item.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Day of Month'
                        },
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#666' }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (₱)'
                        },
                        grid: { color: '#d2d2d2' },
                        ticks: {
                            font: { size: 10 },
                            color: '#666',
                            callback: function (value) {
                                return '₱' + value;
                            }
                        }
                    }
                }
            }
        });
    }

});