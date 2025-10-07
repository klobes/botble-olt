 $(document).ready((function(){ 
$('#add-olt-form').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("fiberhome.olt.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Botble.showSuccess(response.message);
                $('#add-olt-modal').modal('hide');
                $('#olt-table').DataTable().ajax.reload();
                $('#add-olt-form')[0].reset();
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
                Botble.showError('{{ trans("plugins/fiberhome-olt-manager::olt.add_error") }}');
            }
        }
    });
});
}));