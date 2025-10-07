<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Services\SnmpManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OLTService
{
    protected $snmpManager;

    public function __construct(SnmpManager $snmpManager)
    {
        $this->snmpManager = $snmpManager;
    }

    /**
     * Get all OLTs with performance data
     */
    public function getAllOLTs(): array
    {
        return OLT::with(['onus', 'ports'])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get OLT by ID with full details
     */
    public function getOLTById(int $id): ?OLT
    {
        return OLT::with(['onus', 'ports', 'performanceLogs'])
            ->find($id);
    }

    /**
     * Create new OLT
     */
    public function createOLT(array $data): OLT
    {
        $olt = OLT::create($data);
        
        // Auto-discover capabilities based on model
        $this->discoverCapabilities($olt);
        
        return $olt;
    }

    /**
     * Update OLT
     */
    public function updateOLT(int $id, array $data): ?OLT
    {
        $olt = OLT::find($id);
        
        if ($olt) {
            $olt->update($data);
            $this->clearCache($olt->id);
        }
        
        return $olt;
    }

    /**
     * Delete OLT
     */
    public function deleteOLT(int $id): bool
    {
        $olt = OLT::find($id);
        
        if ($olt) {
            $this->clearCache($id);
            return $olt->delete();
        }
        
        return false;
    }

    /**
     * Poll OLT for real-time data
     */
    public function pollOLT(OLT $olt): array
    {
        try {
            $device = OltDevice::where('ip_address', $olt->ip_address)->first();
            
            if (!$device) {
                throw new \Exception("Device not found for IP: {$olt->ip_address}");
            }

            $data = [
                'cpu_usage' => $this->getCpuUsage($device),
                'memory_usage' => $this->getMemoryUsage($device),
                'temperature' => $this->getTemperature($device),
                'uptime' => $this->getUptime($device),
                'status' => $this->getStatus($device),
                'last_polled' => now(),
            ];

            $olt->update($data);
            
            // Cache the data
            $this->cacheData($olt->id, $data);
            
            return $data;
            
        } catch (\Exception $e) {
            Log::error("Failed to poll OLT {$olt->name}: " . $e->getMessage());
            
            $olt->update([
                'status' => 'offline',
                'last_polled' => now(),
            ]);
            
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }
    }

    /**
     * Discover ONUs connected to OLT
     */
    public function discoverONUs(OLT $olt): array
    {
        try {
            $device = OltDevice::where('ip_address', $olt->ip_address)->first();
            
            if (!$device) {
                throw new \Exception("Device not found");
            }

            $onus = [];
            
            // Get ONU list based on OLT model
            if (str_contains($olt->model, 'AN6000')) {
                $onus = $this->discoverAN6000ONUs($device);
            } else {
                $onus = $this->discoverAN5516ONUs($device);
            }
            
            return $onus;
            
        } catch (\Exception $e) {
            Log::error("Failed to discover ONUs for OLT {$olt->name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get OLT statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => OLT::count(),
            'online' => OLT::where('status', 'online')->count(),
            'offline' => OLT::where('status', 'offline')->count(),
            'maintenance' => OLT::where('status', 'maintenance')->count(),
            'by_model' => OLT::select('model', \DB::raw('count(*) as count'))
                ->groupBy('model')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get OLT health status
     */
    public function getHealthStatus(OLT $olt): array
    {
        return [
            'cpu' => [
                'value' => $olt->cpu_usage,
                'status' => $this->getCpuStatus($olt->cpu_usage),
            ],
            'memory' => [
                'value' => $olt->memory_usage,
                'status' => $this->getMemoryStatus($olt->memory_usage),
            ],
            'temperature' => [
                'value' => $olt->temperature,
                'status' => $this->getTemperatureStatus($olt->temperature),
            ],
            'uptime' => [
                'value' => $olt->uptime,
                'formatted' => $this->formatUptime($olt->uptime),
            ],
        ];
    }

    /**
     * Private methods
     */
    private function discoverCapabilities(OLT $olt): void
    {
        $capabilities = [
            'AN5516-01' => ['gpon', 'epon', 'max_onus' => 64, 'max_ports' => 32],
            'AN5516-02' => ['gpon', 'epon', 'max_onus' => 128, 'max_ports' => 64],
            'AN5516-04' => ['gpon', 'epon', 'max_onus' => 256, 'max_ports' => 128],
            'AN5516-06' => ['gpon', 'epon', 'xgpon', 'max_onus' => 512, 'max_ports' => 256],
            'AN5516-10' => ['gpon', 'epon', 'xgpon', 'xgspn', 'max_onus' => 1024, 'max_ports' => 512],
            'AN6000-01' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon', 'max_onus' => 512, 'max_ports' => 256],
            'AN6000-02' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon', 'max_onus' => 1024, 'max_ports' => 512],
            'AN6000-04' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon', 'max_onus' => 2048, 'max_ports' => 1024],
            'AN6000-06' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon', 'max_onus' => 4096, 'max_ports' => 2048],
            'AN6000-10' => ['gpon', 'xgpon', 'xgspn', 'epon', '10g_epon', 'max_onus' => 8192, 'max_ports' => 4096],
        ];

        if (isset($capabilities[$olt->model])) {
            $cap = $capabilities[$olt->model];
            $olt->update([
                'technology' => $cap,
                'max_onus' => $cap['max_onus'],
                'max_ports' => $cap['max_ports'],
                'vendor' => str_contains($olt->model, 'AN6000') ? 'FiberHome AN6000' : 'FiberHome AN5516',
            ]);
        }
    }

    private function getCpuUsage(OltDevice $device): float
    {
        // SNMP OID for CPU usage (example)
        $cpu = $this->snmpManager->get($device, '1.3.6.1.4.1.5875.800.3.9.1.1.1.0');
        return is_numeric($cpu) ? (float) $cpu : 0;
    }

    private function getMemoryUsage(OltDevice $device): float
    {
        // SNMP OID for memory usage (example)
        $memory = $this->snmpManager->get($device, '1.3.6.1.4.1.5875.800.3.9.1.2.1.0');
        return is_numeric($memory) ? (float) $memory : 0;
    }

    private function getTemperature(OltDevice $device): ?float
    {
        // SNMP OID for temperature (example)
        $temp = $this->snmpManager->get($device, '1.3.6.1.4.1.5875.800.3.9.1.3.1.0');
        return is_numeric($temp) ? (float) $temp : null;
    }

    private function getUptime(OltDevice $device): int
    {
        // SNMP OID for uptime (example)
        $uptime = $this->snmpManager->get($device, '1.3.6.1.2.1.1.3.0');
        return is_numeric($uptime) ? (int) $uptime : 0;
    }

    private function getStatus(OltDevice $device): string
    {
        try {
            $response = $this->snmpManager->get($device, '1.3.6.1.2.1.1.1.0');
            return $response !== null ? 'online' : 'offline';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function discoverAN5516ONUs(OltDevice $device): array
    {
        // Implementation for AN5516 ONU discovery
        return [];
    }

    private function discoverAN6000ONUs(OltDevice $device): array
    {
        // Implementation for AN6000 ONU discovery
        return [];
    }

    private function getCpuStatus(float $cpu): string
    {
        if ($cpu < 70) return 'good';
        if ($cpu < 85) return 'warning';
        return 'critical';
    }

    private function getMemoryStatus(float $memory): string
    {
        if ($memory < 75) return 'good';
        if ($memory < 90) return 'warning';
        return 'critical';
    }

    private function getTemperatureStatus(?float $temp): string
    {
        if ($temp === null) return 'unknown';
        if ($temp < 60) return 'good';
        if ($temp < 75) return 'warning';
        return 'critical';
    }

    private function formatUptime(int $uptime): string
    {
        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);
        
        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }

    private function cacheData(int $oltId, array $data): void
    {
        $cacheKey = "olt_data_{$oltId}";
        Cache::put($cacheKey, $data, now()->addMinutes(5));
    }

    private function clearCache(int $oltId): void
    {
        Cache::forget("olt_data_{$oltId}");
    }
}
