
<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\Onu as ONU;
use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Services\SnmpManager;
use Illuminate\Support\Facades\Log;

class DiscoveryService
{
    protected $snmpManager;

    public function __construct(SnmpManager $snmpManager)
    {
        $this->snmpManager = $snmpManager;
    }

    /**
     * Discover all ONUs on an OLT
     */
    public function discoverONUs(OLT $olt): array
    {
        try {
            $device = OltDevice::where('ip_address', $olt->ip_address)->first();
            
            if (!$device) {
                throw new \Exception("Device not found for OLT: {$olt->name}");
            }

            // Determine discovery method based on OLT model
            if (str_contains($olt->model, 'AN6000')) {
                return $this->discoverAN6000ONUs($device, $olt);
            } else {
                return $this->discoverAN5516ONUs($device, $olt);
            }

        } catch (\Exception $e) {
            Log::error("ONU discovery failed for OLT {$olt->name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Discover ONUs on AN5516 OLT
     */
    protected function discoverAN5516ONUs(OltDevice $device, OLT $olt): array
    {
        $discoveredONUs = [];

        // OID for ONU discovery on AN5516
        $baseOid = '1.3.6.1.4.1.5875.800.3.9.1.1';

        try {
            // Walk through all slots and ports
            for ($slot = 1; $slot <= 16; $slot++) {
                for ($port = 1; $port <= 16; $port++) {
                    $onuOid = "{$baseOid}.{$slot}.{$port}";
                    $onuData = $this->snmpManager->walk($device, $onuOid);

                    foreach ($onuData as $oid => $value) {
                        // Parse ONU information
                        $onuInfo = $this->parseONUData($value, $slot, $port);
                        
                        if ($onuInfo) {
                            $discoveredONUs[] = $onuInfo;
                            
                            // Create or update ONU in database
                            $this->createOrUpdateONU($olt, $onuInfo);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("AN5516 ONU discovery error: " . $e->getMessage());
        }

        return $discoveredONUs;
    }

    /**
     * Discover ONUs on AN6000 OLT
     */
    protected function discoverAN6000ONUs(OltDevice $device, OLT $olt): array
    {
        $discoveredONUs = [];

        // OID for ONU discovery on AN6000
        $baseOid = '1.3.6.1.4.1.5875.801.3.9.1.1';

        try {
            // Walk through all slots and ports
            for ($slot = 1; $slot <= 20; $slot++) {
                for ($port = 1; $port <= 16; $port++) {
                    $onuOid = "{$baseOid}.{$slot}.{$port}";
                    $onuData = $this->snmpManager->walk($device, $onuOid);

                    foreach ($onuData as $oid => $value) {
                        // Parse ONU information
                        $onuInfo = $this->parseONUData($value, $slot, $port);
                        
                        if ($onuInfo) {
                            $discoveredONUs[] = $onuInfo;
                            
                            // Create or update ONU in database
                            $this->createOrUpdateONU($olt, $onuInfo);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("AN6000 ONU discovery error: " . $e->getMessage());
        }

        return $discoveredONUs;
    }

    /**
     * Parse ONU data from SNMP response
     */
    protected function parseONUData($value, int $slot, int $port): ?array
    {
        // Parse SNMP value to extract ONU information
        // This is a simplified example - actual implementation depends on SNMP MIB
        
        if (empty($value)) {
            return null;
        }

        return [
            'slot' => $slot,
            'port' => $port,
            'serial_number' => $this->extractSerialNumber($value),
            'status' => $this->extractStatus($value),
            'distance' => $this->extractDistance($value),
            'rx_power' => $this->extractRxPower($value),
            'tx_power' => $this->extractTxPower($value),
        ];
    }

    /**
     * Create or update ONU in database
     */
    protected function createOrUpdateONU(OLT $olt, array $onuInfo): void
    {
        try {
            ONU::updateOrCreate(
                [
                    'olt_id' => $olt->id,
                    'serial_number' => $onuInfo['serial_number'],
                ],
                [
                    'slot' => $onuInfo['slot'],
                    'port' => $onuInfo['port'],
                    'status' => $onuInfo['status'],
                    'distance' => $onuInfo['distance'] ?? null,
                    'rx_power' => $onuInfo['rx_power'] ?? null,
                    'tx_power' => $onuInfo['tx_power'] ?? null,
                    'last_seen' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to create/update ONU: " . $e->getMessage());
        }
    }

    /**
     * Auto-discover new OLTs on network
     */
    public function discoverOLTs(string $networkRange): array
    {
        $discoveredOLTs = [];

        // Parse network range (e.g., "192.168.1.0/24")
        $ips = $this->parseNetworkRange($networkRange);

        foreach ($ips as $ip) {
            try {
                // Try to connect via SNMP
                $device = new OltDevice([
                    'ip_address' => $ip,
                    'snmp_community' => setting('fiberhome_default_snmp_community', 'public'),
                    'snmp_version' => setting('fiberhome_default_snmp_version', '2c'),
                ]);

                // Test connection
                if ($this->snmpManager->testConnection($device)) {
                    // Get system information
                    $sysDescr = $this->snmpManager->get($device, '1.3.6.1.2.1.1.1.0');
                    
                    if ($sysDescr && str_contains($sysDescr, 'FiberHome')) {
                        $discoveredOLTs[] = [
                            'ip_address' => $ip,
                            'system_description' => $sysDescr,
                            'model' => $this->extractModel($sysDescr),
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Skip this IP
                continue;
            }
        }

        return $discoveredOLTs;
    }

    /**
     * Helper methods
     */
    protected function extractSerialNumber($value): string
    {
        // Extract serial number from SNMP value
        return (string) $value;
    }

    protected function extractStatus($value): string
    {
        // Extract status from SNMP value
        return 'online';
    }

    protected function extractDistance($value): ?float
    {
        // Extract distance from SNMP value
        return null;
    }

    protected function extractRxPower($value): ?float
    {
        // Extract RX power from SNMP value
        return null;
    }

    protected function extractTxPower($value): ?float
    {
        // Extract TX power from SNMP value
        return null;
    }

    protected function extractModel($sysDescr): string
    {
        // Extract model from system description
        if (str_contains($sysDescr, 'AN6000')) {
            return 'AN6000-01';
        } elseif (str_contains($sysDescr, 'AN5516')) {
            return 'AN5516-01';
        }
        return 'Unknown';
    }

    protected function parseNetworkRange(string $range): array
    {
        // Parse CIDR notation and return array of IPs
        // Simplified implementation
        return [];
    }
}
