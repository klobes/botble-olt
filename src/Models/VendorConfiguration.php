<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;

class VendorConfiguration extends BaseModel
{
    protected $table = 'om_vendor_configurations';

    protected $fillable = [
        'vendor',
        'model',
        'oid_mappings',
        'capabilities',
        'default_settings',
        'notes',
    ];

    protected $casts = [
        'oid_mappings' => 'array',
        'capabilities' => 'array',
        'default_settings' => 'array',
    ];

    public function getVendorLabelAttribute(): string
    {
        $vendors = [
            'fiberhome' => 'Fiberhome',
            'huawei' => 'Huawei',
            'zte' => 'ZTE',
            'other' => 'Other',
        ];

        return $vendors[$this->vendor] ?? 'Unknown';
    }

    public function getVendorBadgeClassAttribute(): string
    {
        return match($this->vendor) {
            'fiberhome' => 'badge-primary',
            'huawei' => 'badge-danger',
            'zte' => 'badge-warning',
            'other' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function getCapabilitiesSummaryAttribute(): string
    {
        $summary = [];

        if (isset($this->capabilities['max_onus'])) {
            $summary[] = 'Max ONUs: ' . $this->capabilities['max_onus'];
        }

        if (isset($this->capabilities['max_distance'])) {
            $summary[] = 'Max Distance: ' . $this->capabilities['max_distance'] . 'm';
        }

        if (isset($this->capabilities['supports_qinq']) && $this->capabilities['supports_qinq']) {
            $summary[] = 'QinQ';
        }

        if (isset($this->capabilities['supports_vlan']) && $this->capabilities['supports_vlan']) {
            $summary[] = 'VLAN';
        }

        return implode(', ', $summary);
    }
}