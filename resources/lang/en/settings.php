<?php

return [
    'title' => 'FiberHome OLT Manager Settings',
    'description' => 'Configure your FiberHome OLT Manager plugin settings',
    'updated_success' => 'Settings updated successfully!',
    'updated_error' => 'Failed to update settings',
    'reset_success' => 'Settings reset to defaults successfully!',
    'reset_error' => 'Failed to reset settings',
    'connection_success' => 'Connection test successful!',
    'connection_error' => 'Connection test failed',
    'export_error' => 'Failed to export settings',
    'reset_confirm' => 'Are you sure you want to reset all settings to defaults?',

    // SNMP Settings
    'snmp_settings' => 'SNMP Settings',
    'snmp_timeout' => 'SNMP Timeout',
    'snmp_timeout_help' => 'Timeout in milliseconds for SNMP requests (1000-30000ms)',
    'snmp_retries' => 'SNMP Retries',
    'snmp_retries_help' => 'Number of retry attempts for failed SNMP requests (1-10)',
    'default_snmp_community' => 'Default SNMP Community',
    'default_snmp_community_help' => 'Default SNMP community string for new devices',
    'default_snmp_version' => 'Default SNMP Version',
    'default_snmp_version_help' => 'Default SNMP version for new devices',

    // Polling Settings
    'polling_settings' => 'Polling Settings',
    'polling_interval' => 'Polling Interval',
    'polling_interval_help' => 'Interval in seconds between device polls (60-3600s)',
    'discovery_timeout' => 'Discovery Timeout',
    'discovery_timeout_help' => 'Timeout in milliseconds for device discovery (10000-120000ms)',
    'max_concurrent_polls' => 'Max Concurrent Polls',
    'max_concurrent_polls_help' => 'Maximum number of concurrent polling processes (1-20)',
    'cache_ttl' => 'Cache TTL',
    'cache_ttl_help' => 'Time in seconds to cache SNMP data (60-3600s)',

    // Alert Settings
    'alert_settings' => 'Alert Settings',
    'alert_threshold_cpu' => 'CPU Alert Threshold',
    'alert_threshold_memory' => 'Memory Alert Threshold',
    'alert_threshold_temperature' => 'Temperature Alert Threshold',
    'enable_alerts' => 'Enable System Alerts',
    'enable_email_alerts' => 'Enable Email Alerts',
    'enable_webhook_alerts' => 'Enable Webhook Alerts',
    'enable_auto_discovery' => 'Enable Auto Discovery',
    'email_recipients' => 'Email Recipients',
    'email_recipients_help' => 'Comma-separated list of email addresses for alerts',
    'webhook_url' => 'Webhook URL',
    'webhook_url_help' => 'URL to send webhook alerts (POST request)',

    // Topology Settings
    'topology_settings' => 'Topology Settings',
    'topology_grid_size' => 'Grid Size',
    'topology_grid_size_help' => 'Size of grid cells for topology layout (10-100px)',
    'topology_auto_layout' => 'Enable Auto Layout',
    'topology_show_labels' => 'Show Device Labels',
    'topology_color_scheme' => 'Color Scheme',
    'color_scheme_default' => 'Default',
    'color_scheme_dark' => 'Dark',
    'color_scheme_light' => 'Light',
    'color_scheme_high_contrast' => 'High Contrast',

    // Advanced Settings
    'advanced_settings' => 'Advanced Settings',
    'log_level' => 'Log Level',
    'log_level_help' => 'Logging level for debugging',
    'log_level_debug' => 'Debug',
    'log_level_info' => 'Info',
    'log_level_warning' => 'Warning',
    'log_level_error' => 'Error',
    'maintenance_mode' => 'Maintenance Mode',
    'enable_maintenance_mode' => 'Enable Maintenance Mode',
    'maintenance_mode_help' => 'Disable all polling and discovery processes',

    // Statistics
    'statistics' => 'System Statistics',
    'total_olts' => 'Total OLTs',
    'total_onus' => 'Total ONUs',
    'total_bandwidth_profiles' => 'Total Bandwidth Profiles',

    // Actions
    'save_changes' => 'Save Changes',
    'test_connection' => 'Test Connection',
    'reset_defaults' => 'Reset Defaults',
    'export' => 'Export Settings',
    'import' => 'Import Settings',
];