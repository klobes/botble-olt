<?php

namespace Botble\FiberhomeOltManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnuType extends BaseModel
{
    protected $table = 'om_onu_types';

    protected $fillable = [
        'vendor',
        'model',
        'type_name',
        'ethernet_ports',
        'pots_ports',
        'catv_ports',
        'wifi_support',
        'capabilities',
        'default_config',
        'description',
    ];

    protected $casts = [
        'ethernet_ports' => 'integer',
        'pots_ports' => 'integer',
        'catv_ports' => 'integer',
        'wifi_support' => 'boolean',
        'capabilities' => 'array',
        'default_config' => 'array',
    ];

    public function vendorConfiguration(): BelongsTo
    {
        return $this->belongsTo(VendorConfiguration::class, 'vendor', 'vendor');
    }

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

    public function getPortsSummaryAttribute(): string
    {
        $ports = [];

        if ($this->ethernet_ports > 0) {
            $ports[] = "Eth: {$this->ethernet_ports}";
        }

        if ($this->pots_ports > 0) {
            $ports[] = "POTS: {$this->pots_ports}";
        }

        if ($this->catv_ports > 0) {
            $ports[] = "CATV: {$this->catv_ports}";
        }

        return implode(', ', $ports);
    }

    public function getCapabilitiesSummaryAttribute(): string
    {
        $capabilities = [];

        if ($this->wifi_support) {
            $capabilities[] = 'WiFi';
        }

        if (isset($this->capabilities['supports_voice']) && $this->capabilities['supports_voice']) {
            $capabilities[] = 'Voice';
        }

        if (isset($this->capabilities['supports_data']) && $this->capabilities['supports_data']) {
            $capabilities[] = 'Data';
        }

        if (isset($this->capabilities['supports_catv']) && $this->capabilities['supports_catv']) {
            $capabilities[] = 'CATV';
        }

        return implode(', ', $capabilities);
    }

    public function hasCapability(string $capability): bool
    {
        return isset($this->capabilities[$capability]) && $this->capabilities[$capability];
    }
}