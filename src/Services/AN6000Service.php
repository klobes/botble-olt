<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Illuminate\Support\Facades\Log;

class AN6000Service
{
    protected $snmpService;

    public function __construct(SNMPService $snmpService)
    {
        $this->snmpService = $snmpService;
    }

    /**
     * Get AN6000 specific OIDs
     */
    public function getAN6000OIDs(): array
    {
        return [
            // System OIDs
            'system_name' => '1.3.6.1.2.1.1.5.0',
            'system_description' => '1.3.6.1.2.1.1.1.0',
            'system_uptime' => '1.3.6.1.2.1.1.3.0',
            
            // Performance OIDs
            'cpu_usage' => '1.3.6.1.4.1.34592.1.4.1.1.1.1.1',
            'memory_usage' => '1.3.6.1.4.1.34592.1.4.1.1.1.1.2',
            'temperature' => '1.3.6.1.4.1.34592.1.4.1.1.1.1.3',
            
            // Port OIDs
            'port_status' => '1.3.6.1.4.1.34592.1.4.1.2.1.1.1',
            'port_rx_power' => '1.3.6.1.4.1.34592.1.4.1.2.1.1.2',
            'port_tx_power' => '1.3.6.1.4.1.34592.1.4.1.2.1.1.3',
            
            // ONU OIDs
            'onu_list' => '1.3.6.1.4.1.34592.1.4.1.3.1.1.1',
            'onu_status' => '1.3.6.1.4.1.34592.1.4.1.3.1.1.2',
            'onu_rx_power' => '1.3.6.1.4.1.34592.1.4.1.3.1.1.3',
            'onu_tx_power' => '1.3.6.1.4.1.34592.1.4.1.3.1.1.4',
            'onu_distance' => '1.3.6.1.4.1.34592.1.4.1.3.1.1.5',
        ];
    }

    /**
     * Poll AN6000 device
     */
    public function pollAN6000(OLT $olt): array
    {
        try {
            $oids = $this->getAN6000OIDs();
            
            $systemData = $this->snmpService->getMultiple($olt->ip_address, $oids, $olt->snmp_community);
            
            return [
                'system' => [
                    'name' => $systemData[$oids['system_name']] ?? null,
                    'description' => $systemData[$oids['system_description']] ?? null,
                    'uptime' => $systemData[$oids['system_uptime']] ?? null,
                ],
                'performance' => [
                    'cpu_usage' => $this->parsePercentage($systemData[$oids['cpu_usage']] ?? 0),
                    'memory_usage' => $this->parsePercentage($systemData[$oids['memory_usage']] ?? 0),
                    'temperature' => $this->parseTemperature($systemData[$oids['temperature']] ?? 0),
                ],
                'ports' => $this->getPortData($olt),
                'onus' => $this->getONUData($olt),
            ];
        } catch (\Exception $e) {
            Log::error('AN6000 polling error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get port data for AN6000
     */
    protected function getPortData(OLT $olt): array
    {
        $oids = $this->getAN6000OIDs();
        
        $portData = [];
        
        // Get port status for all ports
        for ($slot = 1; $slot <= 20; $slot++) {
            for ($port = 1; $port <= 16; $port++) {
                $portOid = "{$oids['port_status']}.$slot.$port";
                $rxOid = "{$oids['port_rx_power']}.$slot.$port";
                $txOid = "{$oids['port_tx_power']}.$slot.$port";
                
                $data = $this->snmpService->getMultiple($olt->ip_address, [
                    'status' => $portOid,
                    'rx_power' => $rxOid,
                    'tx_power' => $txOid,
                ], $olt->snmp_community);
                
                if ($data['status']) {
                    $portData[] = [
                        'slot' => $slot,
                        'port' => $port,
                        'status' => $this->parseStatus($data['status']),
                        'rx_power' => $this->parsePower($data['rx_power']),
                        'tx_power' => $this->parsePower($data['tx_power']),
                    ];
                }
            }
        }
        
        return $portData;
    }

    /**
     * Get ONU data for AN6000
     */
    protected function getONUData(OLT $olt): array
    {
        $oids = $this->getAN6000OIDs();
        
        $onuData = [];
        
        // Get ONU list
        $onuList = $this->snmpService->walk($olt->ip_address, $oids['onu_list'], $olt->snmp_community);
        
        foreach ($onuList as $oid => $value) {
            if (preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $oid, $matches)) {
                $slot = $matches[1];
                $port = $matches[2];
                $onuId = $matches[3];
                
                $onuOid = "{$oids['onu_status']}.$slot.$port.$onuId";
                $rxOid = "{$oids['onu_rx_power']}.$slot.$port.$onuId";
                $txOid = "{$oids['onu_tx_power']}.$slot.$port.$onuId";
                $distanceOid = "{$oids['onu_distance']}.$slot.$port.$onuId";
                
                $data = $this->snmpService->getMultiple($olt->ip_address, [
                    'status' => $onuOid,
                    'rx_power' => $rxOid,
                    'tx_power' => $txOid,
                    'distance' => $distanceOid,
                ], $olt->snmp_community);
                
                $onuData[] = [
                    'slot' => $slot,
                    'port' => $port,
                    'onu_id' => $onuId,
                    'serial_number' => $this->parseSerialNumber($value),
                    'status' => $this->parseStatus($data['status']),
                    'rx_power' => $this->parsePower($data['rx_power']),
                    'tx_power' => $this->parsePower($data['tx_power']),
                    'distance' => $this->parseDistance($data['distance']),
                ];
            }
        }
        
        return $onuData;
    }

    /**
     * Parse percentage value
     */
    protected function parsePercentage($value): float
    {
        return floatval(str_replace(['%', ' '], '', $value));
    }

    /**
     * Parse temperature value
     */
    protected function parseTemperature($value): float
    {
        return floatval(str_replace(['°C', ' '], '', $value));
    }

    /**
     * Parse power value (dBm)
     */
    protected function parsePower($value): float
    {
        return floatval(str_replace(['dBm', ' '], '', $value));
    }

    /**
     * Parse distance value (meters)
     */
    protected function parseDistance($value): float
    {
        return floatval(str_replace(['m', ' '], '', $value));
    }

    /**
     * Parse status value
     */
    protected function parseStatus($value): string
    {
        $statusMap = [
            '1' => 'online',
            '2' => 'offline',
            '3' => 'dying_gasp',
            '4' => 'power_off',
        ];
        
        return $statusMap[strval($value)] ?? 'unknown';
    }

    /**
     * Parse serial number from ONU data
     */
    protected function parseSerialNumber($value): string
    {
        // Remove common prefixes and clean the serial number
        $serial = str_replace(['HWTC', 'ZTE', 'AN6000'], '', $value);
        return trim($serial);
    }

    /**
     * Get AN6000 specific configuration
     */
    public function getConfiguration(OLT $olt): array
    {
        return [
            'model' => 'AN6000',
            'type' => 'olt',
            'vendor' => 'FiberHome',
            'max_onus' => 1024,
            'max_ports' => 320,
            'supports' => [
                'gpon' => true,
                'xgpon' => true,
                'xgspn' => true,
                'epon' => true,
                '10g_epon' => true,
            ],
            'features' => [
                'vlan_stacking' => true,
                'qos' => true,
                'security' => true,
                'remote_management' => true,
            ],
        ];
    }

    /**
     * Get supported OLT models
     */
    public static function getSupportedModels(): array
    {
        return [
            'AN5516-01' => [
                'name' => 'AN5516-01',
                'type' => 'olt',
                'max_onus' => 64,
                'max_ports' => 32,
                'technology' => ['gpon', 'epon'],
            ],
            'AN5516-02' => [
                'name' => 'AN5516-02',
                'type' => 'olt',
                'max_onus' => 128,
                'max_ports' => 64,
                'technology' => ['gpon', 'epon'],
            ],
            'AN5516-04' => [
                'name' => 'AN5516-04',
                'type' => 'olt',
                'max_onus' => 256,
                'max_ports' => 128,
                'technology' => ['gpon', 'epon'],
            ],
            'AN5516-06' => [
                'name' => 'AN5516-06',
                'type' => 'olt',
                'max_onus' => 512,
                'max_ports' => 256,
                'technology' => ['gpon', 'epon', 'xgpon'],
            ],
            'AN5516-10' => [
                'name' => 'AN5516-10',
                'type' => 'olt',
                'max_onus' => 1024,
                'max_ports' => 512,
                'technology' => ['gpon', 'epon', 'xgpon', 'xgspn'],
            ],
            'AN6000-01' => [
                'name' => 'AN6000-01',
                'type' => 'olt',
                'max_onus' => 512,
                'max_ports' => 256,
                'technology' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon'],
            ],
            'AN6000-02' => [
                'name' => 'AN6000-02',
                'type' => 'olt',
                'max_onus' => 1024,
                'max_ports' => 512,
                'technology' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon'],
            ],
            'AN6000-04' => [
                'name' => 'AN6000-04',
                'type' => 'olt',
                'max_onus' => 2048,
                'max_ports' => 1024,
                'technology' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon'],
            ],
        ];
    }
}