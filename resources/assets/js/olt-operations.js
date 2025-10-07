/**
 * OLT Operations JavaScript
 * Handles all OLT-related operations including view, edit, sync, test, delete
 */

(function($) {
    'use strict';

    const OltOperations = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            // View OLT Details
            $(document).on('click', '.view-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.viewOltDetails(oltId);
            });

            // Edit OLT
            $(document).on('click', '.edit-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.editOlt(oltId);
            });

            // Sync OLT Data
            $(document).on('click', '.sync-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.syncOlt(oltId);
            });

            // Test Connection
            $(document).on('click', '.test-connection', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.testConnection(oltId);
            });

            // Delete OLT
            $(document).on('click', '.delete-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.deleteOlt(oltId);
            });
        },

        viewOltDetails: function(oltId) {
            const self = this;

            $.ajax({
                url: `/api/fiberhome-olt/devices/${oltId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        self.populateDetailsModal(response.data);
                        $('#view-details-modal').modal('show');
                    }
                },
                error: function(xhr) {
                    Botble.showError('Failed to load OLT details');
                }
            });
        },

        populateDetailsModal: function(data) {
            $('#detail-name').text(data.name);
            $('#detail-ip').text(data.ip_address);
            $('#detail-model').text(data.model);
            $('#detail-vendor').text(data.vendor);
            $('#detail-status').html(this.getStatusBadge(data.status));
            $('#detail-location').text(data.location || 'N/A');
            $('#detail-description').text(data.description || 'N/A');
            $('#detail-onu-count').text(data.onu_count || 0);
            $('#detail-uptime').text(data.uptime || 'N/A');
            $('#detail-last-sync').text(data.last_sync || 'Never');
        },

        editOlt: function(oltId) {
            const self = this;

            $.ajax({
                url: `/api/fiberhome-olt/devices/${oltId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        self.populateEditForm(response.data);
                        $('#edit-olt-modal').modal('show');
                    }
                },
                error: function(xhr) {
                    Botble.showError('Failed to load OLT data');
                }
            });
        },

        populateEditForm: function(data) {
            $('#edit-olt-id').val(data.id);
            $('#edit-name').val(data.name);
            $('#edit-ip').val(data.ip_address);
            $('#edit-vendor').val(data.vendor);
            $('#edit-model').val(data.model);
            $('#edit-snmp-community').val(data.snmp_community);
            $('#edit-snmp-version').val(data.snmp_version);
            $('#edit-snmp-port').val(data.snmp_port);
            $('#edit-location').val(data.location);
            $('#edit-description').val(data.description);
        },

        syncOlt: function(oltId) {
            const self = this;
            const $btn = $(`.sync-olt[data-id="${oltId}"]`);
            const originalHtml = $btn.html();

            Swal.fire({
                title: 'Sync OLT Data?',
                text: "This will fetch latest data from the OLT device",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, sync it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
                    
                    return $.ajax({
                        url: `/api/fiberhome-olt/devices/${oltId}/sync`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).then(response => {
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.responseJSON?.message || error.statusText}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                $btn.html(originalHtml).prop('disabled', false);
                
                if (result.isConfirmed) {
                    Botble.showSuccess('OLT data synced successfully');
                    $('#olt-table').DataTable().ajax.reload(null, false);
                }
            });
        },

        testConnection: function(oltId) {
            const self = this;
            const $btn = $(`.test-connection[data-id="${oltId}"]`);
            const originalHtml = $btn.html();

            $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: `/api/fiberhome-olt/devices/${oltId}/test-connection`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Connection Successful',
                            text: 'OLT device is reachable and responding',
                            timer: 3000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Failed',
                            text: response.message || 'Unable to connect to OLT device'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: xhr.responseJSON?.message || 'Failed to test connection'
                    });
                },
                complete: function() {
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        },

        deleteOlt: function(oltId) {
            const self = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the OLT device and all associated data!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: `/api/fiberhome-olt/devices/${oltId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).then(response => {
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.responseJSON?.message || error.statusText}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Botble.showSuccess('OLT device deleted successfully');
                    $('#olt-table').DataTable().ajax.reload();
                }
            });
        },

        getStatusBadge: function(status) {
            const statusClass = {
                'online': 'success',
                'offline': 'danger',
                'error': 'warning',
                'pending': 'info'
            };
            const className = statusClass[status] || 'secondary';
            return `<span class="badge badge-${className}">${status}</span>`;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        OltOperations.init();
    });

})(jQuery);