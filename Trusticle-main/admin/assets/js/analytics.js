$(document).ready(function() {
    // Initialize the donut chart for article distribution
    const donutCtx = document.getElementById('donutChart').getContext('2d');
    
    // Define chart center plugin
    const chartCenterTextPlugin = {
        id: 'chartCenterText',
        beforeDraw: function(chart) {
            if (chart.config.type === 'doughnut') {
                // Get ctx and config
                const ctx = chart.ctx;
                const width = chart.width;
                const height = chart.height;
                
                // Calculate center position
                const centerX = width / 2;
                const centerY = height / 2;
                
                // Draw text
                ctx.restore();
                ctx.font = 'bold 16px Arial';
                ctx.textBaseline = 'middle';
                ctx.textAlign = 'center';
                
                // Line 1 - number
                ctx.fillStyle = '#333';
                ctx.font = 'bold 40px Arial';
                ctx.fillText('100', centerX, centerY - 30);
                
                // Line 2 - text
                ctx.fillStyle = '#777';
                ctx.font = '14px Arial';
                ctx.fillText('Articles', centerX, centerY + 7);
                
                ctx.save();
            }
        }
    };
    
    // Chart.js donut chart configuration
    const donutChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Real', 'Fake'],
            datasets: [{
                data: [60, 20, 20],
                backgroundColor: [
                    '#FCD34D', // Pending (yellow)
                    '#34D399', // Real (green)
                    '#F87171'  // Fake (red)
                ],
                borderWidth: 0,
                hoverOffset: 5
            }]
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return `${label}: ${value}%`;
                        }
                    }
                }
            }
        },
        plugins: [chartCenterTextPlugin]
    });

    // Initialize the line chart for fake keywords
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    
    // Chart.js line chart configuration
    const lineChart = new Chart(lineCtx, {
        type: 'bar',
        data: {
            labels: ['Crazy', 'Jansdale', 'Baho', 'Multo', 'Bro', 'Luh', 'Bading', 'Ako'],
            datasets: [{
                label: 'Frequency',
                data: [5, 10, 40, 20, 30, 70, 10, 60],
                backgroundColor: '#4F46E5',
                borderColor: '#4F46E5',
                borderWidth: 0,
                borderRadius: 4,
                barThickness: 20,
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false,
                        color: '#f0f0f0'
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#333',
                    titleFont: {
                        size: 13
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 10,
                    displayColors: false
                }
            }
        }
    });
});