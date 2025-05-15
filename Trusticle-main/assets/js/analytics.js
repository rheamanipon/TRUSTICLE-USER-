$(document).ready(function() {
    // Get the article counts from the global articleData object
    const pendingCount = window.articleData ? window.articleData.pending : 0;
    const legitCount = window.articleData ? window.articleData.legit : 0;
    const fakeCount = window.articleData ? window.articleData.fake : 0;
    const totalCount = window.articleData ? window.articleData.total : 0;
    
    console.log("Chart data:", { pendingCount, legitCount, fakeCount, totalCount });
    
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
                ctx.fillText(totalCount.toString(), centerX, centerY - 30);
                
                // Line 2 - text
                ctx.fillStyle = '#777';
                ctx.font = '14px Arial';
                ctx.fillText('Articles', centerX, centerY + 7);
                
                ctx.save();
            }
        }
    };
    
    // Calculate percentages for display
    const pendingPercentage = totalCount === 0 ? 0 : Math.round((pendingCount / totalCount) * 100);
    const legitPercentage = totalCount === 0 ? 0 : Math.round((legitCount / totalCount) * 100);
    const fakePercentage = totalCount === 0 ? 0 : Math.round((fakeCount / totalCount) * 100);
    
    // Chart.js donut chart configuration
    const donutChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Legit', 'Fake'],
            datasets: [{
                data: [pendingCount, legitCount, fakeCount],
                backgroundColor: [
                    '#FCD34D', // Pending (yellow)
                    '#34D399', // Legit (green)
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
                            const value = context.raw || 0;
                            const percentage = totalCount === 0 ? 0 : Math.round((value / totalCount) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        },
        plugins: [chartCenterTextPlugin]
    });

    // Initialize the line chart for fake keywords
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    
    // Check if keyword data is available
    const keywordLabels = window.keywordData ? window.keywordData.labels : ['No', 'Keywords', 'Found', 'Please', 'Submit', 'Fake', 'Articles', 'First'];
    const keywordValues = window.keywordData ? window.keywordData.values : [0, 0, 0, 0, 0, 0, 0, 0];
    
    console.log("Using keyword labels:", keywordLabels);
    console.log("Using keyword values:", keywordValues);
    
    // Chart.js line chart configuration
    const lineChart = new Chart(lineCtx, {
        type: 'bar',
        data: {
            labels: keywordLabels,
            datasets: [{
                label: 'Frequency',
                data: keywordValues,
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
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Occurrences: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
});