<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\ONU;
use Botble\FiberHomeOLTManager\Models\FiberCable;
use Botble\FiberHomeOLTManager\Models\JunctionBox;
use Botble\FiberHomeOLTManager\Models\OltPort;
use Botble\FiberHomeOLTManager\Models\OnuPort;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TopologyService
{
    protected $snmpService;
    protected $oltService;

    public function __construct(SNMPService $snmpService, OLTService $oltService)
    {
        $this->snmpService = $snmpService;
        $this->oltService = $oltService;
    }

    /**
     * Get complete network topology with drag/drop positions
     */
    public function getNetworkTopology(): array
    {
        return Cache::remember('fiberhome_topology', 300, function () {
            $topology = [
                'nodes' => $this->getNodes(),
                'connections' => $this->getConnections(),
                'cables' => $this->getCables(),
                'junction_boxes' => $this->getJunctionBoxes(),
            ];

            return $this->applyDragDropPositions($topology);
        });
    }

    /**
     * Get all network nodes (OLTs, ONUs, Junction Boxes)
     */
    protected function getNodes(): Collection
    {
        $nodes = collect();

        // Add OLT nodes
        $olts = OLT::with(['ports' => function ($query) {
            $query->where('status', 'active');
        }])->get();

        foreach ($olts as $olt) {
            $nodes->push([
                'id' => 'olt_' . $olt->id,
                'type' => 'olt',
                'name' => $olt->name,
                'model' => $olt->model,
                'ip_address' => $olt->ip_address,
                'status' => $olt->status,
                'position' => $this->getNodePosition($olt->id, 'olt'),
                'ports' => $olt->ports->map(function ($port) {
                    return [
                        'id' => $port->id,
                        'name' => $port->name,
                        'type' => $port->port_type,
                        'status' => $port->status,
                        'connected_to' => $port->connected_to,
                    ];
                }),
                'icon' => $this->getNodeIcon('olt', $olt->status),
                'color' => $this->getNodeColor('olt', $olt->status),
                'draggable' => true,
                'resizable' => false,
            ]);
        }

        // Add ONU nodes
        $onus = ONU::with(['olt', 'bandwidthProfile'])->get();

        foreach ($onus as $onu) {
            $nodes->push([
                'id' => 'onu_' . $onu->id,
                'type' => 'onu',
                'name' => $onu->customer_name ?: $onu->serial_number,
                'serial_number' => $onu->serial_number,
                'olt_id' => $onu->olt_id,
                'olt_name' => $onu->olt->name,
                'status' => $onu->status,
                'position' => $this->getNodePosition($onu->id, 'onu'),
                'rx_power' => $onu->rx_power,
                'tx_power' => $onu->tx_power,
                'distance' => $onu->distance,
                'bandwidth_profile' => $onu->bandwidthProfile ? [
                    'name' => $onu->bandwidthProfile->name,
                    'download_speed' => $onu->bandwidthProfile->download_speed,
                    'upload_speed' => $onu->bandwidthProfile->upload_speed,
                ] : null,
                'icon' => $this->getNodeIcon('onu', $onu->status),
                'color' => $this->getNodeColor('onu', $onu->status),
                'draggable' => true,
                'resizable' => false,
            ]);
        }

        // Add Junction Box nodes
        $junctionBoxes = JunctionBox::all();

        foreach ($junctionBoxes as $jb) {
            $nodes->push([
                'id' => 'jb_' . $jb->id,
                'type' => 'junction_box',
                'name' => $jb->name,
                'location' => $jb->location,
                'capacity' => $jb->capacity,
                'used_ports' => $jb->used_ports,
                'available_ports' => $jb->capacity - $jb->used_ports,
                'ports' => $this->getJunctionBoxPorts($jb),
                'coordinates' => $jb->coordinates,
                'position' => $this->getNodePosition($jb->id, 'junction_box'),
                'icon' => $this->getNodeIcon('junction_box', 'active'),
                'color' => $this->getNodeColor('junction_box', 'active'),
                'draggable' => true,
                'resizable' => true,
            ]);
        }

        return $nodes;
    }

    /**
     * Get all network connections
     */
    protected function getConnections(): Collection
    {
        $connections = collect();

        // Get OLT to ONU connections
        $onus = ONU::with(['olt'])->whereNotNull('olt_id')->get();

        foreach ($onus as $onu) {
            $connections->push([
                'id' => 'conn_olt_' . $onu->olt_id . '_onu_' . $onu->id,
                'from' => 'olt_' . $onu->olt_id,
                'to' => 'onu_' . $onu->id,
                'type' => 'olt_onu',
                'status' => $onu->status,
                'cable_info' => $this->getCableInfo($onu->olt_id, $onu->id),
                'path' => $this->calculateCablePath($onu->olt_id, $onu->id),
                'custom_name' => $this->getConnectionName($onu),
                'editable' => true,
                'deletable' => true,
            ]);
        }

        // Get connections through junction boxes
        $cables = FiberCable::with(['fromDevice', 'toDevice'])->get();

        foreach ($cables as $cable) {
            $connections->push([
                'id' => 'conn_cable_' . $cable->id,
                'from' => $this->getDeviceIdentifier($cable->from_device_type, $cable->from_device_id),
                'to' => $this->getDeviceIdentifier($cable->to_device_type, $cable->to_device_id),
                'type' => 'fiber_cable',
                'status' => $cable->status,
                'cable_info' => [
                    'id' => $cable->id,
                    'name' => $cable->name,
                    'length' => $cable->length,
                    'fiber_count' => $cable->fiber_count,
                    'color' => $cable->color,
                    'notes' => $cable->notes,
                    'attenuation' => $this->calculateAttenuation($cable),
                    'splicing_points' => $cable->splicing_points,
                ],
                'path' => $this->getCablePath($cable),
                'custom_name' => $cable->name,
                'editable' => true,
                'deletable' => true,
            ]);
        }

        return $connections;
    }

    /**
     * Get fiber cables with detailed information
     */
    protected function getCables(): Collection
    {
        return FiberCable::with(['fromDevice', 'toDevice'])->get()->map(function ($cable) {
            return [
                'id' => $cable->id,
                'name' => $cable->name,
                'length' => $cable->length,
                'fiber_count' => $cable->fiber_count,
                'color' => $cable->color,
                'status' => $cable->status,
                'from_device' => [
                    'type' => $cable->from_device_type,
                    'id' => $cable->from_device_id,
                    'name' => $this->getDeviceName($cable->from_device_type, $cable->from_device_id),
                    'port' => $cable->from_port,
                ],
                'to_device' => [
                    'type' => $cable->to_device_type,
                    'id' => $cable->to_device_id,
                    'name' => $this->getDeviceName($cable->to_device_type, $cable->to_device_id),
                    'port' => $cable->to_port,
                ],
                'attenuation' => $this->calculateAttenuation($cable),
                'splicing_points' => $cable->splicing_points,
                'notes' => $cable->notes,
                'coordinates' => $cable->coordinates,
                'waypoints' => $cable->waypoints,
            ];
        });
    }

    /**
     * Get junction boxes with port information
     */
    protected function getJunctionBoxes(): Collection
    {
        return JunctionBox::all()->map(function ($jb) {
            return [
                'id' => $jb->id,
                'name' => $jb->name,
                'location' => $jb->location,
                'capacity' => $jb->capacity,
                'used_ports' => $jb->used_ports,
                'available_ports' => $jb->capacity - $jb->used_ports,
                'ports' => $this->getJunctionBoxPorts($jb),
                'coordinates' => $jb->coordinates,
                'notes' => $jb->notes,
            ];
        });
    }

    /**
     * Update node position for drag/drop functionality
     */
    public function updateNodePosition(string $nodeType, int $nodeId, array $position): bool
    {
        $cacheKey = "fiberhome_node_position_{$nodeType}_{$nodeId}";
        
        Cache::put($cacheKey, [
            'x' => $position['x'] ?? 0,
            'y' => $position['y'] ?? 0,
            'updated_at' => now(),
        ], 86400); // 24 hours

        return true;
    }

    /**
     * Create a new fiber cable connection
     */
    public function createCableConnection(array $data): FiberCable
    {
        $cable = FiberCable::create([
            'name' => $data['name'],
            'from_device_type' => $data['from_device_type'],
            'from_device_id' => $data['from_device_id'],
            'from_port' => $data['from_port'] ?? null,
            'to_device_type' => $data['to_device_type'],
            'to_device_id' => $data['to_device_id'],
            'to_port' => $data['to_port'] ?? null,
            'length' => $data['length'] ?? 0,
            'fiber_count' => $data['fiber_count'] ?? 1,
            'color' => $data['color'] ?? 'yellow',
            'status' => $data['status'] ?? 'active',
            'splicing_points' => $data['splicing_points'] ?? [],
            'coordinates' => $data['coordinates'] ?? [],
            'waypoints' => $data['waypoints'] ?? [],
            'notes' => $data['notes'] ?? '',
        ]);

        // Clear topology cache
        Cache::forget('fiberhome_topology');

        return $cable;
    }

    /**
     * Update cable connection
     */
    public function updateCableConnection(int $cableId, array $data): bool
    {
        $cable = FiberCable::findOrFail($cableId);
        $cable->update($data);

        // Clear topology cache
        Cache::forget('fiberhome_topology');

        return true;
    }

    /**
     * Delete cable connection
     */
    public function deleteCableConnection(int $cableId): bool
    {
        $cable = FiberCable::findOrFail($cableId);
        $cable->delete();

        // Clear topology cache
        Cache::forget('fiberhome_topology');

        return true;
    }

    /**
     * Get node position from cache or generate default
     */
    protected function getNodePosition(int $nodeId, string $nodeType): array
    {
        $cacheKey = "fiberhome_node_position_{$nodeType}_{$nodeId}";
        $position = Cache::get($cacheKey);

        if (!$position) {
            // Generate default position based on node type and ID
            $position = $this->generateDefaultPosition($nodeId, $nodeType);
            Cache::put($cacheKey, $position, 86400);
        }

        return $position;
    }

    /**
     * Generate default position for nodes
     */
    protected function generateDefaultPosition(int $nodeId, string $nodeType): array
    {
        $basePositions = [
            'olt' => ['x' => 100, 'y' => 100],
            'onu' => ['x' => 300, 'y' => 200],
            'junction_box' => ['x' => 200, 'y' => 150],
        ];

        $base = $basePositions[$nodeType] ?? ['x' => 150, 'y' => 150];
        
        // Add some randomness to avoid overlap
        $base['x'] += ($nodeId % 10) * 50;
        $base['y'] += ($nodeId % 5) * 40;

        return $base;
    }

    /**
     * Get node icon based on type and status
     */
    protected function getNodeIcon(string $type, string $status): string
    {
        $icons = [
            'olt' => $status === 'online' ? 'fa-server text-success' : 'fa-server text-danger',
            'onu' => match($status) {
                'online' => 'fa-wifi text-success',
                'offline' => 'fa-wifi text-danger',
                'dying_gasp' => 'fa-wifi text-warning',
                default => 'fa-wifi text-secondary',
            },
            'junction_box' => 'fa-square text-primary',
        ];

        return $icons[$type] ?? 'fa-circle text-secondary';
    }

    /**
     * Get node color based on type and status
     */
    protected function getNodeColor(string $type, string $status): string
    {
        $colors = [
            'olt' => $status === 'online' ? '#28a745' : '#dc3545',
            'onu' => match($status) {
                'online' => '#28a745',
                'offline' => '#dc3545',
                'dying_gasp' => '#ffc107',
                default => '#6c757d',
            },
            'junction_box' => '#007bff',
        ];

        return $colors[$type] ?? '#6c757d';
    }

    /**
     * Get junction box ports with connection info
     */
    protected function getJunctionBoxPorts($junctionBox): array
    {
        return collect(range(1, $junctionBox->capacity))->map(function ($portNumber) use ($junctionBox) {
            $connection = $this->getPortConnection($junctionBox->id, $portNumber);
            
            return [
                'port_number' => $portNumber,
                'status' => $connection ? 'connected' : 'available',
                'connected_to' => $connection ? $connection['device_name'] : null,
                'connection_type' => $connection ? $connection['type'] : null,
                'cable_info' => $connection ? $connection['cable_info'] : null,
                'custom_name' => "Port {$portNumber}",
                'editable' => true,
            ];
        })->toArray();
    }

    /**
     * Get connection info for a specific port
     */
    protected function getPortConnection(int $junctionBoxId, int $portNumber): ?array
    {
        $cable = FiberCable::where(function ($query) use ($junctionBoxId, $portNumber) {
            $query->where('from_device_type', 'junction_box')
                  ->where('from_device_id', $junctionBoxId)
                  ->where('from_port', $portNumber);
        })->orWhere(function ($query) use ($junctionBoxId, $portNumber) {
            $query->where('to_device_type', 'junction_box')
                  ->where('to_device_id', $junctionBoxId)
                  ->where('to_port', $portNumber);
        })->first();

        if (!$cable) {
            return null;
        }

        $deviceType = $cable->from_device_type === 'junction_box' ? $cable->to_device_type : $cable->from_device_type;
        $deviceId = $cable->from_device_type === 'junction_box' ? $cable->to_device_id : $cable->from_device_id;

        return [
            'type' => $deviceType,
            'device_name' => $this->getDeviceName($deviceType, $deviceId),
            'cable_info' => [
                'id' => $cable->id,
                'name' => $cable->name,
                'status' => $cable->status,
            ],
        ];
    }

    /**
     * Get device name by type and ID
     */
    protected function getDeviceName(string $type, int $id): string
    {
        return match ($type) {
            'olt' => OLT::find($id)?->name ?? "OLT {$id}",
            'onu' => ONU::find($id)?->customer_name ?? ONU::find($id)?->serial_number ?? "ONU {$id}",
            'junction_box' => JunctionBox::find($id)?->name ?? "JB {$id}",
            default => "Device {$id}",
        };
    }

    /**
     * Get device identifier
     */
    protected function getDeviceIdentifier(string $type, int $id): string
    {
        return "{$type}_{$id}";
    }

    /**
     * Calculate cable attenuation
     */
    protected function calculateAttenuation(FiberCable $cable): float
    {
        // Typical attenuation: 0.35 dB/km for single mode fiber
        $attenuationPerKm = 0.35;
        $spliceLoss = 0.1; // dB per splice
        $connectorLoss = 0.3; // dB per connector

        $fiberLoss = ($cable->length / 1000) * $attenuationPerKm;
        $spliceLossTotal = ($cable->splicing_points ?? 0) * $spliceLoss;
        $connectorLossTotal = 2 * $connectorLoss; // Assuming 2 connectors

        return round($fiberLoss + $spliceLossTotal + $connectorLossTotal, 2);
    }

    /**
     * Get cable info for OLT-ONU connection
     */
    protected function getCableInfo(int $oltId, int $onuId): array
    {
        // Find cables connecting OLT to ONU (possibly through junction boxes)
        $cables = FiberCable::where(function ($query) use ($oltId, $onuId) {
            $query->where('from_device_type', 'olt')
                  ->where('from_device_id', $oltId);
        })->orWhere(function ($query) use ($oltId, $onuId) {
            $query->where('to_device_type', 'onu')
                  ->where('to_device_id', $onuId);
        })->get();

        return $cables->map(function ($cable) {
            return [
                'id' => $cable->id,
                'name' => $cable->name,
                'length' => $cable->length,
                'status' => $cable->status,
            ];
        })->toArray();
    }

    /**
     * Calculate cable path for visualization
     */
    protected function calculateCablePath(int $oltId, int $onuId): array
    {
        // Simple straight line path - can be enhanced with routing algorithms
        return [
            'type' => 'straight',
            'coordinates' => [], // Will be calculated on frontend
            'waypoints' => [],
        ];
    }

    /**
     * Get cable path for existing cable
     */
    protected function getCablePath(FiberCable $cable): array
    {
        return [
            'type' => 'custom',
            'coordinates' => $cable->path_coordinates ?? [],
            'waypoints' => $cable->waypoints ?? [],
        ];
    }

    /**
     * Get connection name
     */
    protected function getConnectionName(ONU $onu): string
    {
        return "{$onu->olt->name} → {$onu->customer_name}";
    }

    /**
     * Apply drag/drop positions to topology
     */
    protected function applyDragDropPositions(array $topology): array
    {
        foreach ($topology['nodes'] as &$node) {
            $position = $this->getNodePosition(
                intval(str_replace(['olt_', 'onu_', 'jb_'], '', $node['id'])), 
                $node['type']
            );
            
            $node['position'] = $position;
            $node['draggable'] = true;
            $node['resizable'] = $node['type'] === 'junction_box';
        }

        return $topology;
    }

    /**
     * Clear topology cache
     */
    public function clearCache(): void
    {
        Cache::forget('fiberhome_topology');
        Cache::forget('fiberhome_node_positions');
    }
}