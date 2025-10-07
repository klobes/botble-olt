 $(document).ready((function(){
function viewOLTDetails(oltId) {
    $.ajax({
        url: '{{ url("fiberhome/olt") }}/' + oltId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Populate basic info
                $('#detail-name').text(data.name);
                $('#detail-ip').text(data.ip_address);
                $('#detail-model').text(data.model);
                $('#detail-firmware').text(data.firmware_version || 'N/A');
                $('#detail-uptime').text(data.uptime || 'N/A');
                
                // Populate performance metrics
                $('#detail-cpu').text(data.cpu_usage + '%');
                $('#detail-memory').text(data.memory_usage + '%');
                $('#detail-temperature').text(data.temperature + '°C');
                $('#detail-status').html(getStatusBadge(data.status));
                $('#detail-polled').text(data.last_polled ? new Date(data.last_polled).toLocaleString() : 'N/A');
                
                // Load port statistics
                loadPortStatistics(oltId);
                
                $('#view-olt-details-modal').modal('show');
            }
        },
        error: function() {
            Botble.showError('{{ trans("plugins/fiberhome-olt-manager::olt.load_error") }}');
        }
    });
}

function loadPortStatistics(oltId) {
    $.ajax({
        url: '{{ url("/fiberhome/olt") }}/' + oltId + '/ports',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const ports = response.data;
                let html = '';
                
                ports.forEach(function(port) {
                    html += '<tr>';
                    html += '<td>' + port.slot + '</td>';
                    html += '<td>' + port.port + '</td>';
                    html += '<td>' + getStatusBadge(port.status) + '</td>';
                    html += '<td>' + port.onu_count + '</td>';
                    html += '<td>' + (port.bandwidth_usage || '0') + '%</td>';
                    html += '</tr>';
                });
                
                $('#port-statistics').html(html);
            }
        }
    });
}

function getStatusBadge(status) {
    const statusClass = status === 'online' ? 'success' : 'danger';
    const statusText = status === 'online' ? '{{ trans("plugins/fiberhome-olt-manager::olt.online") }}' : '{{ trans("plugins/fiberhome-olt-manager::olt.offline") }}';
    
    return '<span class="badge badge-' + statusClass + '">' + statusText + '</span>';
}
            // Initialize DataTable
            var table = $('#olt-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/v1/olt/devices/datatable',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'ip_address', name: 'ip_address' },
                    { data: 'model', name: 'model' },
                    { data: 'vendor', name: 'vendor' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data) {
                            var statusClass = {
                                'online': 'success',
                                'offline': 'danger',
                                'error': 'warning'
                            };
                            var className = statusClass[data] || 'secondary';
                            return '<span class="badge badge-' + className + '">' + data + '</span>';
                        }
                    },
                    { data: 'onu_count', name: 'onu_count', className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                order: [[0, 'desc']],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                }
            });
            
            // Refresh table button
            $('#refresh-table').on('click', function() {
                table.ajax.reload();
            });
            
            // Auto refresh every 30 seconds
            setInterval(function() {
                table.ajax.reload(null, false); // false = don't reset pagination
            }, 30000);
}));