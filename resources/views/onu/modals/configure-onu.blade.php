<div class="modal fade" id="configure-onu-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="configure-onu-form">
                @csrf
                <input type="hidden" id="configure-onu-id" name="onu_id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::onu.configure_onu') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="configure-serial">{{ trans('plugins/fiberhome-olt-manager::onu.serial_number') }}</label>
                        <input type="text" class="form-control" id="configure-serial" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="configure-bandwidth-profile">{{ trans('plugins/fiberhome-olt-manager::onu.bandwidth_profile') }}</label>
                        <select class="form-control" id="configure-bandwidth-profile" name="bandwidth_profile_id">
                            <option value="">{{ trans('plugins/fiberhome-olt-manager::onu.no_profile') }}</option>
                            @foreach($bandwidthProfiles as $profile)
                                <option value="{{ $profile->id }}">{{ $profile->name }} - {{ $profile->download_speed }}/{{ $profile->upload_speed }} Mbps</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="configure-vlan">{{ trans('plugins/fiberhome-olt-manager::onu.vlan') }}</label>
                        <input type="number" class="form-control" id="configure-vlan" name="vlan" min="1" max="4096">
                    </div>
                    
                    <div class="form-group">
                        <label for="configure-service-type">{{ trans('plugins/fiberhome-olt-manager::onu.service_type') }}</label>
                        <select class="form-control" id="configure-service-type" name="service_type">
                            <option value="internet">{{ trans('plugins/fiberhome-olt-manager::onu.internet') }}</option>
                            <option value="voip">{{ trans('plugins/fiberhome-olt-manager::onu.voip') }}</option>
                            <option value="iptv">{{ trans('plugins/fiberhome-olt-manager::onu.iptv') }}</option>
                            <option value="triple_play">{{ trans('plugins/fiberhome-olt-manager::onu.triple_play') }}</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="configure-mcast-vlan">{{ trans('plugins/fiberhome-olt-manager::onu.multicast_vlan') }}</label>
                        <input type="number" class="form-control" id="configure-mcast-vlan" name="multicast_vlan" min="1" max="4096">
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="configure-igmp" name="igmp_snooping">
                            <label class="form-check-label" for="configure-igmp">
                                {{ trans('plugins/fiberhome-olt-manager::onu.enable_igmp_snooping') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="configure-dhcp" name="dhcp_snooping">
                            <label class="form-check-label" for="configure-dhcp">
                                {{ trans('plugins/fiberhome-olt-manager::onu.enable_dhcp_snooping') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="configure-description">{{ trans('plugins/fiberhome-olt-manager::onu.configuration_description') }}</label>
                        <textarea class="form-control" id="configure-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('plugins/fiberhome-olt-manager::onu.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('plugins/fiberhome-olt-manager::onu.apply_configuration') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function configureONU(onuId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                $('#configure-onu-id').val(data.id);
                $('#configure-serial').val(data.serial_number);
                
                // Load current configuration
                loadONUConfiguration(onuId);
                
                $('#configure-onu-modal').modal('show');
            }
        },
        error: function() {
            Botble.showError('{{ trans("plugins/fiberhome-olt-manager::onu.load_error") }}');
        }
    });
}

function loadONUConfiguration(onuId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId + '/configuration',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const config = response.data;
                
                $('#configure-bandwidth-profile').val(config.bandwidth_profile_id || '');
                $('#configure-vlan').val(config.vlan || '');
                $('#configure-service-type').val(config.service_type || 'internet');
                $('#configure-mcast-vlan').val(config.multicast_vlan || '');
                $('#configure-igmp').prop('checked', config.igmp_snooping || false);
                $('#configure-dhcp').prop('checked', config.dhcp_snooping || false);
                $('#configure-description').val(config.description || '');
            }
        }
    });
}

$('#configure-onu-form').on('submit', function(e) {
    e.preventDefault();
    
    const onuId = $('#configure-onu-id').val();
    
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId + '/configure',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#configure-onu-modal').modal('hide');
                $('#onu-table').DataTable().ajax.reload();
            } else {
                Botble.showError(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = '';
                
                Object.keys(errors).forEach(function(key) {
                    errorMessage += errors[key][0] + '<br>';
                });
                
                Botble.showError(errorMessage);
            } else {
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::onu.configuration_error") }}');
            }
        }
    });
});
</script>