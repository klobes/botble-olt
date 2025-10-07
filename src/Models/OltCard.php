<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OltCard extends BaseModel
{
    protected $table = 'om_olt_cards';

    protected $fillable = [
        'olt_device_id',
        'slot_index',
        'card_type',
        'card_type_name',
        'hardware_version',
        'software_version',
        'status',
        'num_of_ports',
        'available_ports',
        'cpu_util',
        'mem_util',
    ];

    protected $casts = [
        'slot_index' => 'integer',
        'card_type' => 'integer',
        'num_of_ports' => 'integer',
        'available_ports' => 'integer',
        'cpu_util' => 'integer',
        'mem_util' => 'integer',
    ];

    public function oltDevice(): BelongsTo
    {
        return $this->belongsTo(OltDevice::class);
    }

    public function ponPorts(): HasMany
    {
        return $this->hasMany(OltPonPort::class);
    }

    public function isNormal(): bool
    {
        return $this->status === 'normal';
    }

    public function getCardTypeName(): string
    {
        $types = [
            260 => 'EC2',
            724 => 'EC2-X',
            16384 => 'AC16',
        ];

        return $types[$this->card_type] ?? $this->card_type_name ?? 'Unknown';
    }
}