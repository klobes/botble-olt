/**
 * DataTables Initialization
 * Common DataTables configuration for all tables
 */

(function($) {
    'use strict';

    const DataTablesInit = {
        defaultOptions: {
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries to show",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-secondary'
                }
            ]
        },

        init: function() {
            this.initOltDevicesTable();
            this.initOnuTable();
            this.initBandwidthProfilesTable();
        },

        initOltDevicesTable: function() {
            if (!$('#oltDevicesTable').length) return;

            $('#oltDevicesTable').DataTable($.extend({}, this.defaultOptions, {
                ajax: {
                    url: '/api/fiberhome-olt/devices/datatable',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'ip_address', name: 'ip_address' },
                    { data: 'model', name: 'model' },
                    { data: 'vendor', name: 'vendor' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data) {
                            const statusClass = {
                                'online': 'success',
                                'offline': 'danger',
                                'error': 'warning'
                            };
                            return `<span class="badge bg-${statusClass[data] || 'secondary'}">${data}</span>`;
                        }
                    },
                    { 
                        data: 'onu_count', 
                        name: 'onu_count',
                        className: 'text-center'
                    },
                    { 
                        data: 'actions', 
                        name: 'actions', 
                        orderable: false, 
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[0, 'desc']]
            }));
        },

        initOnuTable: function() {
            if (!$('#onuTable').length) return;

            $('#onuTable').DataTable($.extend({}, this.defaultOptions, {
                ajax: {
                    url: '/api/fiberhome-olt/onus/datatable',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'olt_name', name: 'olt_device.name' },
                    { data: 'pon_port', name: 'pon_port' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data) {
                            const statusClass = {
                                'online': 'success',
                                'offline': 'danger',
                                'los': 'warning',
                                'dying_gasp': 'danger'
                            };
                            return `<span class="badge bg-${statusClass[data] || 'secondary'}">${data}</span>`;
                        }
                    },
                    { 
                        data: 'rx_power', 
                        name: 'rx_power',
                        render: function(data) {
                            return data ? data + ' dBm' : 'N/A';
                        }
                    },
                    { 
                        data: 'distance', 
                        name: 'distance',
                        render: function(data) {
                            return data ? data + ' m' : 'N/A';
                        }
                    },
                    { data: 'customer_name', name: 'customer_name' },
                    { 
                        data: 'actions', 
                        name: 'actions', 
                        orderable: false, 
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[0, 'desc']]
            }));
        },

        initBandwidthProfilesTable: function() {
            if (!$('#bandwidthProfilesTable').length) return;

            $('#bandwidthProfilesTable').DataTable($.extend({}, this.defaultOptions, {
                ajax: {
                    url: '/api/fiberhome-olt/bandwidth-profiles/datatable',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { 
                        data: 'downstream_rate', 
                        name: 'downstream_rate',
                        render: function(data) {
                            return data + ' Mbps';
                        }
                    },
                    { 
                        data: 'upstream_rate', 
                        name: 'upstream_rate',
                        render: function(data) {
                            return data + ' Mbps';
                        }
                    },
                    { 
                        data: 'priority', 
                        name: 'priority',
                        render: function(data) {
                            const priorityClass = {
                                'low': 'secondary',
                                'medium': 'info',
                                'high': 'warning',
                                'premium': 'success'
                            };
                            return `<span class="badge bg-${priorityClass[data] || 'secondary'}">${data}</span>`;
                        }
                    },
                    { 
                        data: 'onu_count', 
                        name: 'onu_count',
                        className: 'text-center'
                    },
                    { 
                        data: 'actions', 
                        name: 'actions', 
                        orderable: false, 
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[0, 'desc']]
            }));
        },

        // Utility function to reload table
        reloadTable: function(tableId) {
            const table = $(tableId).DataTable();
            if (table) {
                table.ajax.reload(null, false);
            }
        },

        // Utility function to clear table filters
        clearFilters: function(tableId) {
            const table = $(tableId).DataTable();
            if (table) {
                table.search('').columns().search('').draw();
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        DataTablesInit.init();

        // Global reload function
        window.reloadDataTable = function(tableId) {
            DataTablesInit.reloadTable(tableId);
        };

        // Global clear filters function
        window.clearDataTableFilters = function(tableId) {
            DataTablesInit.clearFilters(tableId);
        };
    });

})(jQuery);