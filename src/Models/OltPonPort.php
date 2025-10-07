<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OltPonPort extends BaseModel
{
    protected $table = 'om_olt_pon_ports';

    protected $fillable = [
        'olt_device_id',
        'olt_card_id',
        'pon_index',
        'pon_name',
        'description',
        'pon_type',
        'is_enabled',
        'online_status',
        'speed',
        'upstream_speed',
        'tx_optical_power',
        'optical_voltage',
        'optical_current',
        'optical_temperature',
        'auth_onu_num',
    ];

    protected $casts = [
        'pon_index' => 'integer',
        'pon_type' => 'integer',
        'is_enabled' => 'boolean',
        'speed' => 'integer',
        'upstream_speed' => 'integer',
        'tx_optical_power' => 'integer',
        'optical_voltage' => 'integer',
        'optical_current' => 'integer',
        'optical_temperature' => 'integer',
        'auth_onu_num' => 'integer',
    ];

    public function oltDevice(): BelongsTo
    {
        return $this->belongsTo(OltDevice::class);
    }

    public function oltCard(): BelongsTo
    {
        return $this->belongsTo(OltCard::class);
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    public function isOnline(): bool
    {
        return $this->online_status === 'online';
    }

    public function getOpticalPowerDbm(): ?float
    {
        if ($this->tx_optical_power === null) {
            return null;
        }
        
        // Convert from internal format to dBm
        return $this->tx_optical_power / 100;
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
}