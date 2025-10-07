/**
 * OLT Management JavaScript
 * Handles OLT CRUD operations and interactions
 */

(function($) {
    'use strict';

    const OltManagement = {
        init: function() {
            this.bindEvents();
            this.initModals();
        },

        bindEvents: function() {
            const self = this;

            // Add OLT button
            $('#addOltBtn').on('click', function(e) {
                e.preventDefault();
                self.showAddModal();
            });

            // Edit OLT
            $(document).on('click', '.edit-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.showEditModal(oltId);
            });

            // View OLT details
            $(document).on('click', '.view-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.showDetailsModal(oltId);
            });

            // Delete OLT
            $(document).on('click', '.delete-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.deleteOlt(oltId);
            });

            // Test connection
            $(document).on('click', '.test-connection', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.testConnection(oltId);
            });

            // Sync OLT data
            $(document).on('click', '.sync-olt', function(e) {
                e.preventDefault();
                const oltId = $(this).data('id');
                self.syncOlt(oltId);
            });

            // Form submission
            $('#addOltForm').on('submit', function(e) {
                e.preventDefault();
                self.submitAddForm();
            });

            $('#editOltForm').on('submit', function(e) {
                e.preventDefault();
                self.submitEditForm();
            });

            // Test connection in modal
            $('#testConnectionBtn').on('click', function(e) {
                e.preventDefault();
                self.testConnectionInModal();
            });
        },

        initModals: function() {
            // Initialize Bootstrap modals
            this.addModal = new bootstrap.Modal(document.getElementById('addOltModal'));
            this.editModal = new bootstrap.Modal(document.getElementById('editOltModal'));
            this.detailsModal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
        },

        showAddModal: function() {
            $('#addOltForm')[0].reset();
            this.addModal.show();
        },

        showEditModal: function(oltId) {
            const self = this;

            $.ajax({
                url: `/api/fiberhome-olt/devices/${oltId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        self.populateEditForm(response.data);
                        self.editModal.show();
                    }
                },
                error: function(xhr) {
                    self.showError('Failed to load OLT data');
                }
            });
        },

        showDetailsModal: function(oltId) {
            const self = this;

            $.ajax({
                url: `/api/fiberhome-olt/devices/${oltId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        self.populateDetailsModal(response.data);
                        self.detailsModal.show();
                    }
                },
                error: function(xhr) {
                    self.showError('Failed to load OLT details');
                }
            });
        },

        populateEditForm: function(data) {
            $('#editOltId').val(data.id);
            $('#editOltName').val(data.name);
            $('#editOltIp').val(data.ip_address);
            $('#editOltModel').val(data.model);
            $('#editOltVendor').val(data.vendor);
            $('#editOltCommunity').val(data.snmp_community);
            $('#editOltVersion').val(data.snmp_version);
            $('#editOltPort').val(data.snmp_port);
            $('#editOltLocation').val(data.location);
            $('#editOltDescription').val(data.description);
        },

        populateDetailsModal: function(data) {
            $('#detailOltName').text(data.name);
            $('#detailOltIp').text(data.ip_address);
            $('#detailOltModel').text(data.model);
            $('#detailOltVendor').text(data.vendor);
            $('#detailOltStatus').html(this.getStatusBadge(data.status));
            $('#detailOltLocation').text(data.location || 'N/A');
            $('#detailOltDescription').text(data.description || 'N/A');
            $('#detailOltOnuCount').text(data.onu_count || 0);
            $('#detailOltUptime').text(data.uptime || 'N/A');
            $('#detailOltLastSync').text(data.last_sync || 'Never');
        },

        submitAddForm: function() {
            const self = this;
            const formData = $('#addOltForm').serialize();

            $.ajax({
                url: '/admin/fiberhome-olt/devices/create',
                method: 'POST',
                data: formData,
                success: function(response) {
                    self.addModal.hide();
                    self.showSuccess('OLT added successfully');
                    window.reloadDataTable('#oltDevicesTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to add OLT');
                }
            });
        },

        submitEditForm: function() {
            const self = this;
            const oltId = $('#editOltId').val();
            const formData = $('#editOltForm').serialize();

            $.ajax({
                url: `/admin/fiberhome-olt/devices/${oltId}`,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    self.editModal.hide();
                    self.showSuccess('OLT updated successfully');
                    window.reloadDataTable('#oltDevicesTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to update OLT');
                }
            });
        },

        deleteOlt: function(oltId) {
            const self = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/fiberhome-olt/devices/${oltId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('OLT deleted successfully');
                            window.reloadDataTable('#oltDevicesTable');
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to delete OLT');
                        }
                    });
                }
            });
        },

        testConnection: function(oltId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/devices/${oltId}/test-connection`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Connection successful!');
                    } else {
                        self.showError('Connection failed: ' + response.message);
                    }
                },
                error: function(xhr) {
                    self.showError('Connection test failed');
                }
            });
        },

        testConnectionInModal: function() {
            const self = this;
            const ipAddress = $('#addOltIp').val() || $('#editOltIp').val();
            const community = $('#addOltCommunity').val() || $('#editOltCommunity').val();

            if (!ipAddress || !community) {
                self.showError('Please fill in IP address and SNMP community');
                return;
            }

            $('#testConnectionBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');

            $.ajax({
                url: '/admin/fiberhome-olt/devices/test-connection',
                method: 'POST',
                data: {
                    ip_address: ipAddress,
                    snmp_community: community
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Connection successful!');
                    } else {
                        self.showError('Connection failed: ' + response.message);
                    }
                },
                error: function(xhr) {
                    self.showError('Connection test failed');
                },
                complete: function() {
                    $('#testConnectionBtn').prop('disabled', false).html('<i class="fa fa-plug"></i> Test Connection');
                }
            });
        },

        syncOlt: function(oltId) {
            const self = this;

            Swal.fire({
                title: 'Sync OLT Data?',
                text: "This will fetch latest data from the OLT device",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, sync it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/fiberhome-olt/devices/${oltId}/sync`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('OLT data synced successfully');
                            window.reloadDataTable('#oltDevicesTable');
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to sync OLT');
                        }
                    });
                }
            });
        },

        getStatusBadge: function(status) {
            const statusClass = {
                'online': 'success',
                'offline': 'danger',
                'error': 'warning'
            };
            return `<span class="badge bg-${statusClass[status] || 'secondary'}">${status}</span>`;
        },

        showSuccess: function(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        },

        showError: function(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        OltManagement.init();
    });

})(jQuery);