<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnuPort extends BaseModel
{
    protected $table = 'om_onu_ports';

    protected $fillable = [
        'onu_id',
        'port_index',
        'port_name',
        'description',
        'port_type',
        'is_enabled',
        'online_status',
        'speed',
        'duplex',
        'auto_negotiation',
        'flow_control',
        'mac_address',
        'default_vlan',
    ];

    protected $casts = [
        'port_index' => 'integer',
        'port_type' => 'integer',
        'is_enabled' => 'boolean',
        'speed' => 'integer',
        'duplex' => 'boolean',
        'auto_negotiation' => 'boolean',
        'flow_control' => 'boolean',
        'default_vlan' => 'integer',
    ];

    public function onu(): BelongsTo
    {
        return $this->belongsTo(Onu::class);
    }

    public function serviceConfigurations(): HasMany
    {
        return $this->hasMany(ServiceConfiguration::class);
    }

    public function isUp(): bool
    {
        return $this->online_status === 'up';
    }

    public function getSpeedText(): string
    {
        $speeds = [
            0 => '10M',
            1 => '100M',
            2 => '1000M',
        ];

        return $speeds[$this->speed] ?? 'Unknown';
    }

    public function getDuplexText(): string
    {
        return $this->duplex ? 'Full' : 'Half';
    }
}