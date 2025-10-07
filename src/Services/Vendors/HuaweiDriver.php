<?php

namespace Botble\FiberHomeOLTManager\Services\Vendors;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Models\Onu;

/**
 * Class HuaweiDriver
 * 
 * Driver implementation for Huawei OLT devices
 * Supports models: MA5608T, MA5680T, MA5683T, MA5800
 */
class HuaweiDriver extends AbstractVendorDriver
{
    /**
     * Initialize Huawei-specific OID mappings
     */
    protected function initializeOidMappings(): void
    {
        $this->oidMappings = [
            'system' => [
                'sysDescr' => '1.3.6.1.2.1.1.1.0',
                'sysUpTime' => '1.3.6.1.2.1.1.3.0',
                'sysName' => '1.3.6.1.2.1.1.5.0',
                'sysLocation' => '1.3.6.1.2.1.1.6.0',
                'sysContact' => '1.3.6.1.2.1.1.4.0',
            ],
            'board' => [
                'boardType' => '1.3.6.1.4.1.2011.6.3.3.2.1.1',
                'boardStatus' => '1.3.6.1.4.1.2011.6.3.3.2.1.2',
                'boardVersion' => '1.3.6.1.4.1.2011.6.3.3.2.1.3',
            ],
            'port' => [
                'gponPortStatus' => '1.3.6.1.4.1.2011.6.128.1.1.2.21.1.10',
                'gponPortOnuCount' => '1.3.6.1.4.1.2011.6.128.1.1.2.21.1.6',
            ],
            'onu' => [
                'onuStatus' => '1.3.6.1.4.1.2011.6.128.1.1.2.46.1.1',
                'onuSerialNumber' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',
                'onuRxPower' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4',
                'onuTxPower' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6',
                'onuDistance' => '1.3.6.1.4.1.2011.6.128.1.1.2.53.1.1',
                'onuType' => '1.3.6.1.4.1.2011.6.128.1.1.2.45.1.4',
            ],
            'service' => [
                'serviceProfile' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.7',
                'vlanConfig' => '1.3.6.1.4.1.2011.6.128.1.1.2.62.1.3',
            ],
            'performance' => [
                'cpuUsage' => '1.3.6.1.4.1.2011.6.3.5.1.1.2',
                'memoryUsage' => '1.3.6.1.4.1.2011.6.3.5.1.1.3',
                'temperature' => '1.3.6.1.4.1.2011.6.3.5.1.1.4',
            ],
        ];
    }

    /**
     * Get vendor name
     *
     * @return string
     */
    public function getVendorName(): string
    {
        return 'huawei';
    }

    /**
     * Get supported models
     *
     * @return array
     */
    public function getSupportedModels(): array
    {
        return [
            'MA5608T',
            'MA5680T',
            'MA5683T',
            'MA5800',
        ];
    }

    /**
     * Get all cards/slots information
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getCards(OltDevice $olt): array
    {
        try {
            $cards = [];
            $boardInfo = $this->snmpService->walk(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['board']['boardType']
            );

            foreach ($boardInfo as $oid => $value) {
                $parts = explode('.', $oid);
                $slotId = end($parts);

                $cards[$slotId] = [
                    'slot_id' => $slotId,
                    'card_type' => $value,
                    'status' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['board']['boardStatus'] . '.' . $slotId),
                    'version' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['board']['boardVersion'] . '.' . $slotId),
                ];
            }

            return $cards;
        } catch (\Exception $e) {
            $this->logError("Failed to get cards for OLT {$olt->name}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all PON ports information
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getPonPorts(OltDevice $olt): array
    {
        try {
            $ports = [];
            $portInfo = $this->snmpService->walk(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['port']['gponPortStatus']
            );

            foreach ($portInfo as $oid => $value) {
                $parts = explode('.', $oid);
                $portIndex = end($parts);

                $ports[$portIndex] = [
                    'port_index' => $portIndex,
                    'status' => $value,
                    'onu_count' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['port']['gponPortOnuCount'] . '.' . $portIndex),
                ];
            }

            return $ports;
        } catch (\Exception $e) {
            $this->logError("Failed to get PON ports for OLT {$olt->name}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all ONUs from OLT
     *
     * @param OltDevice $olt
     * @return array
     */
    public function getOnus(OltDevice $olt): array
    {
        try {
            $onus = [];
            $onuStatus = $this->snmpService->walk(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['onu']['onuStatus']
            );

            foreach ($onuStatus as $oid => $value) {
                $parts = explode('.', $oid);
                $onuIndex = end($parts);

                $onus[$onuIndex] = [
                    'onu_index' => $onuIndex,
                    'status' => $value,
                    'serial_number' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuSerialNumber'] . '.' . $onuIndex),
                    'rx_power' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuRxPower'] . '.' . $onuIndex),
                    'tx_power' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuTxPower'] . '.' . $onuIndex),
                    'distance' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuDistance'] . '.' . $onuIndex),
                    'type' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuType'] . '.' . $onuIndex),
                ];
            }

            return $onus;
        } catch (\Exception $e) {
            $this->logError("Failed to get ONUs for OLT {$olt->name}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get specific ONU information
     *
     * @param OltDevice $olt
     * @param string $onuId
     * @return array
     */
    public function getOnuInfo(OltDevice $olt, string $onuId): array
    {
        try {
            return [
                'status' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuStatus'] . '.' . $onuId),
                'serial_number' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuSerialNumber'] . '.' . $onuId),
                'rx_power' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuRxPower'] . '.' . $onuId),
                'tx_power' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuTxPower'] . '.' . $onuId),
                'distance' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuDistance'] . '.' . $onuId),
                'type' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuType'] . '.' . $onuId),
            ];
        } catch (\Exception $e) {
            $this->logError("Failed to get ONU info for {$onuId}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get ONU optical power (RX/TX)
     *
     * @param OltDevice $olt
     * @param string $onuId
     * @return array
     */
    public function getOnuOpticalPower(OltDevice $olt, string $onuId): array
    {
        try {
            return [
                'rx_power' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuRxPower'] . '.' . $onuId),
                'tx_power' => $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuTxPower'] . '.' . $onuId),
            ];
        } catch (\Exception $e) {
            $this->logError("Failed to get optical power for ONU {$onuId}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get ONU distance
     *
     * @param OltDevice $olt
     * @param string $onuId
     * @return float
     */
    public function getOnuDistance(OltDevice $olt, string $onuId): float
    {
        try {
            $distance = $this->snmpService->get($olt->ip_address, $olt->snmp_community, $this->oidMappings['onu']['onuDistance'] . '.' . $onuId);
            return (float) $distance;
        } catch (\Exception $e) {
            $this->logError("Failed to get distance for ONU {$onuId}", ['error' => $e->getMessage()]);
            return 0.0;
        }
    }

    /**
     * Enable ONU
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function enableOnu(OltDevice $olt, Onu $onu): bool
    {
        try {
            // Huawei specific enable command
            $result = $this->snmpService->set(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['onu']['onuStatus'] . '.' . $onu->onu_index,
                'i',
                1
            );

            $this->logInfo("ONU {$onu->serial_number} enabled");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to enable ONU {$onu->serial_number}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Disable ONU
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function disableOnu(OltDevice $olt, Onu $onu): bool
    {
        try {
            $result = $this->snmpService->set(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['onu']['onuStatus'] . '.' . $onu->onu_index,
                'i',
                0
            );

            $this->logInfo("ONU {$onu->serial_number} disabled");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to disable ONU {$onu->serial_number}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Reboot ONU
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function rebootOnu(OltDevice $olt, Onu $onu): bool
    {
        try {
            // Huawei ONU reboot: disable then enable
            $this->disableOnu($olt, $onu);
            sleep(2);
            $result = $this->enableOnu($olt, $onu);

            $this->logInfo("ONU {$onu->serial_number} rebooted");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to reboot ONU {$onu->serial_number}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Configure ONU bandwidth profile
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @param array $profile
     * @return bool
     */
    public function configureBandwidth(OltDevice $olt, Onu $onu, array $profile): bool
    {
        try {
            $result = $this->snmpService->set(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['service']['serviceProfile'] . '.' . $onu->onu_index,
                's',
                $profile['profile_name']
            );

            $this->logInfo("Bandwidth configured for ONU {$onu->serial_number}");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to configure bandwidth for ONU {$onu->serial_number}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Configure ONU VLAN
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @param array $vlanConfig
     * @return bool
     */
    public function configureVlan(OltDevice $olt, Onu $onu, array $vlanConfig): bool
    {
        try {
            $result = $this->snmpService->set(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['service']['vlanConfig'] . '.' . $onu->onu_index,
                'i',
                $vlanConfig['vlan_id']
            );

            $this->logInfo("VLAN configured for ONU {$onu->serial_number}");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to configure VLAN for ONU {$onu->serial_number}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Discover new ONUs
     *
     * @param OltDevice $olt
     * @return array
     */
    public function discoverOnus(OltDevice $olt): array
    {
        try {
            $allOnus = $this->getOnus($olt);
            $newOnus = [];

            foreach ($allOnus as $onu) {
                if ($onu['status'] == 'auto-find' || $onu['status'] == 'initial') {
                    $newOnus[] = $onu;
                }
            }

            $this->logInfo("Discovered {count} new ONUs", ['count' => count($newOnus)]);
            return $newOnus;
        } catch (\Exception $e) {
            $this->logError("Failed to discover ONUs for OLT {$olt->name}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Add ONU to whitelist
     *
     * @param OltDevice $olt
     * @param array $onuData
     * @return bool
     */
    public function addOnuToWhitelist(OltDevice $olt, array $onuData): bool
    {
        try {
            // Huawei uses service profile for authorization
            $result = $this->snmpService->set(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['service']['serviceProfile'] . '.' . $onuData['onu_index'],
                's',
                $onuData['service_profile']
            );

            $this->logInfo("ONU {$onuData['serial_number']} added to whitelist");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to add ONU to whitelist", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Remove ONU from whitelist
     *
     * @param OltDevice $olt
     * @param Onu $onu
     * @return bool
     */
    public function removeOnuFromWhitelist(OltDevice $olt, Onu $onu): bool
    {
        try {
            $result = $this->snmpService->set(
                $olt->ip_address,
                $olt->snmp_community,
                $this->oidMappings['service']['serviceProfile'] . '.' . $onu->onu_index,
                's',
                ''
            );

            $this->logInfo("ONU {$onu->serial_number} removed from whitelist");
            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to remove ONU from whitelist", ['error' => $e->getMessage()]);
            return false;
        }
    }
}