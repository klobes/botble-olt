<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Onu extends BaseModel
{
    protected $table = 'om_onus';

    protected $fillable = [
        'olt_id',
        'bandwidth_profile_id',
        'olt_pon_port_id',
        'onu_index',
        'onu_name',
        'description',
        'mac_address',
        'serial_number',
        'password',
        'onu_type',
        'auth_type',
        'is_enabled',
        'status',
        'distance',
        'rx_optical_power',
        'tx_optical_power',
        'optical_voltage',
        'optical_current',
        'optical_temperature',
        'last_online',
        'last_offline',
        'last_seen',
		
    ];

    protected $casts = [
        'onu_index' => 'integer',
        'onu_type' => 'integer',
        'is_enabled' => 'boolean',
        'distance' => 'integer',
        'rx_optical_power' => 'integer',
        'tx_optical_power' => 'integer',
        'optical_voltage' => 'integer',
        'optical_current' => 'integer',
        'optical_temperature' => 'integer',
        'last_online' => 'datetime',
        'last_offline' => 'datetime',
    ];
	 public function olt()
    {
        return $this->belongsTo(OLT::class, 'olt_id');
    }
    
//    public function oltDevice()
//    {
//        return $this->belongsTo(OltDevice::class);
//    }
    
    public function bandwidthProfile()
    {
        return $this->belongsTo(BandwidthProfile::class, 'bandwidth_profile_id');
    }

    public function oltDevice(): BelongsTo
    {
        return $this->belongsTo(OltDevice::class);
    }

    public function oltPonPort(): BelongsTo
    {
        return $this->belongsTo(OltPonPort::class);
    }

    public function ports(): HasMany
    {
        return $this->hasMany(OnuPort::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function getRxOpticalPowerDbm(): ?float
    {
        if ($this->rx_optical_power === null) {
            return null;
        }
        
        return $this->rx_optical_power / 100;
    }

    public function getTxOpticalPowerDbm(): ?float
    {
        if ($this->tx_optical_power === null) {
            return null;
        }
        
        return $this->tx_optical_power / 100;
    }

    public function getDistanceKm(): ?float
    {
        if ($this->distance === null) {
            return null;
        }
        
        return $this->distance / 1000;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'online' => 'badge-success',
            'offline' => 'badge-secondary',
            'los' => 'badge-warning',
            'dying_gasp' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}