<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BandwidthProfile extends BaseModel
{
    protected $table = 'om_bandwidth_profiles';

    protected $fillable = [
        'olt_device_id',
        'profile_id',
        'profile_name',
        'up_min_rate',
        'up_max_rate',
        'down_min_rate',
        'down_max_rate',
        'fixed_rate',
		'status',
    ];

    protected $casts = [
        'profile_id' => 'integer',
        'up_min_rate' => 'integer',
        'up_max_rate' => 'integer',
        'down_min_rate' => 'integer',
        'down_max_rate' => 'integer',
        'fixed_rate' => 'integer',
    ];
	 public function onus()
    {
        return $this->belongsTo(Onu::class);
    }

    public function oltDevice(): BelongsTo
    {
        return $this->belongsTo(OltDevice::class);
    }

    public function getUpMinRateMbps(): float
    {
        return $this->up_min_rate / 1000;
    }

    public function getUpMaxRateMbps(): float
    {
        return $this->up_max_rate / 1000;
    }

    public function getDownMinRateMbps(): float
    {
        return $this->down_min_rate / 1000;
    }

    public function getDownMaxRateMbps(): float
    {
        return $this->down_max_rate / 1000;
    }
}