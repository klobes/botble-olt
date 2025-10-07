<div class="modal fade" id="create-bandwidth-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="create-bandwidth-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::bandwidth.create_profile') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="create-name">{{ trans('plugins/fiberhome-olt-manager::bandwidth.name') }}</label>
                        <input type="text" class="form-control" id="create-name" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create-download-speed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.download_speed') }} (Mbps)</label>
                                <input type="number" class="form-control" id="create-download-speed" name="download_speed" min="1" max="10000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create-upload-speed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.upload_speed') }} (Mbps)</label>
                                <input type="number" class="form-control" id="create-upload-speed" name="upload_speed" min="1" max="10000" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create-download-guaranteed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.download_guaranteed') }} (%)</label>
                                <input type="number" class="form-control" id="create-download-guaranteed" name="download_guaranteed" min="10" max="100" value="80">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="create-upload-guaranteed">{{ trans('plugins/fiberhome-olt-manager::bandwidth.upload_guaranteed') }} (%)</label>
                                <input type="number" class="form-control" id="create-upload-guaranteed" name="upload_guaranteed" min="10" max="100" value="80">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-priority">{{ trans('plugins/fiberhome-olt-manager::bandwidth.priority') }}</label>
                        <select class="form-control" id="create-priority" name="priority">
                            <option value="low">{{ trans('plugins/fiberhome-olt-manager::bandwidth.low') }}</option>
                            <option value="medium" selected>{{ trans('plugins/fiberhome-olt-manager::bandwidth.medium') }}</option>
                            <option value="high">{{ trans('plugins/fiberhome-olt-manager::bandwidth.high') }}</option>
                            <option value="premium">{{ trans('plugins/fiberhome-olt-manager::bandwidth.premium') }}</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-description">{{ trans('plugins/fiberhome-olt-manager::bandwidth.description') }}</label>
                        <textarea class="form-control" id="create-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="create-status" name="status" checked>
                            <label class="form-check-label" for="create-status">
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
                        {{ trans('plugins/fiberhome-olt-manager::bandwidth.create_profile') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#create-bandwidth-form').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("fiberhome.bandwidth.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#create-bandwidth-modal').modal('hide');
                $('#bandwidth-table').DataTable().ajax.reload();
                $('#create-bandwidth-form')[0].reset();
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
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::bandwidth.create_error") }}');
            }
        }
    });
});
</script>