/**
 * LenDen Enterprise Dashboard Charts
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue vs Expense Bar Chart
    const revCanvas = document.getElementById('revExpChart');
    if (revCanvas) {
        const labels = JSON.parse(revCanvas.dataset.labels || '[]');
        const revenue = JSON.parse(revCanvas.dataset.revenue || '[]');
        const expense = JSON.parse(revCanvas.dataset.expense || '[]');

        new Chart(revCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sales Revenue',
                        data: revenue,
                        backgroundColor: 'rgba(79, 70, 229, 0.85)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Purchases / Expenses',
                        data: expense,
                        backgroundColor: 'rgba(239, 68, 68, 0.75)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '₹ ' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }

    // 2. Liquid Assets vs Liabilities Doughnut Chart
    const balCanvas = document.getElementById('balanceChart');
    if (balCanvas) {
        const cash = parseFloat(balCanvas.dataset.cash || 0);
        const bank = parseFloat(balCanvas.dataset.bank || 0);
        const ar = parseFloat(balCanvas.dataset.ar || 0);
        const ap = parseFloat(balCanvas.dataset.ap || 0);

        new Chart(balCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Cash Vault', 'Bank Balance', 'Accounts Receivable', 'Accounts Payable'],
                datasets: [{
                    data: [cash, bank, ar, ap],
                    backgroundColor: [
                        '#06b6d4',
                        '#4f46e5',
                        '#10b981',
                        '#f59e0b'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});

