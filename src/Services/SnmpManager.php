<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SnmpManager
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('plugins.fiberhome-olt-manager.fiberhome-olt');
    }

    /**
     * Get SNMP value from OLT
     */
    public function get(OltDevice $olt, string $oid): mixed
    {
        try {
            $cacheKey = $this->getCacheKey($olt, $oid);
            
            if ($this->config['cache']['enabled']) {
                return Cache::remember($cacheKey, $this->config['cache']['ttl'], function () use ($olt, $oid) {
                    return $this->performSnmpGet($olt, $oid);
                });
            }

            return $this->performSnmpGet($olt, $oid);
        } catch (Exception $e) {
            Log::error("SNMP GET failed for OLT {$olt->name}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Walk SNMP tree from OLT
     */
    public function walk(OltDevice $olt, string $oid): array
    {
        try {
            $cacheKey = $this->getCacheKey($olt, $oid . '_walk');
            
            if ($this->config['cache']['enabled']) {
                return Cache::remember($cacheKey, $this->config['cache']['ttl'], function () use ($olt, $oid) {
                    return $this->performSnmpWalk($olt, $oid);
                });
            }

            return $this->performSnmpWalk($olt, $oid);
        } catch (Exception $e) {
            Log::error("SNMP WALK failed for OLT {$olt->name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Set SNMP value on OLT
     */
    public function set(OltDevice $olt, string $oid, string $type, mixed $value): bool
    {
        try {
            $result = $this->performSnmpSet($olt, $oid, $type, $value);
            
            // Clear cache for this OID
            if ($this->config['cache']['enabled']) {
                Cache::forget($this->getCacheKey($olt, $oid));
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error("SNMP SET failed for OLT {$olt->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform actual SNMP GET operation
     */
    protected function performSnmpGet(OltDevice $olt, string $oid): mixed
    {
        $session = $this->createSnmpSession($olt);
        
        if ($olt->snmp_version === '2c') {
            return @snmp2_get(
                $olt->ip_address,
                $olt->snmp_community,
                $oid,
                $this->config['snmp']['timeout'],
                $this->config['snmp']['retries']
            );
        }
        
        return @snmpget(
            $olt->ip_address,
            $olt->snmp_community,
            $oid,
            $this->config['snmp']['timeout'],
            $this->config['snmp']['retries']
        );
    }

    /**
     * Perform actual SNMP WALK operation
     */
    protected function performSnmpWalk(OltDevice $olt, string $oid): array
    {
        if ($olt->snmp_version === '2c') {
            $result = @snmp2_real_walk(
                $olt->ip_address,
                $olt->snmp_community,
                $oid,
                $this->config['snmp']['timeout'],
                $this->config['snmp']['retries']
            );
        } else {
            $result = @snmprealwalk(
                $olt->ip_address,
                $olt->snmp_community,
                $oid,
                $this->config['snmp']['timeout'],
                $this->config['snmp']['retries']
            );
        }
        
        return is_array($result) ? $result : [];
    }

    /**
     * Perform actual SNMP SET operation
     */
    protected function performSnmpSet(OltDevice $olt, string $oid, string $type, mixed $value): bool
    {
        if ($olt->snmp_version === '2c') {
            return @snmp2_set(
                $olt->ip_address,
                $olt->snmp_community,
                $oid,
                $type,
                $value,
                $this->config['snmp']['timeout'],
                $this->config['snmp']['retries']
            );
        }
        
        return @snmpset(
            $olt->ip_address,
            $olt->snmp_community,
            $oid,
            $type,
            $value,
            $this->config['snmp']['timeout'],
            $this->config['snmp']['retries']
        );
    }

    /**
     * Create SNMP session
     */
    protected function createSnmpSession(OltDevice $olt): void
    {
        snmp_set_quick_print(true);
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    }

    /**
     * Generate cache key
     */
    protected function getCacheKey(OltDevice $olt, string $oid): string
    {
        return $this->config['cache']['prefix'] . $olt->id . '_' . md5($oid);
    }

    /**
     * Clear all cache for an OLT
     */
    public function clearCache(OltDevice $olt): void
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }

        $prefix = $this->config['cache']['prefix'] . $olt->id . '_';
        Cache::flush(); // In production, use more specific cache clearing
    }

    /**
     * Test SNMP connection to OLT
     */
    public function testConnection(OltDevice $olt): bool
    {
        try {
            $result = $this->get($olt, '1.3.6.1.2.1.1.1.0'); // sysDescr
            return $result !== null && $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Parse SNMP value
     */
    public function parseValue(mixed $value): mixed
    {
        if (is_string($value)) {
            // Remove SNMP type prefix (e.g., "STRING: ", "INTEGER: ")
            $value = preg_replace('/^[A-Z]+:\s*/', '', $value);
            
            // Remove quotes
            $value = trim($value, '"');
            
            // Convert to appropriate type
            if (is_numeric($value)) {
                return strpos($value, '.') !== false ? (float)$value : (int)$value;
            }
        }
        
        return $value;
    }
}