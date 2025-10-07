 $(document).ready((function(){
 // Vendor change handler - load models dynamically
    $('#add-vendor').on('change', function() {
        var vendor = $(this).val();
        var $modelSelect = $('#add-model');
        var $modelDetails = $('#model-details');
        
        // Reset model selection
        $modelSelect.empty().prop('disabled', true);
        $modelDetails.hide();
        
        if (!vendor) {
            $modelSelect.append('<option value="">{{ trans("plugins/fiberhome-olt-manager::olt.select_vendor_first") }}</option>');
            return;
        }
        
        // Show loading
        $modelSelect.append('<option value="">Loading models...</option>');
        
        // Fetch models from API
        $.ajax({
            url: '/api/fiberhome-olt/vendors/' + vendor + '/models',
            method: 'GET',
            success: function(response) {
                $modelSelect.empty();
                $modelSelect.append('<option value="">{{ trans("plugins/fiberhome-olt-manager::olt.select_model") }}</option>');
                
                if (response.success && response.data) {
                    $.each(response.data, function(index, model) {
                        $modelSelect.append(
                            $('<option></option>')
                                .val(model.value)
                                .text(model.text)
                                .data('model-info', model)
                        );
                    });
                    $modelSelect.prop('disabled', false);
                }
            },
            error: function() {
                $modelSelect.empty();
                $modelSelect.append('<option value="">Error loading models</option>');
                Botble.showError('Failed to load models for selected vendor');
            }
        });
    });
    
    // Model change handler - show model details
    $('#add-model').on('change', function() {
        var $selectedOption = $(this).find('option:selected');
        var modelInfo = $selectedOption.data('model-info');
        var $modelDetails = $('#model-details');
        
        if (modelInfo) {
            $('#model-description').text(modelInfo.description || '-');
            $('#model-max-ports').text(modelInfo.max_ports || '-');
            $('#model-max-onus').text(modelInfo.max_onus || '-');
            $('#model-technology').text(
                modelInfo.technology && modelInfo.technology.length > 0 
                    ? modelInfo.technology.join(', ') 
                    : '-'
            );
            $modelDetails.fadeIn();
        } else {
            $modelDetails.hide();
        }
    });
    
    // Test Connection
    $('#test-connection-btn').on('click', function() {
        var $btn = $(this);
        var $status = $('#connection-status');
        var $message = $('#connection-message');
        
        // Validate required fields
        var ip = $('#add-ip').val();
        var community = $('#add-snmp-community').val();
        var version = $('#add-snmp-version').val();
        var port = $('#add-snmp-port').val() || 161;
        
        if (!ip || !community || !version) {
            $status.removeClass('alert-success alert-danger').addClass('alert-warning').show();
            $message.text('Please fill in IP address, SNMP community, and version first.');
            return;
        }
        
        // Show loading
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');
        $status.removeClass('alert-success alert-danger alert-warning').addClass('alert-info').show();
        $message.text('Testing connection to OLT device...');
        
        // Test connection
        $.ajax({
            url: '/api/fiberhome-olt/devices/test-connection',
            method: 'POST',
            data: {
                ip_address: ip,
                snmp_community: community,
                snmp_version: version,
                snmp_port: port,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $status.removeClass('alert-info alert-danger').addClass('alert-success');
                    var msg = 'Connection successful!';
                    if (response.data) {
                        if (response.data.system_name) {
                            msg += ' System: ' + response.data.system_name;
                        }
                        if (response.data.system_description) {
                            msg += ' (' + response.data.system_description + ')';
                        }
                    }
                    $message.text(msg);
                } else {
                    $status.removeClass('alert-info alert-success').addClass('alert-danger');
                    $message.text('Connection failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                $status.removeClass('alert-info alert-success').addClass('alert-danger');
                var errorMsg = 'Connection failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ': ' + xhr.responseJSON.message;
                }
                $message.text(errorMsg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-plug"></i> Test Connection');
            }
        });
    });
    
    // Form submission
    $('#add-olt-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#save-olt-btn');
        var formData = $form.serialize();
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '/api/fiberhome-olt/devices',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Botble.showSuccess(response.message || '{{ trans("plugins/fiberhome-olt-manager::olt.add_success") }}');
                    $('#add-olt-modal').modal('hide');
                    $form[0].reset();
                    $('#add-model').empty().append('<option value="">{{ trans("plugins/fiberhome-olt-manager::olt.select_vendor_first") }}</option>').prop('disabled', true);
                    $('#model-details').hide();
                    $('#connection-status').hide();
                    
                    // Reload DataTable
                    if (window.oltTable) {
                        window.oltTable.ajax.reload();
                    } else {
                        location.reload();
                    }
                } else {
                    Botble.showError(response.message || '{{ trans("plugins/fiberhome-olt-manager::olt.add_error") }}');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    
                    Object.keys(errors).forEach(function(key) {
                        errorMessage += errors[key].join(', ') + '<br>';
                    });
                    
                    Botble.showError(errorMessage);
                } else {
                    Botble.showError(xhr.responseJSON?.message || '{{ trans("plugins/fiberhome-olt-manager::olt.add_error") }}');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("core/base::forms.save") }}');
            }
        });
    });
    
    // Reset form when modal is closed
    $('#add-olt-modal').on('hidden.bs.modal', function() {
        $('#add-olt-form')[0].reset();
        $('#add-model').empty().append('<option value="">{{ trans("plugins/fiberhome-olt-manager::olt.select_vendor_first") }}</option>').prop('disabled', true);
        $('#model-details').hide();
        $('#connection-status').hide();
    });
}));