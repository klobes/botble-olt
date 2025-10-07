<?php

return [
    [
        'name' => 'FiberHome OLT Manager',
        'flag' => 'fiberhome-olt-manager.index',
        'parent_flag' => 'plugins.index',
    ],
    [
        'name' => 'View Dashboard',
        'flag' => 'fiberhome-olt-manager.dashboard.index',
        'parent_flag' => 'fiberhome-olt-manager.index',
    ],
    [
        'name' => 'OLT Management',
        'flag' => 'fiberhome-olt-manager.olt.index',
        'parent_flag' => 'fiberhome-olt-manager.index',
    ],
    [
        'name' => 'Create OLT',
        'flag' => 'fiberhome-olt-manager.olt.create',
        'parent_flag' => 'fiberhome-olt-manager.olt.index',
    ],
    [
        'name' => 'Edit OLT',
        'flag' => 'fiberhome-olt-manager.olt.edit',
        'parent_flag' => 'fiberhome-olt-manager.olt.index',
    ],
    [
        'name' => 'Delete OLT',
        'flag' => 'fiberhome-olt-manager.olt.destroy',
        'parent_flag' => 'fiberhome-olt-manager.olt.index',
    ],
    [
        'name' => 'ONU Management',
        'flag' => 'fiberhome-olt-manager.onu.index',
        'parent_flag' => 'fiberhome-olt-manager.index',
    ],
    [
        'name' => 'Edit ONU',
        'flag' => 'fiberhome-olt-manager.onu.edit',
        'parent_flag' => 'fiberhome-olt-manager.onu.index',
    ],
    [
        'name' => 'Configure ONU',
        'flag' => 'fiberhome-olt-manager.onu.configure',
        'parent_flag' => 'fiberhome-olt-manager.onu.index',
    ],
    [
        'name' => 'Reboot ONU',
        'flag' => 'fiberhome-olt-manager.onu.reboot',
        'parent_flag' => 'fiberhome-olt-manager.onu.index',
    ],
    [
        'name' => 'Bandwidth Profiles',
        'flag' => 'fiberhome-olt-manager.bandwidth.index',
        'parent_flag' => 'fiberhome-olt-manager.index',
    ],
    [
        'name' => 'Create Bandwidth Profile',
        'flag' => 'fiberhome-olt-manager.bandwidth.create',
        'parent_flag' => 'fiberhome-olt-manager.bandwidth.index',
    ],
    [
        'name' => 'Edit Bandwidth Profile',
        'flag' => 'fiberhome-olt-manager.bandwidth.edit',
        'parent_flag' => 'fiberhome-olt-manager.bandwidth.index',
    ],
    [
        'name' => 'Delete Bandwidth Profile',
        'flag' => 'fiberhome-olt-manager.bandwidth.destroy',
        'parent_flag' => 'fiberhome-olt-manager.bandwidth.index',
    ],
    [
        'name' => 'Assign Bandwidth Profile',
        'flag' => 'fiberhome-olt-manager.bandwidth.assign',
        'parent_flag' => 'fiberhome-olt-manager.bandwidth.index',
    ],
    [
        'name' => 'Network Topology',
        'flag' => 'fiberhome-olt-manager.topology.index',
        'parent_flag' => 'fiberhome-olt-manager.index',
    ],
    [
        'name' => 'Settings',
        'flag' => 'fiberhome-olt-manager.settings.index',
        'parent_flag' => 'fiberhome-olt-manager.index',
    ],
];