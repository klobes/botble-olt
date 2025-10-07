<div class="modal fade" id="view-onu-details-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::onu.view_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::onu.basic_info') }}</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.serial_number') }}:</strong></td>
                                <td id="onu-detail-serial"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.olt') }}:</strong></td>
                                <td id="onu-detail-olt"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.slot') }}:</strong></td>
                                <td id="onu-detail-slot"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.port') }}:</strong></td>
                                <td id="onu-detail-port"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.onu_id') }}:</strong></td>
                                <td id="onu-detail-onu-id"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::onu.optical_metrics') }}</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.rx_power') }}:</strong></td>
                                <td id="onu-detail-rx-power"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.tx_power') }}:</strong></td>
                                <td id="onu-detail-tx-power"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.distance') }}:</strong></td>
                                <td id="onu-detail-distance"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.status') }}:</strong></td>
                                <td id="onu-detail-status"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::onu.last_seen') }}:</strong></td>
                                <td id="onu-detail-last-seen"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::onu.bandwidth_info') }}</strong></h6>
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.profile_name') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.download_speed') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.upload_speed') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.current_usage') }}</th>
                                </tr>
                            </thead>
                            <tbody id="onu-bandwidth-info">
                                <!-- Bandwidth info will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::onu.performance_history') }}</strong></h6>
                        <canvas id="onu-performance-chart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ trans('plugins/fiberhome-olt-manager::onu.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewONUDetails(onuId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Populate basic info
                $('#onu-detail-serial').text(data.serial_number);
                $('#onu-detail-olt').text(data.olt_name);
                $('#onu-detail-slot').text(data.slot);
                $('#onu-detail-port').text(data.port);
                $('#onu-detail-onu-id').text(data.onu_id);
                
                // Populate optical metrics
                $('#onu-detail-rx-power').text(data.rx_power + ' dBm');
                $('#onu-detail-tx-power').text(data.tx_power + ' dBm');
                $('#onu-detail-distance').text(data.distance + ' m');
                $('#onu-detail-status').html(getONUStatusBadge(data.status));
                $('#onu-detail-last-seen').text(data.last_seen ? new Date(data.last_seen).toLocaleString() : 'N/A');
                
                // Load bandwidth info
                loadONUBandwidthInfo(onuId);
                
                // Load performance history
                loadONUPerformanceHistory(onuId);
                
                $('#view-onu-details-modal').modal('show');
            }
        },
        error: function() {
            Botble.showError('{{ trans("plugins/fiberhome-olt-manager::onu.load_error") }}');
        }
    });
}

function loadONUBandwidthInfo(onuId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId + '/bandwidth',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const bandwidth = response.data;
                let html = '';
                
                if (bandwidth) {
                    html += '<tr>';
                    html += '<td>' + (bandwidth.profile_name || 'Default') + '</td>';
                    html += '<td>' + (bandwidth.download_speed || 0) + ' Mbps</td>';
                    html += '<td>' + (bandwidth.upload_speed || 0) + ' Mbps</td>';
                    html += '<td>' + (bandwidth.current_usage || 0) + '%</td>';
                    html += '</tr>';
                } else {
                    html += '<tr><td colspan="4" class="text-center">{{ trans("plugins/fiberhome-olt-manager::onu.no_bandwidth_info") }}</td></tr>';
                }
                
                $('#onu-bandwidth-info').html(html);
            }
        }
    });
}

function loadONUPerformanceHistory(onuId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId + '/performance',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                renderONUPerformanceChart(response.data);
            }
        }
    });
}

function renderONUPerformanceChart(data) {
    const ctx = document.getElementById('onu-performance-chart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.onuPerformanceChart) {
        window.onuPerformanceChart.destroy();
    }
    
    const labels = data.map(item => new Date(item.created_at).toLocaleTimeString());
    const rxPower = data.map(item => parseFloat(item.rx_power) || 0);
    const txPower = data.map(item => parseFloat(item.tx_power) || 0);
    
    window.onuPerformanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ trans("plugins/fiberhome-olt-manager::onu.rx_power") }}',
                data: rxPower,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: '{{ trans("plugins/fiberhome-olt-manager::onu.tx_power") }}',
                data: txPower,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'dBm'
                    }
                }
            }
        }
    });
}

function getONUStatusBadge(status) {
    let statusClass, statusText;
    
    switch(status) {
        case 'online':
            statusClass = 'success';
            statusText = '{{ trans("plugins/fiberhome-olt-manager::onu.online") }}';
            break;
        case 'offline':
            statusClass = 'danger';
            statusText = '{{ trans("plugins/fiberhome-olt-manager::onu.offline") }}';
            break;
        case 'dying_gasp':
            statusClass = 'warning';
            statusText = '{{ trans("plugins/fiberhome-olt-manager::onu.dying_gasp") }}';
            break;
        default:
            statusClass = 'secondary';
            statusText = '{{ trans("plugins/fiberhome-olt-manager::onu.unknown") }}';
    }
    
    return '<span class="badge badge-' + statusClass + '">' + statusText + '</span>';
}
</script>