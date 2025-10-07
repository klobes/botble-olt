<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\Onu;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Services\SnmpManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OnuService
{
    protected $snmpManager;

    public function __construct(SnmpManager $snmpManager)
    {
        $this->snmpManager = $snmpManager;
    }

    /**
     * Get all Onus with OLT relationship
     */
    public function getAllOnus(): array
    {
        return Onu::with(['olt', 'bandwidthProfile'])
            ->orderBy('serial_number')
            ->get()
            ->toArray();
    }

    /**
     * Get Onu by ID with full details
     */
    public function getOnuById(int $id): ?Onu
    {
        return Onu::with(['olt', 'bandwidthProfile', 'ports'])
            ->find($id);
    }

    /**
     * Create new Onu
     */
    public function createOnu(array $data): Onu
    {
        $Onu = Onu::create($data);
        
        // Auto-discover Onu configuration
        $this->discoverConfiguration($Onu);
        
        return $Onu;
    }

    /**
     * Update Onu
     */
    public function updateOnu(int $id, array $data): ?Onu
    {
        $Onu = Onu::find($id);
        
        if ($Onu) {
            $Onu->update($data);
            $this->clearCache($Onu->id);
        }
        
        return $Onu;
    }

    /**
     * Delete Onu
     */
    public function deleteOnu(int $id): bool
    {
        $Onu = Onu::find($id);
        
        if ($Onu) {
            $this->clearCache($id);
            return $Onu->delete();
        }
        
        return false;
    }

    /**
     * Configure Onu settings
     */
    public function configureOnu(Onu $Onu, array $configuration): bool
    {
        try {
            // Implementation based on Onu model and OLT type
            $olt = $Onu->olt;
            
            if (!$olt) {
                throw new \Exception("OLT not found for this Onu");
            }

            // Apply configuration based on OLT model
            if (str_contains($olt->model, 'AN6000')) {
                return $this->configureAN6000Onu($Onu, $configuration);
            } else {
                return $this->configureAN5516Onu($Onu, $configuration);
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to configure Onu {$Onu->serial_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reboot Onu
     */
    public function rebootOnu(Onu $Onu): bool
    {
        try {
            $olt = $Onu->olt;
            
            if (!$olt) {
                throw new \Exception("OLT not found");
            }

            // Implementation based on OLT model
            if (str_contains($olt->model, 'AN6000')) {
                return $this->rebootAN6000Onu($Onu);
            } else {
                return $this->rebootAN5516Onu($Onu);
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to reboot Onu {$Onu->serial_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Onu performance data
     */
    public function getPerformanceData(Onu $Onu): array
    {
        try {
            $olt = $Onu->olt;
            
            if (!$olt) {
                throw new \Exception("OLT not found");
            }

            $device = OltDevice::where('ip_address', $olt->ip_address)->first();
            
            if (!$device) {
                throw new \Exception("Device not found");
            }

            // Get performance metrics based on Onu slot/port
            return [
                'rx_power' => $this->getRxPower($device, $Onu),
                'tx_power' => $this->getTxPower($device, $Onu),
                'temperature' => $this->getTemperature($device, $Onu),
                'voltage' => $this->getVoltage($device, $Onu),
                'distance' => $this->getDistance($device, $Onu),
                'status' => $this->getStatus($device, $Onu),
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get performance data for Onu {$Onu->serial_number}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Onu statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => Onu::count(),
            'online' => Onu::where('status', 'online')->count(),
            'offline' => Onu::where('status', 'offline')->count(),
            'by_olt' => Onu::select('olt_id', DB::raw('count(*) as count'))
                ->groupBy('olt_id')
                ->with('olt:id,name')
                ->get()
                ->toArray(),
            'with_bandwidth' => Onu::whereNotNull('bandwidth_profile_id')->count(),
            'without_bandwidth' => Onu::whereNull('bandwidth_profile_id')->count(),
        ];
    }

    /**
     * Get available Onus for OLT
     */
    public function getAvailableOnus(int $oltId): array
    {
        return Onu::where('olt_device_id', $oltId)//olt_id
            ->where('status', '!=', 'assigned')
            ->get()
            ->toArray();
    }

    /**
     * Assign bandwidth profile to Onu
     */
    public function assignBandwidthProfile(Onu $Onu, int $profileId): bool
    {
        try {
            $Onu->bandwidth_profile_id = $profileId;
            return $Onu->save();
        } catch (\Exception $e) {
            Log::error("Failed to assign bandwidth profile to Onu {$Onu->serial_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Onu configuration
     */
    public function getConfiguration(Onu $Onu): array
    {
        return [
            'basic' => [
                'serial_number' => $Onu->serial_number,
                'customer_name' => $Onu->customer_name,
                'installation_address' => $Onu->installation_address,
            ],
            'network' => [
                'vlan' => $Onu->vlan_id ?? null,
                'ip_address' => $Onu->ip_address ?? null,
                'subnet_mask' => $Onu->subnet_mask ?? null,
                'gateway' => $Onu->gateway ?? null,
            ],
            'bandwidth' => [
                'profile_id' => $Onu->bandwidth_profile_id,
                'download_speed' => $Onu->bandwidthProfile?->download_speed ?? 0,
                'upload_speed' => $Onu->bandwidthProfile?->upload_speed ?? 0,
            ],
        ];
    }

    /**
     * Private methods
     */
    private function discoverConfiguration(Onu $Onu): void
    {
        // Auto-discover Onu capabilities based on serial number
        $capabilities = $this->getOnuCapabilities($Onu->serial_number);
        
        if ($capabilities) {
            $Onu->update([
                'max_ports' => $capabilities['max_ports'],
                'technology' => $capabilities['technology'],
                'description' => $capabilities['description'],
            ]);
        }
    }

    private function configureAN5516Onu(Onu $Onu, array $configuration): bool
    {
        // Implementation for AN5516 Onu configuration
        return true;
    }

    private function configureAN6000Onu(Onu $Onu, array $configuration): bool
    {
        // Implementation for AN6000 Onu configuration
        return true;
    }

    private function rebootAN5516Onu(Onu $Onu): bool
    {
        // Implementation for AN5516 Onu reboot
        return true;
    }

    private function rebootAN6000Onu(Onu $Onu): bool
    {
        // Implementation for AN6000 Onu reboot
        return true;
    }

    private function getRxPower(OltDevice $device, Onu $Onu): ?float
    {
        // SNMP OID for RX power based on slot/port/Onu_id
        $oid = "1.3.6.1.4.1.5875.800.3.9.1.5.1.{$Onu->slot}.{$Onu->port}.{$Onu->Onu_id}";
        $power = $this->snmpManager->get($device, $oid);
        return is_numeric($power) ? (float) $power : null;
    }

    private function getTxPower(OltDevice $device, Onu $Onu): ?float
    {
        // SNMP OID for TX power based on slot/port/Onu_id
        $oid = "1.3.6.1.4.1.5875.800.3.9.1.5.2.{$Onu->slot}.{$Onu->port}.{$Onu->Onu_id}";
        $power = $this->snmpManager->get($device, $oid);
        return is_numeric($power) ? (float) $power : null;
    }

    private function getTemperature(OltDevice $device, Onu $Onu): ?float
    {
        // SNMP OID for temperature based on slot/port/Onu_id
        $oid = "1.3.6.1.4.1.5875.800.3.9.1.5.3.{$Onu->slot}.{$Onu->port}.{$Onu->Onu_id}";
        $temp = $this->snmpManager->get($device, $oid);
        return is_numeric($temp) ? (float) $temp : null;
    }

    private function getVoltage(OltDevice $device, Onu $Onu): ?float
    {
        // SNMP OID for voltage based on slot/port/Onu_id
        $oid = "1.3.6.1.4.1.5875.800.3.9.1.5.4.{$Onu->slot}.{$Onu->port}.{$Onu->Onu_id}";
        $voltage = $this->snmpManager->get($device, $oid);
        return is_numeric($voltage) ? (float) $voltage : null;
    }

    private function getDistance(OltDevice $device, Onu $Onu): ?float
    {
        // SNMP OID for distance based on slot/port/Onu_id
        $oid = "1.3.6.1.4.1.5875.800.3.9.1.5.5.{$Onu->slot}.{$Onu->port}.{$Onu->Onu_id}";
        $distance = $this->snmpManager->get($device, $oid);
        return is_numeric($distance) ? (float) $distance : null;
    }

    private function getStatus(OltDevice $device, Onu $Onu): string
    {
        // SNMP OID for Onu status based on slot/port/Onu_id
        $oid = "1.3.6.1.4.1.5875.800.3.9.1.5.6.{$Onu->slot}.{$Onu->port}.{$Onu->Onu_id}";
        $status = $this->snmpManager->get($device, $oid);
        
        if ($status === null) return 'offline';
        
        return $status == 1 ? 'online' : 'offline';
    }

    private function getOnuCapabilities(string $serialNumber): ?array
    {
        // Determine Onu capabilities based on serial number prefix
        $prefixes = [
            'AN5506' => ['max_ports' => 4, 'technology' => ['gpon', 'epon'], 'description' => 'AN5506 Series Onu'],
            'AN5506-04' => ['max_ports' => 4, 'technology' => ['gpon'], 'description' => 'AN5506-04 GPON Onu'],
            'AN5506-02' => ['max_ports' => 2, 'technology' => ['gpon'], 'description' => 'AN5506-02 GPON Onu'],
            'AN5506-01' => ['max_ports' => 1, 'technology' => ['gpon'], 'description' => 'AN5506-01 GPON Onu'],
        ];

        foreach ($prefixes as $prefix => $capabilities) {
            if (str_starts_with($serialNumber, $prefix)) {
                return $capabilities;
            }
        }

        return null;
    }

    private function clearCache(int $OnuId): void
    {
        Cache::forget("Onu_data_{$OnuId}");
    }
}
