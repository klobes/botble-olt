/**
 * Settings JavaScript
 * Handles plugin settings and configuration
 */

(function($) {
    'use strict';

    const Settings = {
        init: function() {
            this.bindEvents();
            this.initValidation();
        },

        bindEvents: function() {
            const self = this;

            // Settings form submission
            $('#settingsForm').on('submit', function(e) {
                e.preventDefault();
                self.saveSettings();
            });

            // Test SNMP settings
            $('#testSnmpBtn').on('click', function(e) {
                e.preventDefault();
                self.testSnmpSettings();
            });

            // Reset to defaults
            $('#resetDefaultsBtn').on('click', function(e) {
                e.preventDefault();
                self.resetToDefaults();
            });

            // Clear cache
            $('#clearCacheBtn').on('click', function(e) {
                e.preventDefault();
                self.clearCache();
            });

            // Export settings
            $('#exportSettingsBtn').on('click', function(e) {
                e.preventDefault();
                self.exportSettings();
            });

            // Import settings
            $('#importSettingsBtn').on('click', function(e) {
                e.preventDefault();
                $('#importSettingsFile').click();
            });

            $('#importSettingsFile').on('change', function(e) {
                self.importSettings(e.target.files[0]);
            });

            // SNMP version change
            $('#snmpVersion').on('change', function() {
                self.toggleSnmpVersionFields($(this).val());
            });
        },

        initValidation: function() {
            // Add custom validation rules
            $.validator.addMethod('ipAddress', function(value, element) {
                return this.optional(element) || /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/.test(value);
            }, 'Please enter a valid IP address');

            // Initialize form validation
            $('#settingsForm').validate({
                rules: {
                    snmp_timeout: {
                        required: true,
                        number: true,
                        min: 1000,
                        max: 30000
                    },
                    snmp_retries: {
                        required: true,
                        number: true,
                        min: 1,
                        max: 10
                    },
                    polling_interval: {
                        required: true,
                        number: true,
                        min: 60,
                        max: 3600
                    },
                    cache_ttl: {
                        required: true,
                        number: true,
                        min: 60,
                        max: 86400
                    }
                },
                messages: {
                    snmp_timeout: {
                        required: 'SNMP timeout is required',
                        number: 'Must be a number',
                        min: 'Minimum value is 1000ms',
                        max: 'Maximum value is 30000ms'
                    },
                    snmp_retries: {
                        required: 'SNMP retries is required',
                        number: 'Must be a number',
                        min: 'Minimum value is 1',
                        max: 'Maximum value is 10'
                    },
                    polling_interval: {
                        required: 'Polling interval is required',
                        number: 'Must be a number',
                        min: 'Minimum value is 60 seconds',
                        max: 'Maximum value is 3600 seconds'
                    },
                    cache_ttl: {
                        required: 'Cache TTL is required',
                        number: 'Must be a number',
                        min: 'Minimum value is 60 seconds',
                        max: 'Maximum value is 86400 seconds'
                    }
                }
            });
        },

        saveSettings: function() {
            const self = this;

            if (!$('#settingsForm').valid()) {
                return;
            }

            const formData = $('#settingsForm').serialize();

            $.ajax({
                url: '/admin/fiberhome-olt/settings/update',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    self.showSuccess('Settings saved successfully');
                },
                error: function(xhr) {
                    self.showError(xhr.responseJSON?.message || 'Failed to save settings');
                }
            });
        },

        testSnmpSettings: function() {
            const self = this;

            const testData = {
                snmp_version: $('#snmpVersion').val(),
                snmp_timeout: $('#snmpTimeout').val(),
                snmp_retries: $('#snmpRetries').val()
            };

            $('#testSnmpBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');

            $.ajax({
                url: '/admin/fiberhome-olt/settings/test-snmp',
                method: 'POST',
                data: testData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('SNMP settings are valid');
                    } else {
                        self.showError('SNMP test failed: ' + response.message);
                    }
                },
                error: function(xhr) {
                    self.showError('SNMP test failed');
                },
                complete: function() {
                    $('#testSnmpBtn').prop('disabled', false).html('<i class="fa fa-check"></i> Test SNMP');
                }
            });
        },

        resetToDefaults: function() {
            const self = this;

            Swal.fire({
                title: 'Reset to Defaults?',
                text: "This will restore all settings to default values",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, reset!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/fiberhome-olt/settings/reset',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('Settings reset to defaults');
                            location.reload();
                        },
                        error: function(xhr) {
                            self.showError('Failed to reset settings');
                        }
                    });
                }
            });
        },

        clearCache: function() {
            const self = this;

            Swal.fire({
                title: 'Clear Cache?',
                text: "This will clear all cached data",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/fiberhome-olt/settings/clear-cache',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('Cache cleared successfully');
                        },
                        error: function(xhr) {
                            self.showError('Failed to clear cache');
                        }
                    });
                }
            });
        },

        exportSettings: function() {
            const self = this;

            $.ajax({
                url: '/admin/fiberhome-olt/settings/export',
                method: 'GET',
                success: function(response) {
                    const dataStr = JSON.stringify(response.data, null, 2);
                    const dataBlob = new Blob([dataStr], { type: 'application/json' });
                    const url = URL.createObjectURL(dataBlob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'fiberhome-olt-settings-' + Date.now() + '.json';
                    link.click();
                    URL.revokeObjectURL(url);
                    
                    self.showSuccess('Settings exported successfully');
                },
                error: function(xhr) {
                    self.showError('Failed to export settings');
                }
            });
        },

        importSettings: function(file) {
            const self = this;

            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    
                    $.ajax({
                        url: '/admin/fiberhome-olt/settings/import',
                        method: 'POST',
                        data: { settings: settings },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            self.showSuccess('Settings imported successfully');
                            location.reload();
                        },
                        error: function(xhr) {
                            self.showError(xhr.responseJSON?.message || 'Failed to import settings');
                        }
                    });
                } catch (error) {
                    self.showError('Invalid settings file');
                }
            };
            reader.readAsText(file);
        },

        toggleSnmpVersionFields: function(version) {
            if (version === 'v3') {
                $('#snmpV3Fields').show();
                $('#snmpCommunityField').hide();
            } else {
                $('#snmpV3Fields').hide();
                $('#snmpCommunityField').show();
            }
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
        Settings.init();
    });

})(jQuery);