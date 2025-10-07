<?php

namespace Botble\FiberHomeOLTManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JunctionBox extends BaseModel
{
    protected $table = 'om_junction_boxes';

    protected $fillable = [
        'box_code',
        'box_name',
        'box_type',
        'capacity',
        'used_capacity',
        'latitude',
        'longitude',
        'address',
        'location_description',
        'access_code',
        'installation_date',
        'status',
        'photos',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'photos' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'used_capacity' => 'integer',
        'capacity' => 'integer',
    ];

    public function splitters(): HasMany
    {
        return $this->hasMany(Splitter::class);
    }

    public function spliceCassettes(): HasMany
    {
        return $this->hasMany(SpliceCassette::class);
    }

    public function cableSegments(): HasMany
    {
        return $this->hasMany(CableSegment::class, 'destination_id')
            ->where('destination_type', 'JunctionBox');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFull(): bool
    {
        return $this->used_capacity >= $this->capacity;
    }

    public function getTypeLabelAttribute(): string
    {
        $types = [
            'street' => 'Street',
            'building' => 'Building',
            'pole' => 'Pole',
            'underground' => 'Underground',
            'wall_mount' => 'Wall Mount',
        ];

        return $types[$this->box_type] ?? 'Unknown';
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'damaged' => 'Damaged',
            'full' => 'Full',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'damaged' => 'badge-danger',
            'full' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->capacity == 0) {
            return 0;
        }

        return ($this->used_capacity / $this->capacity) * 100;
    }

    public function getLocationAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->latitude}, {$this->longitude}";
        }

        return $this->address ?? 'No location';
    }
}