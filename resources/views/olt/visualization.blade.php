@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>{{ trans('plugins/fiberhome-olt-manager::olt.visualization_title', ['name' => $olt->name]) }}</h1>
                        <p class="text-muted">{{ trans('plugins/fiberhome-olt-manager::olt.visualization_description') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('fiberhome-olt.olt.show', $olt->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ trans('plugins/fiberhome-olt-manager::olt.back') }}
                        </a>
                        <button type="button" class="btn btn-success" id="refresh-visualization">
                            <i class="fas fa-sync"></i> {{ trans('plugins/fiberhome-olt-manager::olt.refresh') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OLT Info Card -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{{ trans('plugins/fiberhome-olt-manager::olt.model') }}:</strong>
                            <span class="badge badge-primary">{{ $olt->model }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ trans('plugins/fiberhome-olt-manager::olt.vendor') }}:</strong>
                            {{ $olt->vendor }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ trans('plugins/fiberhome-olt-manager::olt.ip_address') }}:</strong>
                            <code>{{ $olt->ip_address }}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ trans('plugins/fiberhome-olt-manager::olt.status') }}:</strong>
                            <span class="badge badge-{{ $olt->status === 'online' ? 'success' : 'danger' }}">
                                {{ $olt->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visualization Container -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::olt.physical_view') }}</h4>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" data-view="front">
                                <i class="fas fa-eye"></i> {{ trans('plugins/fiberhome-olt-manager::olt.front_view') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-view="ports">
                                <i class="fas fa-network-wired"></i> {{ trans('plugins/fiberhome-olt-manager::olt.port_view') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="olt-visualization-container" style="min-height: 500px;">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                            <p class="mt-3">{{ trans('plugins/fiberhome-olt-manager::olt.loading_visualization') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::olt.legend') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="legend-item">
                                <span class="legend-icon" style="background-color: #28a745;"></span>
                                <span>{{ trans('plugins/fiberhome-olt-manager::olt.port_active_low') }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="legend-item">
                                <span class="legend-icon" style="background-color: #ffc107;"></span>
                                <span>{{ trans('plugins/fiberhome-olt-manager::olt.port_active_high') }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="legend-item">
                                <span class="legend-icon" style="background-color: #dc3545;"></span>
                                <span>{{ trans('plugins/fiberhome-olt-manager::olt.port_down') }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="legend-item">
                                <span class="legend-icon" style="background-color: #6c757d;"></span>
                                <span>{{ trans('plugins/fiberhome-olt-manager::olt.port_disabled') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Details Modal -->
    <div class="modal fade" id="portDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::olt.port_details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="portDetailsContent">
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .legend-icon {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 10px;
        display: inline-block;
        border: 1px solid #ddd;
    }
    
    /* OLT Chassis Styles */
    .olt-chassis {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border: 3px solid #1a252f;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        margin: 20px auto;
    }
    
    .olt-chassis-1u {
        max-width: 900px;
        min-height: 150px;
    }
    
    .olt-chassis-2u {
        max-width: 900px;
        min-height: 300px;
    }
    
    /* Slot Styles */
    .olt-slots {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .olt-slot {
        flex: 1;
        background: #1a252f;
        border: 2px solid #34495e;
        border-radius: 4px;
        padding: 10px;
        min-height: 80px;
        text-align: center;
        color: #ecf0f1;
    }
    
    .olt-slot.has-card {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        border-color: #1e8449;
    }
    
    .olt-slot.empty {
        background: #2c3e50;
        opacity: 0.5;
    }
    
    /* Port Grid Styles */
    .port-grid {
        display: grid;
        gap: 8px;
        padding: 15px;
        background: #34495e;
        border-radius: 6px;
    }
    
    .port-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .port-grid-6 {
        grid-template-columns: repeat(6, 1fr);
    }
    
    .port-grid-8 {
        grid-template-columns: repeat(8, 1fr);
    }
    
    .port-grid-16 {
        grid-template-columns: repeat(16, 1fr);
    }
    
    /* Port Styles */
    .olt-port {
        aspect-ratio: 1;
        border: 2px solid #2c3e50;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        font-size: 11px;
        color: white;
        font-weight: bold;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }
    
    .olt-port:hover {
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    
    .olt-port.status-up {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .olt-port.status-down {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    .olt-port.status-disabled {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }
    
    .olt-port.high-utilization {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }
    
    .port-label {
        font-size: 9px;
        margin-top: 2px;
    }
    
    .port-onus {
        font-size: 8px;
        margin-top: 2px;
        opacity: 0.9;
    }
    
    /* Power Supply & Fan Styles */
    .olt-components {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #34495e;
    }
    
    .component-group {
        display: flex;
        gap: 10px;
    }
    
    .component {
        background: #1a252f;
        border: 2px solid #34495e;
        border-radius: 4px;
        padding: 8px 12px;
        color: #ecf0f1;
        font-size: 12px;
    }
    
    .component.active {
        border-color: #27ae60;
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    }
    
    /* Model Label */
    .olt-model-label {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script>
    let oltStructure = null;
    let currentView = 'front';

    $(document).ready(function() {
        loadVisualization();

        // Refresh button
        $('#refresh-visualization').on('click', function() {
            loadVisualization();
        });

        // View switcher
        $('[data-view]').on('click', function() {
            $('[data-view]').removeClass('active');
            $(this).addClass('active');
            currentView = $(this).data('view');
            renderVisualization();
        });
    });

    function loadVisualization() {
        $.ajax({
            url: '{{ route('fiberhome-olt.visualization.structure', $olt->id) }}',
            method: 'GET',
            success: function(response) {
                if (response.error) {
                    toastr.error(response.message);
                    return;
                }
                oltStructure = response.data;
                renderVisualization();
            },
            error: function() {
                toastr.error('{{ trans('plugins/fiberhome-olt-manager::olt.load_error') }}');
            }
        });
    }

    function renderVisualization() {
        if (!oltStructure) return;

        const container = $('#olt-visualization-container');
        
        if (currentView === 'front') {
            container.html(renderFrontView(oltStructure));
        } else {
            container.html(renderPortView(oltStructure));
        }

        // Attach port click handlers
        $('.olt-port').on('click', function() {
            const portId = $(this).data('port-id');
            showPortDetails(portId);
        });
    }

    function renderFrontView(structure) {
        const chassisClass = structure.chassis.height === '2U' ? 'olt-chassis-2u' : 'olt-chassis-1u';
        
        let html = `
            <div class="olt-chassis ${chassisClass}">
                <div class="olt-model-label">${structure.model}</div>
        `;

        // Render slots if modular
        if (structure.chassis.type === 'modular' && structure.slots.length > 0) {
            html += '<div class="olt-slots">';
            structure.slots.forEach(slot => {
                const slotClass = slot.type === 'card' ? 'has-card' : 'empty';
                html += `
                    <div class="olt-slot ${slotClass}">
                        <div>Slot ${slot.index}</div>
                        ${slot.card ? `<div style="font-size: 10px;">${slot.card.type}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
        }

        // Render ports
        html += renderPortGrid(structure);

        // Render components
        html += '<div class="olt-components">';
        
        // Power supplies
        if (structure.power_supplies && structure.power_supplies.length > 0) {
            html += '<div class="component-group">';
            structure.power_supplies.forEach(ps => {
                const activeClass = ps.status === 'active' ? 'active' : '';
                html += `<div class="component ${activeClass}">${ps.slot}</div>`;
            });
            html += '</div>';
        }

        // Fans
        if (structure.fans && structure.fans.length > 0) {
            html += '<div class="component-group">';
            structure.fans.forEach(fan => {
                html += `<div class="component active">${fan.id}</div>`;
            });
            html += '</div>';
        }

        html += '</div></div>';

        return html;
    }

    function renderPortView(structure) {
        return `
            <div class="olt-chassis olt-chassis-1u">
                <div class="olt-model-label">${structure.model}</div>
                ${renderPortGrid(structure)}
            </div>
        `;
    }

    function renderPortGrid(structure) {
        if (!structure.ports || structure.ports.length === 0) {
            return '<div class="text-center py-5">No ports configured</div>';
        }

        const portsPerRow = getPortsPerRow(structure.model);
        let html = `<div class="port-grid port-grid-${portsPerRow}">`;

        structure.ports.forEach(port => {
            const statusClass = getPortStatusClass(port);
            const utilizationClass = port.utilization > 80 ? 'high-utilization' : '';
            
            html += `
                <div class="olt-port ${statusClass} ${utilizationClass}" 
                     data-port-id="${port.id}"
                     title="${port.label} - ${port.onus_online}/${port.onus_total} ONUs">
                    <div class="port-label">${port.port}</div>
                    <div class="port-onus">${port.onus_online}/${port.onus_total}</div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    function getPortsPerRow(model) {
        const layouts = {
            'AN5516-01': 8,
            'AN5516-04': 4,
            'AN5516-06': 6,
            'AN6000-17': 16,
        };
        return layouts[model] || 8;
    }

    function getPortStatusClass(port) {
        if (port.status !== 'up') {
            return 'status-disabled';
        }
        if (port.oper_status !== 'up') {
            return 'status-down';
        }
        return 'status-up';
    }

    function showPortDetails(portId) {
        $('#portDetailsModal').modal('show');
        $('#portDetailsContent').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');

        $.ajax({
            url: '{{ route('fiberhome-olt.visualization.port-details', ['olt' => $olt->id, 'port' => ':portId']) }}'.replace(':portId', portId),
            method: 'GET',
            success: function(response) {
                if (response.error) {
                    $('#portDetailsContent').html('<div class="alert alert-danger">' + response.message + '</div>');
                    return;
                }
                renderPortDetails(response.data);
            },
            error: function() {
                $('#portDetailsContent').html('<div class="alert alert-danger">Failed to load port details</div>');
            }
        });
    }

    function renderPortDetails(data) {
        const port = data.port;
        const stats = data.statistics;
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Port Information</h6>
                    <table class="table table-sm">
                        <tr><th>Port:</th><td>PON ${port.slot_index}/${port.port_index}</td></tr>
                        <tr><th>Status:</th><td><span class="badge badge-${port.admin_status === 'up' ? 'success' : 'danger'}">${port.admin_status}</span></td></tr>
                        <tr><th>Operational:</th><td><span class="badge badge-${port.oper_status === 'up' ? 'success' : 'danger'}">${port.oper_status}</span></td></tr>
                        <tr><th>RX Power:</th><td>${port.rx_power || 'N/A'} dBm</td></tr>
                        <tr><th>TX Power:</th><td>${port.tx_power || 'N/A'} dBm</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>ONU Statistics</h6>
                    <table class="table table-sm">
                        <tr><th>Total ONUs:</th><td>${stats.total_onus}</td></tr>
                        <tr><th>Online:</th><td><span class="badge badge-success">${stats.online_onus}</span></td></tr>
                        <tr><th>Offline:</th><td><span class="badge badge-danger">${stats.offline_onus}</span></td></tr>
                        <tr><th>LOS:</th><td><span class="badge badge-warning">${stats.los_onus}</span></td></tr>
                    </table>
                </div>
            </div>
        `;

        if (data.onus && data.onus.length > 0) {
            html += `
                <hr>
                <h6>Connected ONUs</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>RX Power</th>
                                <th>Distance</th>
                                <th>Bandwidth</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.onus.forEach(onu => {
                html += `
                    <tr>
                        <td><code>${onu.serial_number}</code></td>
                        <td><span class="badge badge-${onu.status === 'online' ? 'success' : 'danger'}">${onu.status}</span></td>
                        <td>${onu.rx_power || 'N/A'} dBm</td>
                        <td>${onu.distance || 'N/A'} m</td>
                        <td>${onu.bandwidth_profile ? onu.bandwidth_profile.name : 'N/A'}</td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
        }

        $('#portDetailsContent').html(html);
    }
</script>
@endpush