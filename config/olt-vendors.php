<?php

return [
    'vendors' => [
        'fiberhome' => [
            'name' => 'FiberHome',
            'models' => [
                'AN5516-01' => [
                    'name' => 'AN5516-01',
                    'description' => 'GPON OLT with 16 PON ports',
                    'max_ports' => 16,
                    'max_onus' => 1024,
                    'technology' => ['GPON'],
                ],
                'AN5516-04' => [
                    'name' => 'AN5516-04',
                    'description' => 'GPON OLT with 4 PON ports',
                    'max_ports' => 4,
                    'max_onus' => 256,
                    'technology' => ['GPON'],
                ],
                'AN5516-06' => [
                    'name' => 'AN5516-06',
                    'description' => 'GPON OLT with 6 PON ports',
                    'max_ports' => 6,
                    'max_onus' => 384,
                    'technology' => ['GPON'],
                ],
                'AN6000-17' => [
                    'name' => 'AN6000-17',
                    'description' => 'High-capacity GPON OLT',
                    'max_ports' => 17,
                    'max_onus' => 2048,
                    'technology' => ['GPON'],
                ],
                'AN5506-04' => [
                    'name' => 'AN5506-04',
                    'description' => 'Compact GPON OLT with 4 PON ports',
                    'max_ports' => 4,
                    'max_onus' => 256,
                    'technology' => ['GPON'],
                ],
            ],
        ],
        'huawei' => [
            'name' => 'Huawei',
            'models' => [
                'MA5608T' => [
                    'name' => 'MA5608T',
                    'description' => 'Optical Access OLT',
                    'max_ports' => 8,
                    'max_onus' => 512,
                    'technology' => ['GPON', 'EPON'],
                ],
                'MA5680T' => [
                    'name' => 'MA5680T',
                    'description' => 'High-density OLT',
                    'max_ports' => 16,
                    'max_onus' => 2048,
                    'technology' => ['GPON', 'EPON', '10G-GPON'],
                ],
                'MA5800-X7' => [
                    'name' => 'MA5800-X7',
                    'description' => 'Next-generation OLT',
                    'max_ports' => 32,
                    'max_onus' => 4096,
                    'technology' => ['GPON', '10G-GPON', 'XG-PON'],
                ],
                'MA5800-X15' => [
                    'name' => 'MA5800-X15',
                    'description' => 'Ultra high-capacity OLT',
                    'max_ports' => 64,
                    'max_onus' => 8192,
                    'technology' => ['GPON', '10G-GPON', 'XG-PON'],
                ],
            ],
        ],
        'zte' => [
            'name' => 'ZTE',
            'models' => [
                'C300' => [
                    'name' => 'C300',
                    'description' => 'Compact OLT',
                    'max_ports' => 8,
                    'max_onus' => 512,
                    'technology' => ['GPON', 'EPON'],
                ],
                'C320' => [
                    'name' => 'C320',
                    'description' => 'Medium-capacity OLT',
                    'max_ports' => 16,
                    'max_onus' => 2048,
                    'technology' => ['GPON', 'EPON', '10G-GPON'],
                ],
                'C600' => [
                    'name' => 'C600',
                    'description' => 'High-capacity OLT',
                    'max_ports' => 32,
                    'max_onus' => 4096,
                    'technology' => ['GPON', '10G-GPON', 'XG-PON'],
                ],
                'C650' => [
                    'name' => 'C650',
                    'description' => 'Ultra high-capacity OLT',
                    'max_ports' => 64,
                    'max_onus' => 8192,
                    'technology' => ['GPON', '10G-GPON', 'XG-PON', 'XGS-PON'],
                ],
            ],
        ],
    ],

    'default_snmp' => [
        'community' => 'public',
        'version' => '2c',
        'port' => 161,
    ],
];