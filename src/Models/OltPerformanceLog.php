<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltPerformanceLog extends BaseModel
{
    protected $table = 'om_olt_performance_logs';

    public $timestamps = false;

    protected $fillable = [
        'olt_device_id',
        'cpu_utilization',
        'memory_utilization',
        'temperature',
        'recorded_at',
    ];

    protected $casts = [
        'cpu_utilization' => 'integer',
        'memory_utilization' => 'integer',
        'temperature' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function oltDevice(): BelongsTo
    {
        return $this->belongsTo(OltDevice::class);
    }
}