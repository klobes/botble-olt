<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OLT extends BaseModel
{
    protected $table = 'om_olts';

    protected $fillable = [
        'name',
        'ip_address',
        'vendor',
        'model',
        'firmware_version',
        'serial_number',
        'snmp_community',
        'snmp_version',
        'snmp_port',
        'location',
        'description',
        'status',
        'last_seen',
        'last_polled',
        'system_info',
        'is_active',
        'cpu_usage',
        'memory_usage',
        'temperature',
        'uptime',
        'max_onus',
        'max_ports',
        'technology',
    ];

    protected $casts = [
        'system_info' => 'array',
        'technology' => 'array',
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'last_polled' => 'datetime',
        'cpu_usage' => 'decimal:2',
        'memory_usage' => 'decimal:2',
        'temperature' => 'decimal:2',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(OltCard::class, 'olt_id');
    }

    public function ponPorts(): HasMany
    {
        return $this->hasMany(OltPonPort::class, 'olt_id');
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class, 'olt_id');
    }

    public function bandwidthProfiles(): HasMany
    {
        return $this->hasMany(BandwidthProfile::class, 'olt_id');
    }

    public function performanceLogs(): HasMany
    {
        return $this->hasMany(OltPerformanceLog::class, 'olt_id');
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function getSnmpConnectionString(): string
    {
        return sprintf(
            '%s:%s@%s:%d',
            $this->snmp_version,
            $this->snmp_community,
            $this->ip_address,
            $this->snmp_port
        );
    }
}