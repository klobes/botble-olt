<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceConfiguration extends BaseModel
{
    protected $table = 'om_service_configurations';

    protected $fillable = [
        'onu_port_id',
        'service_id',
        'service_type',
        'cvlan_mode',
        'cvlan',
        'cvlan_cos',
        'tvlan',
        'tvlan_cos',
        'svlan',
        'svlan_cos',
        'up_min_bandwidth',
        'up_max_bandwidth',
        'down_bandwidth',
        'service_vlan_name',
        'qinq_profile_name',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'cvlan' => 'integer',
        'cvlan_cos' => 'integer',
        'tvlan' => 'integer',
        'tvlan_cos' => 'integer',
        'svlan' => 'integer',
        'svlan_cos' => 'integer',
        'up_min_bandwidth' => 'integer',
        'up_max_bandwidth' => 'integer',
        'down_bandwidth' => 'integer',
    ];

    public function onuPort(): BelongsTo
    {
        return $this->belongsTo(OnuPort::class);
    }

    public function isUnicast(): bool
    {
        return $this->service_type === 'unicast';
    }

    public function isMulticast(): bool
    {
        return $this->service_type === 'multicast';
    }
}