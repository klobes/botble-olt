<!-- OLT Preview Modal -->
<div class="modal fade" id="oltPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> {{ trans('plugins/fiberhome-olt-manager::olt.preview_title') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ trans('plugins/fiberhome-olt-manager::olt.preview_description') }}
                </div>
                
                <div id="oltPreviewContainer" style="min-height: 400px;">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">{{ trans('plugins/fiberhome-olt-manager::olt.loading_preview') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ trans('plugins/fiberhome-olt-manager::olt.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Reuse the same styles from visualization.blade.php */
    #oltPreviewContainer .olt-chassis {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border: 3px solid #1a252f;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        margin: 20px auto;
    }
    
    #oltPreviewContainer .olt-chassis-1u {
        max-width: 900px;
        min-height: 150px;
    }
    
    #oltPreviewContainer .olt-chassis-2u {
        max-width: 900px;
        min-height: 300px;
    }
    
    #oltPreviewContainer .port-grid {
        display: grid;
        gap: 8px;
        padding: 15px;
        background: #34495e;
        border-radius: 6px;
    }
    
    #oltPreviewContainer .port-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }
    
    #oltPreviewContainer .port-grid-6 {
        grid-template-columns: repeat(6, 1fr);
    }
    
    #oltPreviewContainer .port-grid-8 {
        grid-template-columns: repeat(8, 1fr);
    }
    
    #oltPreviewContainer .port-grid-16 {
        grid-template-columns: repeat(16, 1fr);
    }
    
    #oltPreviewContainer .olt-port {
        aspect-ratio: 1;
        border: 2px solid #2c3e50;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: white;
        font-weight: bold;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }
    
    #oltPreviewContainer .olt-model-label {
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
    
    #oltPreviewContainer .olt-components {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #34495e;
    }
    
    #oltPreviewContainer .component-group {
        display: flex;
        gap: 10px;
    }
    
    #oltPreviewContainer .component {
        background: #1a252f;
        border: 2px solid #34495e;
        border-radius: 4px;
        padding: 8px 12px;
        color: #ecf0f1;
        font-size: 12px;
    }
    
    #oltPreviewContainer .component.active {
        border-color: #27ae60;
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    }
    
    #oltPreviewContainer .model-specs {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    #oltPreviewContainer .spec-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    #oltPreviewContainer .spec-item:last-child {
        border-bottom: none;
    }
    
    #oltPreviewContainer .spec-label {
        font-weight: 600;
        color: #495057;
    }
    
    #oltPreviewContainer .spec-value {
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
    function showOltPreview() {
        const model = $('#model').val();
        const vendor = $('#vendor').val() || 'fiberhome';
        
        if (!model) {
            toastr.warning('{{ trans('plugins/fiberhome-olt-manager::olt.select_model_first') }}');
            return;
        }
        
        $('#oltPreviewModal').modal('show');
        loadOltPreview(model, vendor);
    }
    
    function loadOltPreview(model, vendor) {
        $.ajax({
            url: '{{ route('fiberhome-olt.visualization.preview') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                model: model,
                vendor: vendor
            },
            success: function(response) {
                if (response.error) {
                    $('#oltPreviewContainer').html(
                        '<div class="alert alert-danger">' + response.message + '</div>'
                    );
                    return;
                }
                renderPreview(response.data);
            },
            error: function() {
                $('#oltPreviewContainer').html(
                    '<div class="alert alert-danger">{{ trans('plugins/fiberhome-olt-manager::olt.preview_error') }}</div>'
                );
            }
        });
    }
    
    function renderPreview(structure) {
        const chassisClass = structure.chassis.height === '2U' ? 'olt-chassis-2u' : 'olt-chassis-1u';
        
        let html = `
            <div class="model-specs">
                <h6 class="mb-3"><i class="fas fa-info-circle"></i> Model Specifications</h6>
                <div class="spec-item">
                    <span class="spec-label">Model:</span>
                    <span class="spec-value">${structure.model}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Chassis Type:</span>
                    <span class="spec-value">${structure.chassis.type}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Height:</span>
                    <span class="spec-value">${structure.chassis.height}</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Maximum Ports:</span>
                    <span class="spec-value">${structure.chassis.max_ports}</span>
                </div>
                ${structure.dimensions && structure.dimensions.width ? `
                <div class="spec-item">
                    <span class="spec-label">Dimensions:</span>
                    <span class="spec-value">${structure.dimensions.width} × ${structure.dimensions.depth} × ${structure.dimensions.height}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="olt-chassis ${chassisClass}">
                <div class="olt-model-label">${structure.model}</div>
        `;
        
        // Render port grid
        const portsPerRow = getPortsPerRow(structure.model);
        const totalPorts = structure.chassis.max_ports;
        
        html += `<div class="port-grid port-grid-${portsPerRow}">`;
        
        for (let i = 0; i < totalPorts; i++) {
            html += `
                <div class="olt-port">
                    <div>${i + 1}</div>
                </div>
            `;
        }
        
        html += '</div>';
        
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
        
        $('#oltPreviewContainer').html(html);
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
</script>
@endpush