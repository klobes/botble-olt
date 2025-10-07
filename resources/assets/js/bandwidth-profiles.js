/**
 * Bandwidth Profiles JavaScript
 * Handles bandwidth profile management
 */

(function($) {
    'use strict';

    const BandwidthProfiles = {
        init: function() {
            this.bindEvents();
            this.initModals();
        },

        bindEvents: function() {
            const self = this;

            // Create profile button
            $('#createProfileBtn').on('click', function(e) {
                e.preventDefault();
                self.showCreateModal();
            });

            // Edit profile
            $(document).on('click', '.edit-profile', function(e) {
                e.preventDefault();
                const profileId = $(this).data('id');
                self.showEditModal(profileId);
            });

            // Delete profile
            $(document).on('click', '.delete-profile', function(e) {
                e.preventDefault();
                const profileId = $(this).data('id');
                self.deleteProfile(profileId);
            });

            // Assign profile
            $(document).on('click', '.assign-profile', function(e) {
                e.preventDefault();
                const profileId = $(this).data('id');
                self.showAssignModal(profileId);
            });

            // Form submissions
            $('#createProfileForm').on('submit', function(e) {
                e.preventDefault();
                self.submitCreateForm();
            });

            $('#editProfileForm').on('submit', function(e) {
                e.preventDefault();
                self.submitEditForm();
            });

            $('#assignProfileForm').on('submit', function(e) {
                e.preventDefault();
                self.submitAssignForm();
            });

            // Calculate guaranteed rate
            $('#downstreamRate, #guaranteedPercentage').on('input', function() {
                self.calculateGuaranteedRate();
            });

            $('#editDownstreamRate, #editGuaranteedPercentage').on('input', function() {
                self.calculateEditGuaranteedRate();
            });
        },

        initModals: function() {
            this.createModal = new bootstrap.Modal(document.getElementById('createProfileModal'));
            this.editModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            this.assignModal = new bootstrap.Modal(document.getElementById('assignProfileModal'));
        },

        showCreateModal: function() {
            $('#createProfileForm')[0].reset();
            this.createModal.show();
        },

        showEditModal: function(profileId) {
            const self = this;

            $.ajax({
                url: `/admin/fiberhome-olt/bandwidth-profiles/${profileId}`,
                method: 'GET',
                success: function(response) {
                    self.populateEditForm(response.data);
                    self.editModal.show();
                },
                error: function(xhr) {
                    self.showError('Failed to load profile data');
                }
            });
        },

        showAssignModal: function(profileId) {
            const self = this;

            $('#assignProfileId').val(profileId);
            
            // Load available ONUs
            $.ajax({
                url: '/admin/fiberhome-olt/onus/available',
                method: 'GET',
                success: function(response) {
                    self.populateOnuSelect(response.data);
                    self.assignModal.show();
                },
                error: function(xhr) {
                    self.showError('Failed to load ONUs');
                }
            });
        },

        populateEditForm: function(data) {
            $('#editProfileId').val(data.id);
            $('#editProfileName').val(data.name);
            $('#editDownstreamRate').val(data.downstream_rate);
            $('#editUpstreamRate').val(data.upstream_rate);
            $('#editPriority').val(data.priority);
            $('#editGuaranteedPercentage').val(data.guaranteed_percentage);
            $('#editFixedRate').prop('checked', data.fixed_rate);
            $('#editDescription').val(data.description);
            
            this.calculateEditGuaranteedRate();
        },

        populateOnuSelect: function(onus) {
            const $select = $('#assignOnuSelect');
            $select.empty();
            
            onus.forEach(function(onu) {
                $select.append(
                    $('<option>', {
                        value: onu.id,
                        text: `${onu.serial_number} - ${onu.customer_name || 'No customer'}`
                    })
                );
            });
        },

        submitCreateForm: function() {
            const self = this;
            const formData = $('#createProfileForm').serialize();

            $.ajax({
                url: '/admin/fiberhome-olt/bandwidth-profiles/create',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    self.createModal.hide();
                    self.showSuccess('Bandwidth profile created successfully');
                    window.reloadDataTable('#bandwidthProfilesTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to create profile');
                }
            });
        },

        submitEditForm: function() {
            const self = this;
            const profileId = $('#editProfileId').val();
            const formData = $('#editProfileForm').serialize();

            $.ajax({
                url: `/admin/fiberhome-olt/bandwidth-profiles/${profileId}`,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    self.editModal.hide();
                    self.showSuccess('Bandwidth profile updated successfully');
                    window.reloadDataTable('#bandwidthProfilesTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to update profile');
                }
            });
        },

        submitAssignForm: function() {
            const self = this;
            const profileId = $('#assignProfileId').val();
            const formData = $('#assignProfileForm').serialize();

            $.ajax({
                url: `/admin/fiberhome-olt/bandwidth-profiles/${profileId}/assign`,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    self.assignModal.hide();
                    self.showSuccess('Profile assigned successfully');
                    window.reloadDataTable('#bandwidthProfilesTable');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to assign profile');
                }
            });
        },

        deleteProfile: function(profileId) {
            const self = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "This will affect all ONUs using this profile",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/fiberhome-olt/bandwidth-profiles/${profileId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('Profile deleted successfully');
                            window.reloadDataTable('#bandwidthProfilesTable');
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to delete profile');
                        }
                    });
                }
            });
        },

        calculateGuaranteedRate: function() {
            const downstreamRate = parseFloat($('#downstreamRate').val()) || 0;
            const percentage = parseFloat($('#guaranteedPercentage').val()) || 0;
            const guaranteedRate = (downstreamRate * percentage / 100).toFixed(2);
            
            $('#guaranteedRateDisplay').text(guaranteedRate + ' Mbps');
        },

        calculateEditGuaranteedRate: function() {
            const downstreamRate = parseFloat($('#editDownstreamRate').val()) || 0;
            const percentage = parseFloat($('#editGuaranteedPercentage').val()) || 0;
            const guaranteedRate = (downstreamRate * percentage / 100).toFixed(2);
            
            $('#editGuaranteedRateDisplay').text(guaranteedRate + ' Mbps');
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
        BandwidthProfiles.init();
    });

})(jQuery);