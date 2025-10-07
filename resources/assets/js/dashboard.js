/**
 * Dashboard JavaScript
 * Handles dashboard widgets, charts, and real-time updates
 */

(function($) {
    'use strict';

    const Dashboard = {
        charts: {},
        refreshInterval: 30000, // 30 seconds
        refreshTimer: null,

        init: function() {
            this.initCharts();
            this.initWidgets();
            this.initRealTimeUpdates();
            this.bindEvents();
        },

        initCharts: function() {
            // CPU Usage Chart
            if ($('#cpuUsageChart').length) {
                this.charts.cpuUsage = this.createLineChart('cpuUsageChart', 'CPU Usage', '%');
            }

            // Memory Usage Chart
            if ($('#memoryUsageChart').length) {
                this.charts.memoryUsage = this.createLineChart('memoryUsageChart', 'Memory Usage', '%');
            }

            // Temperature Chart
            if ($('#temperatureChart').length) {
                this.charts.temperature = this.createLineChart('temperatureChart', 'Temperature', '°C');
            }

            // ONU Status Pie Chart
            if ($('#onuStatusChart').length) {
                this.charts.onuStatus = this.createPieChart('onuStatusChart', 'ONU Status');
            }
        },

        createLineChart: function(elementId, label, unit) {
            const ctx = document.getElementById(elementId);
            if (!ctx) return null;

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: label,
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + unit;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + unit;
                                }
                            }
                        }
                    }
                }
            });
        },

        createPieChart: function(elementId, label) {
            const ctx = document.getElementById(elementId);
            if (!ctx) return null;

            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Online', 'Offline', 'LOS', 'Dying Gasp'],
                    datasets: [{
                        data: [0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        initWidgets: function() {
            // Initialize counter animations
            $('.counter').each(function() {
                const $this = $(this);
                const countTo = parseInt($this.attr('data-count'));
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
        },

        initRealTimeUpdates: function() {
            // Start auto-refresh
            this.startAutoRefresh();
        },

        startAutoRefresh: function() {
            const self = this;
            
            this.refreshTimer = setInterval(function() {
                self.refreshDashboardData();
            }, this.refreshInterval);
        },

        stopAutoRefresh: function() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
                this.refreshTimer = null;
            }
        },

        refreshDashboardData: function() {
            const self = this;

            $.ajax({
                url: '/api/fiberhome-olt/dashboard/data',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.updateCharts(response);
                        self.updateWidgets(response);
                        self.updateAlerts(response.alerts);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to refresh dashboard data:', error);
                }
            });
        },

        updateCharts: function(data) {
            // Update CPU chart
            if (this.charts.cpuUsage && data.performance) {
                this.updateLineChart(this.charts.cpuUsage, data.performance.cpu);
            }

            // Update Memory chart
            if (this.charts.memoryUsage && data.performance) {
                this.updateLineChart(this.charts.memoryUsage, data.performance.memory);
            }

            // Update Temperature chart
            if (this.charts.temperature && data.performance) {
                this.updateLineChart(this.charts.temperature, data.performance.temperature);
            }

            // Update ONU Status chart
            if (this.charts.onuStatus && data.onu_status) {
                this.updatePieChart(this.charts.onuStatus, data.onu_status);
            }
        },

        updateLineChart: function(chart, newData) {
            const now = new Date().toLocaleTimeString();
            
            // Add new data
            chart.data.labels.push(now);
            chart.data.datasets[0].data.push(newData);

            // Keep only last 20 data points
            if (chart.data.labels.length > 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
            }

            chart.update();
        },

        updatePieChart: function(chart, data) {
            chart.data.datasets[0].data = [
                data.online || 0,
                data.offline || 0,
                data.los || 0,
                data.dying_gasp || 0
            ];
            chart.update();
        },

        updateWidgets: function(data) {
            // Update statistics
            if (data.statistics) {
                $('#totalOlts').text(data.statistics.total_olts || 0);
                $('#totalOnus').text(data.statistics.total_onus || 0);
                $('#onlineOnus').text(data.statistics.online_onus || 0);
                $('#offlineOnus').text(data.statistics.offline_onus || 0);
            }
        },

        updateAlerts: function(alerts) {
            if (!alerts || alerts.length === 0) return;

            const $alertsContainer = $('#alertsContainer');
            $alertsContainer.empty();

            alerts.forEach(function(alert) {
                const alertHtml = `
                    <div class="alert alert-${alert.type} alert-dismissible fade show" role="alert">
                        <strong>${alert.title}</strong> ${alert.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $alertsContainer.append(alertHtml);
            });
        },

        bindEvents: function() {
            const self = this;

            // Refresh button
            $('#refreshDashboard').on('click', function(e) {
                e.preventDefault();
                self.refreshDashboardData();
            });

            // Auto-refresh toggle
            $('#autoRefreshToggle').on('change', function() {
                if ($(this).is(':checked')) {
                    self.startAutoRefresh();
                } else {
                    self.stopAutoRefresh();
                }
            });

            // Export charts
            $('.export-chart').on('click', function(e) {
                e.preventDefault();
                const chartId = $(this).data('chart');
                self.exportChart(chartId);
            });
        },

        exportChart: function(chartId) {
            const chart = this.charts[chartId];
            if (!chart) return;

            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.download = chartId + '-' + Date.now() + '.png';
            link.href = url;
            link.click();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        Dashboard.init();
    });

})(jQuery);