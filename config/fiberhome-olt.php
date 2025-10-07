<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SNMP Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurimi i lidhjes SNMP me OLT
    |
    */
    'snmp' => [
        'version' => env('OLT_SNMP_VERSION', '2c'),
        'community' => env('OLT_SNMP_COMMUNITY', 'public'),
        'timeout' => env('OLT_SNMP_TIMEOUT', 1000000), // microseconds
        'retries' => env('OLT_SNMP_RETRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fiberhome MIB OIDs
    |--------------------------------------------------------------------------
    |
    | OID-të kryesore nga Fiberhome MIB
    |
    */
    'oids' => [
        'base' => '1.3.6.1.4.1.5875.800.3',
        
        // ONU White List
        'onu_whitelist' => [
            'physical' => '1.3.6.1.4.1.5875.800.3.1.1',
            'logical' => '1.3.6.1.4.1.5875.800.3.1.2',
        ],
        
        // Interface Enable
        'interface_enable' => [
            'voice_port' => '1.3.6.1.4.1.5875.800.3.2.1',
            'data_port' => '1.3.6.1.4.1.5875.800.3.2.2',
            'olt_pon' => '1.3.6.1.4.1.5875.800.3.2.3',
        ],
        
        // System Info
        'system_info' => [
            'frame' => '1.3.6.1.4.1.5875.800.3.9.1.1',
            'card' => '1.3.6.1.4.1.5875.800.3.9.2.1',
            'port' => '1.3.6.1.4.1.5875.800.3.9.3.1',
            'olt_pon' => '1.3.6.1.4.1.5875.800.3.9.3.4',
            'onu_pon' => '1.3.6.1.4.1.5875.800.3.9.3.3',
        ],
        
        // Performance
        'performance' => [
            'cpu' => '1.3.6.1.4.1.5875.800.3.8.6.1.1',
            'memory' => '1.3.6.1.4.1.5875.800.3.8.6.1.2',
            'temperature' => '1.3.6.1.4.1.5875.800.3.8.6.1.3',
        ],
        
        // Bandwidth Profile
        'bandwidth_profile' => '1.3.6.1.4.1.5875.800.3.3.1',
        
        // Port Attribute Profile
        'port_attribute_profile' => '1.3.6.1.4.1.5875.800.3.3.3',
        
        // Service Configuration
        'service_config' => '1.3.6.1.4.1.5875.800.3.5.1',
        
        // QINQ
        'qinq' => [
            'profile' => '1.3.6.1.4.1.5875.800.3.7.2',
            'domain' => '1.3.6.1.4.1.5875.800.3.7.7',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Polling Intervals
    |--------------------------------------------------------------------------
    |
    | Intervalet e polling për të dhëna të ndryshme (në sekonda)
    |
    */
    'polling' => [
        'system_info' => 300, // 5 minuta
        'performance' => 60, // 1 minutë
        'onu_status' => 30, // 30 sekonda
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 300, // 5 minuta
        'prefix' => 'fiberhome_olt_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts & Thresholds
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'cpu_threshold' => 80, // %
        'memory_threshold' => 85, // %
        'temperature_threshold' => 70, // °C
        'optical_power_min' => -28, // dBm
        'optical_power_max' => -8, // dBm
    ],
];