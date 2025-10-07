<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\OltCard;
use Botble\FiberHomeOLTManager\Models\OltPonPort;
use Illuminate\Support\Facades\Log;

class OltVisualizationService
{
    protected $snmpManager;

    public function __construct(SnmpManager $snmpManager)
    {
        $this->snmpManager = $snmpManager;
    }

    /**
     * Get OLT physical structure for visualization
     */
    public function getOltStructure(OLT $olt): array
    {
        try {
            // Get model configuration
            $modelConfig = $this->getModelConfiguration($olt->model);
            
            // Get actual hardware configuration via SNMP
            $hardwareConfig = $this->getHardwareConfiguration($olt);
            
            // Merge configurations
            return [
                'model' => $olt->model,
                'vendor' => $olt->vendor,
                'chassis' => $modelConfig['chassis'],
                'slots' => $this->buildSlotStructure($olt, $modelConfig, $hardwareConfig),
                'ports' => $this->buildPortStructure($olt, $hardwareConfig),
                'power_supplies' => $modelConfig['power_supplies'] ?? [],
                'fans' => $modelConfig['fans'] ?? [],
                'dimensions' => $modelConfig['dimensions'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get OLT structure for {$olt->name}: " . $e->getMessage());
            return $this->getDefaultStructure($olt->model);
        }
    }

    /**
     * Get model-specific configuration
     */
    private function getModelConfiguration(string $model): array
    {
        $configurations = [
            'AN5516-01' => [
                'chassis' => [
                    'type' => 'compact',
                    'height' => '1U',
                    'slots' => 1,
                    'max_ports' => 16,
                ],
                'power_supplies' => [
                    ['slot' => 'PS1', 'type' => 'AC', 'status' => 'active'],
                    ['slot' => 'PS2', 'type' => 'AC', 'status' => 'standby'],
                ],
                'fans' => [
                    ['id' => 'FAN1', 'speed' => 'auto'],
                    ['id' => 'FAN2', 'speed' => 'auto'],
                ],
                'dimensions' => [
                    'width' => '440mm',
                    'depth' => '420mm',
                    'height' => '44mm',
                ],
            ],
            'AN5516-04' => [
                'chassis' => [
                    'type' => 'compact',
                    'height' => '1U',
                    'slots' => 1,
                    'max_ports' => 4,
                ],
                'power_supplies' => [
                    ['slot' => 'PS1', 'type' => 'AC', 'status' => 'active'],
                ],
                'fans' => [
                    ['id' => 'FAN1', 'speed' => 'auto'],
                ],
                'dimensions' => [
                    'width' => '440mm',
                    'depth' => '300mm',
                    'height' => '44mm',
                ],
            ],
            'AN5516-06' => [
                'chassis' => [
                    'type' => 'compact',
                    'height' => '1U',
                    'slots' => 1,
                    'max_ports' => 6,
                ],
                'power_supplies' => [
                    ['slot' => 'PS1', 'type' => 'AC', 'status' => 'active'],
                ],
                'fans' => [
                    ['id' => 'FAN1', 'speed' => 'auto'],
                ],
                'dimensions' => [
                    'width' => '440mm',
                    'depth' => '350mm',
                    'height' => '44mm',
                ],
            ],
            'AN6000-17' => [
                'chassis' => [
                    'type' => 'modular',
                    'height' => '2U',
                    'slots' => 17,
                    'max_ports' => 272, // 17 slots * 16 ports
                ],
                'power_supplies' => [
                    ['slot' => 'PS1', 'type' => 'AC', 'status' => 'active'],
                    ['slot' => 'PS2', 'type' => 'AC', 'status' => 'standby'],
                ],
                'fans' => [
                    ['id' => 'FAN1', 'speed' => 'auto'],
                    ['id' => 'FAN2', 'speed' => 'auto'],
                    ['id' => 'FAN3', 'speed' => 'auto'],
                ],
                'dimensions' => [
                    'width' => '440mm',
                    'depth' => '450mm',
                    'height' => '88mm',
                ],
            ],
        ];

        return $configurations[$model] ?? $this->getDefaultConfiguration();
    }

    /**
     * Get hardware configuration via SNMP
     */
    private function getHardwareConfiguration(OLT $olt): array
    {
        try {
            $config = [
                'cards' => [],
                'ports' => [],
            ];

            // Get cards from database
            $cards = OltCard::where('olt_id', $olt->id)->get();
            foreach ($cards as $card) {
                $config['cards'][] = [
                    'slot' => $card->slot_index,
                    'type' => $card->card_type_name,
                    'status' => $card->status,
                    'ports' => $card->num_of_ports,
                    'available_ports' => $card->available_ports,
                    'cpu_util' => $card->cpu_util,
                    'mem_util' => $card->mem_util,
                ];
            }

            // Get ports from database
            $ports = OltPonPort::where('olt_id', $olt->id)->get();
            foreach ($ports as $port) {
                $config['ports'][] = [
                    'id' => $port->id,
                    'slot' => $port->slot_index,
                    'port' => $port->port_index,
                    'status' => $port->admin_status,
                    'oper_status' => $port->oper_status,
                    'onus_online' => $port->onus_online ?? 0,
                    'onus_total' => $port->onus_total ?? 0,
                    'rx_power' => $port->rx_power,
                    'tx_power' => $port->tx_power,
                ];
            }

            return $config;
        } catch (\Exception $e) {
            Log::error("Failed to get hardware config: " . $e->getMessage());
            return ['cards' => [], 'ports' => []];
        }
    }

    /**
     * Build slot structure
     */
    private function buildSlotStructure(OLT $olt, array $modelConfig, array $hardwareConfig): array
    {
        $slots = [];
        $maxSlots = $modelConfig['chassis']['slots'];

        for ($i = 0; $i < $maxSlots; $i++) {
            $slotData = [
                'index' => $i,
                'type' => 'empty',
                'status' => 'empty',
                'card' => null,
            ];

            // Check if card exists in this slot
            foreach ($hardwareConfig['cards'] as $card) {
                if ($card['slot'] == $i) {
                    $slotData['type'] = 'card';
                    $slotData['status'] = $card['status'];
                    $slotData['card'] = $card;
                    break;
                }
            }

            $slots[] = $slotData;
        }

        return $slots;
    }

    /**
     * Build port structure
     */
    private function buildPortStructure(OLT $olt, array $hardwareConfig): array
    {
        $ports = [];

        foreach ($hardwareConfig['ports'] as $port) {
            $portData = [
                'id' => $port['id'],
                'slot' => $port['slot'],
                'port' => $port['port'],
                'label' => "PON {$port['slot']}/{$port['port']}",
                'status' => $port['status'],
                'oper_status' => $port['oper_status'],
                'onus_online' => $port['onus_online'],
                'onus_total' => $port['onus_total'],
                'utilization' => $this->calculatePortUtilization($port),
                'signal_quality' => $this->calculateSignalQuality($port),
                'color' => $this->getPortColor($port),
            ];

            $ports[] = $portData;
        }

        return $ports;
    }

    /**
     * Calculate port utilization percentage
     */
    private function calculatePortUtilization(array $port): float
    {
        if ($port['onus_total'] == 0) {
            return 0;
        }
        return round(($port['onus_online'] / $port['onus_total']) * 100, 2);
    }

    /**
     * Calculate signal quality based on power levels
     */
    private function calculateSignalQuality(array $port): string
    {
        $rxPower = $port['rx_power'] ?? 0;
        
        if ($rxPower >= -20) {
            return 'excellent';
        } elseif ($rxPower >= -25) {
            return 'good';
        } elseif ($rxPower >= -27) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get port color based on status
     */
    private function getPortColor(array $port): string
    {
        if ($port['status'] !== 'up') {
            return '#6c757d'; // Gray - disabled
        }

        if ($port['oper_status'] !== 'up') {
            return '#dc3545'; // Red - down
        }

        $utilization = $this->calculatePortUtilization($port);
        
        if ($utilization >= 80) {
            return '#ffc107'; // Yellow - high utilization
        } elseif ($utilization >= 50) {
            return '#28a745'; // Green - normal
        } else {
            return '#17a2b8'; // Blue - low utilization
        }
    }

    /**
     * Get default structure for unknown models
     */
    private function getDefaultStructure(string $model): array
    {
        return [
            'model' => $model,
            'vendor' => 'Unknown',
            'chassis' => [
                'type' => 'unknown',
                'height' => '1U',
                'slots' => 1,
                'max_ports' => 16,
            ],
            'slots' => [],
            'ports' => [],
            'power_supplies' => [],
            'fans' => [],
            'dimensions' => [],
        ];
    }

    /**
     * Get default configuration
     */
    private function getDefaultConfiguration(): array
    {
        return [
            'chassis' => [
                'type' => 'compact',
                'height' => '1U',
                'slots' => 1,
                'max_ports' => 16,
            ],
            'power_supplies' => [],
            'fans' => [],
            'dimensions' => [],
        ];
    }

    /**
     * Get port layout for specific model
     */
    public function getPortLayout(string $model): array
    {
        $layouts = [
            'AN5516-01' => [
                'rows' => 2,
                'cols' => 8,
                'total' => 16,
            ],
            'AN5516-04' => [
                'rows' => 1,
                'cols' => 4,
                'total' => 4,
            ],
            'AN5516-06' => [
                'rows' => 1,
                'cols' => 6,
                'total' => 6,
            ],
            'AN6000-17' => [
                'rows' => 17,
                'cols' => 16,
                'total' => 272,
            ],
        ];

        return $layouts[$model] ?? ['rows' => 2, 'cols' => 8, 'total' => 16];
    }
}