        $(document).ready((function(){   
		   // Performance Chart
		    const chartElement = document.getElementById('performance-chart');
    
    // Merrni të dhënat nga data attributes
			const labels = JSON.parse(chartElement.dataset.labels);
			const cpuData = JSON.parse(chartElement.dataset.cpu);
			const memoryData = JSON.parse(chartElement.dataset.memory);
			const cpuLabel = chartElement.dataset.cpuLabel;
			const memoryLabel = chartElement.dataset.memoryLabel;
            const ctx = chartElement.getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: cpuLabel,
                            data:cpuData,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: memoryLabel,
                            data: memoryData,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: '%'
                            }
                        }
                    }
                }
            });

            // Auto-refresh every 30 seconds
            setInterval(function() {
                location.reload();
            }, 30000);
		}));