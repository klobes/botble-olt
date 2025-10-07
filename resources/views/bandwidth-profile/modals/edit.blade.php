<div class="modal fade" id="edit-bandwidth-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="edit-bandwidth-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-bandwidth-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::bandwidth.edit_profile') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-bandwidth-name">{{ trans('plugins/fiberhome-olt-manager::bandwidth.name') }}</label>
                        <input type="text" class="form-control" id="edit-bandwidth-name" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-bandwidth-download-speed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.download_speed') }} (Mbps)</label>
                                <input type="number" class="form-control" id="edit-bandwidth-download-speed" name="download_speed" min="1" max="10000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-bandwidth-upload-speed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.upload_speed') }} (Mbps)</label>
                                <input type="number" class="form-control" id="edit-bandwidth-upload-speed" name="upload_speed" min="1" max="10000" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-bandwidth-download-guaranteed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.download_guaranteed') }} (%)</label>
                                <input type="number" class="form-control" id="edit-bandwidth-download-guaranteed" name="download_guaranteed" min="10" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-bandwidth-upload-guaranteed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.upload_guaranteed') }} (%)</label>
                                <input type="number" class="form-control" id="edit-bandwidth-upload-guaranteed" name="upload_guaranteed" min="10" max="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-bandwidth-priority">{{ trans('plugins/fiberhome-olt-manager::bandwidth.priority') }}</label>
                        <select class="form-control" id="edit-bandwidth-priority" name="priority">
                            <option value="low">{{ trans('plugins/fiberhome-olt-manager::bandwidth.low') }}</option>
                            <option value="medium">{{ trans('plugins/fiberhome-olt-manager::bandwidth.medium') }}</option>
                            <option value="high">{{ trans('plugins/fiberhome-olt-manager::bandwidth.high') }}</option>
                            <option value="premium">{{ trans('plugins/fiberhome-olt-manager::bandwidth.premium') }}</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-bandwidth-description">{{ trans('plugins/fiberhome-olt-manager::bandwidth.description') }}</label>
                        <textarea class="form-control" id="edit-bandwidth-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit-bandwidth-status" name="status">
                            <label class="form-check-label" for="edit-bandwidth-status">
                                {{ trans('plugins/fiberhome-olt-manager::bandwidth.enable_profile') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('plugins/fiberhome-olt-manager::bandwidth.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('plugins/fiberhome-olt-manager::bandwidth.update_profile') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBandwidthProfile(profileId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/bandwidth") }}/' + profileId + '/edit',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                $('#edit-bandwidth-id').val(data.id);
                $('#edit-bandwidth-name').val(data.name);
                $('#edit-bandwidth-download-speed').val(data.download_speed);
                $('#edit-bandwidth-upload-speed').val(data.upload_speed);
                $('#edit-bandwidth-download-guaranteed').val(data.download_guaranteed);
                $('#edit-bandwidth-upload-guaranteed').val(data.upload_guaranteed);
                $('#edit-bandwidth-priority').val(data.priority);
                $('#edit-bandwidth-description').val(data.description);
                $('#edit-bandwidth-status').prop('checked', data.status === 'active');
                
                $('#edit-bandwidth-modal').modal('show');
            }
        },
        error: function() {
            Botble.showError('{{ trans("plugins/fiberhome-olt-manager::bandwidth.load_error") }}');
        }
    });
}

$('#edit-bandwidth-form').on('submit', function(e) {
    e.preventDefault();
    
    const profileId = $('#edit-bandwidth-id').val();
    
    $.ajax({
        url: '{{ url("admin/fiberhome/bandwidth") }}/' + profileId,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#edit-bandwidth-modal').modal('hide');
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
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::bandwidth.update_error") }}');
            }
        }
    });
});
</script>