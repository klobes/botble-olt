<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiberCable extends BaseModel
{
    protected $table = 'om_fiber_cables';

    protected $fillable = [
        'cable_code',
        'cable_name',
        'cable_type',
        'fiber_count',
        'length',
        'manufacturer',
        'model',
        'installation_date',
        'description',
        'specifications',
        'status',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'specifications' => 'array',
        'length' => 'decimal:2',
    ];

    public function cableSegments(): HasMany
    {
        return $this->hasMany(CableSegment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDamaged(): bool
    {
        return $this->status === 'damaged';
    }

    public function getTypeLabelAttribute(): string
    {
        $types = [
            'single_mode' => 'Single Mode',
            'multi_mode' => 'Multi Mode',
            'armored' => 'Armored',
            'aerial' => 'Aerial',
            'underground' => 'Underground',
        ];

        return $types[$this->cable_type] ?? 'Unknown';
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'damaged' => 'Damaged',
            'maintenance' => 'Maintenance',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'damaged' => 'badge-danger',
            'maintenance' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}