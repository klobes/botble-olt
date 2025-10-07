<div class="modal fade" id="assign-bandwidth-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="assign-bandwidth-form">
                @csrf
                <input type="hidden" id="assign-profile-id" name="profile_id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::bandwidth.assign_profile') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="assign-profile-name">{{ trans('plugins/fiberhome-olt-manager::bandwidth.profile_name') }}</label>
                        <input type="text" class="form-control" id="assign-profile-name" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="assign-onu-list">{{ trans('plugins/fiberhome-olt-manager::bandwidth.select_onu') }}</label>
                        <select class="form-control" id="assign-onu-list" name="onu_ids[]" multiple size="10">
                            <!-- ONU list will be loaded dynamically -->
                        </select>
                        <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::bandwidth.hold_ctrl_to_select_multiple') }}</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="assign-replace-existing" name="replace_existing" checked>
                            <label class="form-check-label" for="assign-replace-existing">
                                {{ trans('plugins/fiberhome-olt-manager::bandwidth.replace_existing_profiles') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="assign-schedule">{{ trans('plugins/fiberhome-olt-manager::bandwidth.schedule_assignment') }}</label>
                        <select class="form-control" id="assign-schedule" name="schedule">
                            <option value="immediate">{{ trans('plugins/fiberhome-olt-manager::bandwidth.immediate') }}</option>
                            <option value="maintenance_window">{{ trans('plugins/fiberhome-olt-manager::bandwidth.maintenance_window') }}</option>
                            <option value="custom">{{ trans('plugins/fiberhome-olt-manager::bandwidth.custom_time') }}</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="custom-time-group" style="display: none;">
                        <label for="assign-custom-time">{{ trans('plugins/fiberhome-olt-manager::bandwidth.custom_time') }}</label>
                        <input type="datetime-local" class="form-control" id="assign-custom-time" name="custom_time">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('plugins/fiberhome-olt-manager::bandwidth.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('plugins/fiberhome-olt-manager::bandwidth.assign_profile') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function assignBandwidthProfile(profileId) {
    // Set profile ID and name
    $('#assign-profile-id').val(profileId);
    
    // Get profile details
    $.ajax({
        url: '{{ url("admin/fiberhome/bandwidth") }}/' + profileId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#assign-profile-name').val(response.data.name);
            }
        }
    });
    
    // Load available ONU list
    loadAvailableONU();
    
    $('#assign-bandwidth-modal').modal('show');
}

function loadAvailableONU() {
    $.ajax({
        url: '{{ route("fiberhome.onu.available") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const onus = response.data;
                let html = '';
                
                onus.forEach(function(onu) {
                    html += '<option value="' + onu.id + '">';
                    html += onu.serial_number + ' - ' + (onu.customer_name || 'N/A');
                    html += ' (' + onu.olt_name + ' - Slot ' + onu.slot + ' Port ' + onu.port + ')';
                    html += '</option>';
                });
                
                $('#assign-onu-list').html(html);
            }
        }
    });
}

$('#assign-schedule').on('change', function() {
    if ($(this).val() === 'custom') {
        $('#custom-time-group').show();
    } else {
        $('#custom-time-group').hide();
    }
});

$('#assign-bandwidth-form').on('submit', function(e) {
    e.preventDefault();
    
    const profileId = $('#assign-profile-id').val();
    
    $.ajax({
        url: '{{ url("admin/fiberhome/bandwidth") }}/' + profileId + '/assign',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#assign-bandwidth-modal').modal('hide');
                $('#bandwidth-table').DataTable().ajax.reload();
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
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::bandwidth.assign_error") }}');
            }
        }
    });
});
</script>