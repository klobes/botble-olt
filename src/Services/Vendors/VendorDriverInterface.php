<?php

namespace Botble\FiberHomeOLTManager\Services\Vendors;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Models\Onu;

/**
 * Interface VendorDriverInterface
 * 
 * Defines the contract for vendor-specific OLT drivers
 * Each vendor (Fiberhome, Huawei, ZTE) must implement this interface
 */
interface VendorDriverInterface
{
    /**
     * Get system information from OLT
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getSystemInfo(OltDevice $olt): array;

    /**
     * Get all cards/slots information
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getCards(OltDevice $olt): array;

    /**
     * Get all PON ports information
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getPonPorts(OltDevice $olt): array;

    /**
     * Get all ONUs from OLT
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getOnus(OltDevice $olt): array;

    /**
     * Get specific ONU information
     *
     * @param OltDevice $olt
     * @param string $onuId
     * @return array
     */
    public function getOnuInfo(OltDevice $olt, string $onuId): array;

    /**
     * Get ONU optical power (RX/TX)
     *
     * @param OltDevice $olt
     * @param string $onuId
     * @return array
     */
    public function getOnuOpticalPower(OltDevice $olt, string $onuId): array;

    /**
     * Get ONU distance
     *
     * @param OltDevice $olt
     * @param string $onuId
     * @return float
     */
    public function getOnuDistance(OltDevice $olt, string $onuId): float;

    /**
     * Enable ONU
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function enableOnu(OltDevice $olt, Onu $onu): bool;

    /**
     * Disable ONU
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function disableOnu(OltDevice $olt, Onu $onu): bool;

    /**
     * Reboot ONU
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function rebootOnu(OltDevice $olt, Onu $onu): bool;

    /**
     * Configure ONU bandwidth profile
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @param array $profile
     * @return bool
     */
    public function configureBandwidth(OltDevice $olt, Onu $onu, array $profile): bool;

    /**
     * Configure ONU VLAN
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @param array $vlanConfig
     * @return bool
     */
    public function configureVlan(OltDevice $olt, Onu $onu, array $vlanConfig): bool;

    /**
     * Get performance metrics (CPU, Memory, Temperature)
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getPerformanceMetrics(OltDevice $olt): array;

    /**
     * Discover new ONUs
     *
     * @param OltDevice $olt
     * @return array
     */
    public function discoverOnus(OltDevice $olt): array;

    /**
     * Add ONU to whitelist
     *
     * @param OltDevice $olt
     * @param array $onuData
     * @return bool
     */
    public function addOnuToWhitelist(OltDevice $olt, array $onuData): bool;

    /**
     * Remove ONU from whitelist
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function removeOnuFromWhitelist(OltDevice $olt, Onu $onu): bool;

    /**
     * Get vendor-specific OID mappings
     *
     * @return array
     */
    public function getOidMappings(): array;

    /**
     * Validate OLT connection
     *
     * @param OltDevice $olt
     * @return bool
     */
    public function validateConnection(OltDevice $olt): bool;

    /**
     * Get vendor name
     *
     * @return string
     */
    public function getVendorName(): string;

    /**
     * Get supported models
     *
     * @return array
     */
    public function getSupportedModels(): array;
}