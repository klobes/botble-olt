<?php

namespace Botble\FiberhomeOltManager\Services;

use Botble\FiberhomeOltManager\Models\VendorConfiguration;
use Botble\FiberhomeOltManager\Models\OnuType;
use Botble\FiberhomeOltManager\Models\OltDevice;
use Botble\FiberhomeOltManager\Services\Vendors\VendorDriverInterface;
use Botble\FiberhomeOltManager\Services\Vendors\FiberhomeDriver;
use Botble\FiberhomeOltManager\Services\Vendors\HuaweiDriver;
use Botble\FiberhomeOltManager\Services\Vendors\ZteDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VendorService
{
    protected array $drivers = [];
    protected array $vendorConfigs = [];

    public function __construct()
    {
        $this->registerDrivers();
        $this->loadVendorConfigurations();
    }

    /**
     * Register vendor drivers
     */
    protected function registerDrivers(): void
    {
        $this->drivers = [
            'fiberhome' => FiberhomeDriver::class,
            'huawei' => HuaweiDriver::class,
            'zte' => ZteDriver::class,
        ];
    }

    /**
     * Load vendor configurations
     */
    protected function loadVendorConfigurations(): void
    {
        $this->vendorConfigs = Cache::remember('vendor_configurations', 3600, function () {
            return VendorConfiguration::all()->keyBy(function ($config) {
                return $config->vendor . '_' . $config->model;
            })->toArray();
        });
    }

    /**
     * Get driver for OLT device
     */
    public function getDriver(OltDevice $olt): VendorDriverInterface
    {
        $vendor = $olt->vendor;
        
        if (!isset($this->drivers[$vendor])) {
            throw new \Exception("Driver not found for vendor: {$vendor}");
        }

        return app($this->drivers[$vendor]);
    }

    /**
     * Execute vendor-specific command
     */
    public function executeCommand(OltDevice $olt, string $method, array $params = [])
    {
        try {
            $driver = $this->getDriver($olt);
            
            if (!method_exists($driver, $method)) {
                throw new \Exception("Method {$method} not supported by {$olt->vendor} driver");
            }

            return $driver->$method($olt, ...$params);
        } catch (\Exception $e) {
            Log::error("Error executing vendor command: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get vendor configuration
     */
    public function getVendorConfig(string $vendor, ?string $model = null): ?array
    {
        $key = $vendor . '_' . ($model ?? 'default');
        
        return $this->vendorConfigs[$key] ?? null;
    }

    /**
     * Get ONU types for vendor
     */
    public function getOnuTypes(string $vendor): array
    {
        return OnuType::where('vendor', $vendor)
            ->orderBy('model')
            ->get()
            ->toArray();
    }

    /**
     * Detect vendor from OLT response
     */
    public function detectVendor(string $ipAddress, string $community = 'public'): ?string
    {
        try {
            $sysDescr = $this->getSystemDescription($ipAddress, $community);
            
            if (str_contains($sysDescr, 'Fiberhome')) {
                return 'fiberhome';
            } elseif (str_contains($sysDescr, 'Huawei')) {
                return 'huawei';
            } elseif (str_contains($sysDescr, 'ZTE')) {
                return 'zte';
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error detecting vendor: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get system description via SNMP
     */
    private function getSystemDescription(string $ipAddress, string $community): string
    {
        $sysDescr = @snmp2_get($ipAddress, $community, '1.3.6.1.2.1.1.1.0', 1000000, 2);
        
        return $sysDescr ? trim($sysDescr) : '';
    }

    /**
     * Get vendor-specific OID mappings
     */
    public function getOidMappings(string $vendor): array
    {
        $config = $this->getVendorConfig($vendor);
        
        return $config['oid_mappings'] ?? $this->getDefaultOidMappings($vendor);
    }

    /**
     * Get default OID mappings for vendor
     */
    private function getDefaultOidMappings(string $vendor): array
    {
        $mappings = [
            'fiberhome' => [
                'base' => '1.3.6.1.4.1.5875.800.3',
                'system_info' => '1.3.6.1.4.1.5875.800.3.9',
                'onu_white_list' => '1.3.6.1.4.1.5875.800.3.1',
                'interface_enable' => '1.3.6.1.4.1.5875.800.3.2',
                'onu_port_config' => '1.3.6.1.4.1.5875.800.3.3',
                'performance' => '1.3.6.1.4.1.5875.800.3.8',
            ],
            'huawei' => [
                'base' => '1.3.6.1.4.1.2011.6.128',
                'system_info' => '1.3.6.1.4.1.2011.6.128.1.1',
                'board' => '1.3.6.1.4.1.2011.6.128.1.1.2.21',
                'onu' => '1.3.6.1.4.1.2011.6.128.1.1.2.43',
                'service' => '1.3.6.1.4.1.2011.6.128.1.1.2.62',
            ],
            'zte' => [
                'base' => '1.3.6.1.4.1.3902.1012',
                'system_info' => '1.3.6.1.4.1.3902.1012.3.1',
                'card' => '1.3.6.1.4.1.3902.1012.3.1.1',
                'onu' => '1.3.6.1.4.1.3902.1012.3.28.1',
                'service' => '1.3.6.1.4.1.3902.1012.3.50',
            ],
        ];

        return $mappings[$vendor] ?? [];
    }

    /**
     * Get vendor capabilities
     */
    public function getVendorCapabilities(string $vendor): array
    {
        $config = $this->getVendorConfig($vendor);
        
        return $config['capabilities'] ?? $this->getDefaultCapabilities($vendor);
    }

    /**
     * Get default capabilities for vendor
     */
    private function getDefaultCapabilities(string $vendor): array
    {
        $capabilities = [
            'fiberhome' => [
                'max_onus' => 1024,
                'max_distance' => 20000,
                'supports_qinq' => true,
                'supports_vlan' => true,
                'supports_bandwidth_profiles' => true,
            ],
            'huawei' => [
                'max_onus' => 512,
                'max_distance' => 20000,
                'supports_qinq' => true,
                'supports_vlan' => true,
                'supports_service_profiles' => true,
                'supports_line_profiles' => true,
            ],
            'zte' => [
                'max_onus' => 1024,
                'max_distance' => 20000,
                'supports_qinq' => true,
                'supports_vlan' => true,
                'supports_service_templates' => true,
            ],
        ];

        return $capabilities[$vendor] ?? [];
    }

    /**
     * Validate vendor configuration
     */
    public function validateConfiguration(array $config): array
    {
        $errors = [];
        
        if (!isset($config['vendor'])) {
            $errors[] = 'Vendor is required';
        }
        
        if (!isset($config['oid_mappings'])) {
            $errors[] = 'OID mappings are required';
        }
        
        if (!isset($config['capabilities'])) {
            $errors[] = 'Capabilities are required';
        }
        
        return $errors;
    }

    /**
     * Get vendor statistics
     */
    public function getVendorStats(): array
    {
        return [
            'total_vendors' => count($this->drivers),
            'configured_vendors' => VendorConfiguration::count(),
            'onu_types' => OnuType::count(),
            'by_vendor' => OnuType::selectRaw('vendor, COUNT(*) as count')
                ->groupBy('vendor')
                ->pluck('count', 'vendor')
                ->toArray(),
        ];
    }

    /**
     * Clear vendor cache
     */
    public function clearCache(): void
    {
        Cache::forget('vendor_configurations');
        $this->loadVendorConfigurations();
    }
}