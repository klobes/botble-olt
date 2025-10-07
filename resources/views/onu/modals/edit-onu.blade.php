<div class="modal fade" id="edit-onu-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="edit-onu-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-onu-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::onu.edit_onu') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-onu-serial">{{ trans('plugins/fiberhome-olt-manager::onu.serial_number') }}</label>
                        <input type="text" class="form-control" id="edit-onu-serial" name="serial_number" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-olt">{{ trans('plugins/fiberhome-olt-manager::onu.olt') }}</label>
                        <select class="form-control" id="edit-onu-olt" name="olt_id" required>
                            <option value="">{{ trans('plugins/fiberhome-olt-manager::onu.select_olt') }}</option>
                            @foreach($olts as $olt)
                                <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-slot">{{ trans('plugins/fiberhome-olt-manager::onu.slot') }}</label>
                        <input type="number" class="form-control" id="edit-onu-slot" name="slot" min="1" max="20" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-port">{{ trans('plugins/fiberhome-olt-manager::onu.port') }}</label>
                        <input type="number" class="form-control" id="edit-onu-port" name="port" min="1" max="16" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-id-field">{{ trans('plugins/fiberhome-olt-manager::onu.onu_id') }}</label>
                        <input type="number" class="form-control" id="edit-onu-id-field" name="onu_id" min="1" max="128" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-description">{{ trans('plugins/fiberhome-olt-manager::onu.description') }}</label>
                        <textarea class="form-control" id="edit-onu-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-customer">{{ trans('plugins/fiberhome-olt-manager::onu.customer_name') }}</label>
                        <input type="text" class="form-control" id="edit-onu-customer" name="customer_name">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-onu-address">{{ trans('plugins/fiberhome-olt-manager::onu.installation_address') }}</label>
                        <textarea class="form-control" id="edit-onu-address" name="installation_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('plugins/fiberhome-olt-manager::onu.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('plugins/fiberhome-olt-manager::onu.update_onu') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editONU(onuId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId + '/edit',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                $('#edit-onu-id').val(data.id);
                $('#edit-onu-serial').val(data.serial_number);
                $('#edit-onu-olt').val(data.olt_id);
                $('#edit-onu-slot').val(data.slot);
                $('#edit-onu-port').val(data.port);
                $('#edit-onu-id-field').val(data.onu_id);
                $('#edit-onu-description').val(data.description);
                $('#edit-onu-customer').val(data.customer_name);
                $('#edit-onu-address').val(data.installation_address);
                
                $('#edit-onu-modal').modal('show');
            }
        },
        error: function() {
            Botble.showError('{{ trans("plugins/fiberhome-olt-manager::onu.load_error") }}');
        }
    });
}

$('#edit-onu-form').on('submit', function(e) {
    e.preventDefault();
    
    const onuId = $('#edit-onu-id').val();
    
    $.ajax({
        url: '{{ url("admin/fiberhome/onu") }}/' + onuId,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#edit-onu-modal').modal('hide');
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
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::onu.update_error") }}');
            }
        }
    });
});
</script>