<?php

namespace Botble\FiberHomeOLTManager\Services\Vendors;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Services\SNMPService;
use Illuminate\Support\Facades\Log;

/**
 * Abstract class AbstractVendorDriver
 * 
 * Base implementation for vendor drivers
 * Provides common functionality for all vendors
 */
abstract class AbstractVendorDriver implements VendorDriverInterface
{
    protected SNMPService $snmpService;
    protected array $oidMappings = [];

    public function __construct(SNMPService $snmpService)
    {
        $this->snmpService = $snmpService;
        $this->initializeOidMappings();
    }

    /**
     * Initialize vendor-specific OID mappings
     * Must be implemented by each vendor
     */
    abstract protected function initializeOidMappings(): void;

    /**
     * Get OID mappings
     *
     * @return array
     */
    public function getOidMappings(): array
    {
        return $this->oidMappings;
    }

    /**
     * Validate OLT connection
     *
     * @param OltDevice $olt
     * @return bool
     */
    public function validateConnection(OltDevice $olt): bool
    {
        try {
            $result = $this->snmpService->get(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['system']['sysDescr'] ?? '1.3.6.1.2.1.1.1.0'
            );

            return !empty($result);
        } catch (\Exception $e) {
            Log::error("Connection validation failed for OLT {$olt->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get system information from OLT
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getSystemInfo(OltDevice $olt): array
    {
        try {
            $systemOids = $this->oidMappings['system'] ?? [];
            
            return [
                'description' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $systemOids['sysDescr'] ?? '1.3.6.1.2.1.1.1.0'),
                'uptime' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $systemOids['sysUpTime'] ?? '1.3.6.1.2.1.1.3.0'),
                'name' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $systemOids['sysName'] ?? '1.3.6.1.2.1.1.5.0'),
                'location' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $systemOids['sysLocation'] ?? '1.3.6.1.2.1.1.6.0'),
                'contact' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $systemOids['sysContact'] ?? '1.3.6.1.2.1.1.4.0'),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get system info for OLT {$olt->name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get performance metrics
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getPerformanceMetrics(OltDevice $olt): array
    {
        try {
            $performanceOids = $this->oidMappings['performance'] ?? [];
            
            return [
                'cpu_usage' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $performanceOids['cpuUsage'] ?? ''),
                'memory_usage' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $performanceOids['memoryUsage'] ?? ''),
                'temperature' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $performanceOids['temperature'] ?? ''),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get performance metrics for OLT {$olt->name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse SNMP walk result
     *
     * @param array $walkResult
     * @return array
     */
    protected function parseSnmpWalk(array $walkResult): array
    {
        $parsed = [];
        
        foreach ($walkResult as $oid => $value) {
            // Extract index from OID
            $parts = explode('.', $oid);
            $index = end($parts);
            
            $parsed[$index] = $value;
        }
        
        return $parsed;
    }

    /**
     * Convert hex string to MAC address
     *
     * @param string $hex
     * @return string
     */
    protected function hexToMac(string $hex): string
    {
        $hex = str_replace(' ', '', $hex);
        $hex = str_replace(':', '', $hex);
        
        return implode(':', str_split($hex, 2));
    }

    /**
     * Convert MAC address to hex string
     *
     * @param string $mac
     * @return string
     */
    protected function macToHex(string $mac): string
    {
        return str_replace(':', '', $mac);
    }

    /**
     * Parse ONU ID from string
     *
     * @param string $onuId
     * @return array [slot, port, onu]
     */
    protected function parseOnuId(string $onuId): array
    {
        // Format: slot/port:onu or slot/port/onu
        $parts = preg_split('/[\/:]/', $onuId);
        
        return [
            'slot' => $parts[0] ?? 0,
            'port' => $parts[1] ?? 0,
            'onu' => $parts[2] ?? 0,
        ];
    }

    /**
     * Format ONU ID
     *
     * @param int $slot
     * @param int $port
     * @param int $onu
     * @return string
     */
    protected function formatOnuId(int $slot, int $port, int $onu): string
    {
        return "{$slot}/{$port}:{$onu}";
    }

    /**
     * Log error
     *
     * @param string $message
     * @param array $context
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getVendorName()}] {$message}", $context);
    }

    /**
     * Log info
     *
     * @param string $message
     * @param array $context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info("[{$this->getVendorName()}] {$message}", $context);
    }
}