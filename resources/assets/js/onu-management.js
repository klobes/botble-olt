/**
 * ONU Management JavaScript
 * Handles ONU operations and interactions
 */

(function($) {
    'use strict';

    const OnuManagement = {
        init: function() {
            this.bindEvents();
            this.initModals();
        },

        bindEvents: function() {
            const self = this;

            // View ONU details
            $(document).on('click', '.view-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.showDetailsModal(onuId);
            });

            // Configure ONU
            $(document).on('click', '.configure-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.showConfigureModal(onuId);
            });

            // Edit ONU
            $(document).on('click', '.edit-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.showEditModal(onuId);
            });

            // Enable ONU
            $(document).on('click', '.enable-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.enableOnu(onuId);
            });

            // Disable ONU
            $(document).on('click', '.disable-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.disableOnu(onuId);
            });

            // Reboot ONU
            $(document).on('click', '.reboot-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.rebootOnu(onuId);
            });

            // Delete ONU
            $(document).on('click', '.delete-onu', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.deleteOnu(onuId);
            });

            // Form submissions
            $('#configureOnuForm').on('submit', function(e) {
                e.preventDefault();
                self.submitConfigureForm();
            });

            $('#editOnuForm').on('submit', function(e) {
                e.preventDefault();
                self.submitEditForm();
            });

            // Filter by status
            $('#filterStatus').on('change', function() {
                self.filterByStatus($(this).val());
            });

            // Filter by OLT
            $('#filterOlt').on('change', function() {
                self.filterByOlt($(this).val());
            });

            // Refresh optical power
            $(document).on('click', '.refresh-optical-power', function(e) {
                e.preventDefault();
                const onuId = $(this).data('id');
                self.refreshOpticalPower(onuId);
            });
        },

        initModals: function() {
            this.detailsModal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
            this.configureModal = new bootstrap.Modal(document.getElementById('configureOnuModal'));
            this.editModal = new bootstrap.Modal(document.getElementById('editOnuModal'));
        },

        showDetailsModal: function(onuId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}`,
                method: 'GET',
                success: function(response) {
                    self.populateDetailsModal(response.data);
                    self.detailsModal.show();
                },
                error: function(xhr) {
                    self.showError('Failed to load ONU details');
                }
            });
        },

        showConfigureModal: function(onuId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}`,
                method: 'GET',
                success: function(response) {
                    self.populateConfigureForm(response.data);
                    self.configureModal.show();
                },
                error: function(xhr) {
                    self.showError('Failed to load ONU data');
                }
            });
        },

        showEditModal: function(onuId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}`,
                method: 'GET',
                success: function(response) {
                    self.populateEditForm(response.data);
                    self.editModal.show();
                },
                error: function(xhr) {
                    self.showError('Failed to load ONU data');
                }
            });
        },

        populateDetailsModal: function(data) {
            $('#detailOnuSerial').text(data.serial_number);
            $('#detailOnuMac').text(data.mac_address || 'N/A');
            $('#detailOnuOlt').text(data.olt_name);
            $('#detailOnuPort').text(data.pon_port);
            $('#detailOnuStatus').html(this.getStatusBadge(data.status));
            $('#detailOnuRxPower').text(data.rx_power ? data.rx_power + ' dBm' : 'N/A');
            $('#detailOnuTxPower').text(data.tx_power ? data.tx_power + ' dBm' : 'N/A');
            $('#detailOnuDistance').text(data.distance ? data.distance + ' m' : 'N/A');
            $('#detailOnuCustomer').text(data.customer_name || 'N/A');
            $('#detailOnuBandwidth').text(data.bandwidth_profile || 'N/A');
            $('#detailOnuVlan').text(data.vlan_id || 'N/A');
            $('#detailOnuLastSeen').text(data.last_seen || 'N/A');
        },

        populateConfigureForm: function(data) {
            $('#configureOnuId').val(data.id);
            $('#configureOnuSerial').val(data.serial_number);
            $('#configureBandwidthProfile').val(data.bandwidth_profile_id);
            $('#configureVlanId').val(data.vlan_id);
            $('#configureCvlan').val(data.cvlan);
            $('#configureSvlan').val(data.svlan);
            $('#configureCustomerName').val(data.customer_name);
            $('#configureCustomerPhone').val(data.customer_phone);
            $('#configureCustomerAddress').val(data.customer_address);
        },

        populateEditForm: function(data) {
            $('#editOnuId').val(data.id);
            $('#editOnuSerial').val(data.serial_number);
            $('#editOnuMac').val(data.mac_address);
            $('#editCustomerName').val(data.customer_name);
            $('#editCustomerPhone').val(data.customer_phone);
            $('#editCustomerAddress').val(data.customer_address);
            $('#editOnuDescription').val(data.description);
        },

        submitConfigureForm: function() {
            const self = this;
            const onuId = $('#configureOnuId').val();
            const formData = $('#configureOnuForm').serialize();

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}/configure`,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    self.configureModal.hide();
                    self.showSuccess('ONU configured successfully');
                    window.reloadDataTable('#onuTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to configure ONU');
                }
            });
        },

        submitEditForm: function() {
            const self = this;
            const onuId = $('#editOnuId').val();
            const formData = $('#editOnuForm').serialize();

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}`,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    self.editModal.hide();
                    self.showSuccess('ONU updated successfully');
                    window.reloadDataTable('#onuTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to update ONU');
                }
            });
        },

        enableOnu: function(onuId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}/enable`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    self.showSuccess('ONU enabled successfully');
                    window.reloadDataTable('#onuTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to enable ONU');
                }
            });
        },

        disableOnu: function(onuId) {
            const self = this;

            Swal.fire({
                title: 'Disable ONU?',
                text: "This will disconnect the customer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, disable it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/fiberhome-olt/onus/${onuId}/disable`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('ONU disabled successfully');
                            window.reloadDataTable('#onuTable');
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to disable ONU');
                        }
                    });
                }
            });
        },

        rebootOnu: function(onuId) {
            const self = this;

            Swal.fire({
                title: 'Reboot ONU?',
                text: "This will temporarily disconnect the customer",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, reboot it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/fiberhome-olt/onus/${onuId}/reboot`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('ONU reboot command sent');
                            window.reloadDataTable('#onuTable');
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to reboot ONU');
                        }
                    });
                }
            });
        },

        deleteOnu: function(onuId) {
            const self = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "This will remove the ONU from the system",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/fiberhome-olt/onus/${onuId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('ONU deleted successfully');
                            window.reloadDataTable('#onuTable');
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to delete ONU');
                        }
                    });
                }
            });
        },

        refreshOpticalPower: function(onuId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/onus/${onuId}/performance`,
                method: 'GET',
                success: function(response) {
                    $('#detailOnuRxPower').text(response.data.rx_power + ' dBm');
                    $('#detailOnuTxPower').text(response.data.tx_power + ' dBm');
                    self.showSuccess('Optical power refreshed');
                },
                error: function(xhr) {
                    self.showError('Failed to refresh optical power');
                }
            });
        },

        filterByStatus: function(status) {
            const table = $('#onuTable').DataTable();
            table.column(4).search(status).draw();
        },

        filterByOlt: function(oltId) {
            const table = $('#onuTable').DataTable();
            table.column(2).search(oltId).draw();
        },

        getStatusBadge: function(status) {
            const statusClass = {
                'online': 'success',
                'offline': 'danger',
                'los': 'warning',
                'dying_gasp': 'danger'
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
        OnuManagement.init();
    });

})(jQuery);