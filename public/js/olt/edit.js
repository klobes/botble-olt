$(document).ready((function(){
function editOLT(oltId) {
    $.ajax({
        url: '{{ url("admin/fiberhome/olt") }}/' + oltId + '/edit',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                $('#edit-id').val(data.id);
                $('#edit-name').val(data.name);
                $('#edit-ip').val(data.ip_address);
                $('#edit-model').val(data.model);
                $('#edit-snmp-community').val(data.snmp_community);
                $('#edit-snmp-version').val(data.snmp_version);
                $('#edit-description').val(data.description);
                
                $('#edit-olt-modal').modal('show');
            }
        },
        error: function() {
            Botble.showError('{{ trans("plugins/fiberhome-olt-manager::olt.load_error") }}');
        }
    });
}

$('#edit-olt-form').on('submit', function(e) {
    e.preventDefault();
    
    const oltId = $('#edit-id').val();
    
    $.ajax({
        url: '{{ url("admin/fiberhome/olt") }}/' + oltId,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#edit-olt-modal').modal('hide');
                $('#olt-table').DataTable().ajax.reload();
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
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::olt.update_error") }}');
            }
        }
    });
	});
}));