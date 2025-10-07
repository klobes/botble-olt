<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Models\Onu;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Illuminate\Support\Facades\Log;

class OltConfigurationService
{
    protected SnmpManager $snmp;
    protected array $oids;

    public function __construct(SnmpManager $snmp)
    {
        $this->snmp = $snmp;
        $this->oids = config('plugins.fiberhome-olt.fiberhome-olt.oids');
    }

    /**
     * Add ONU to whitelist (MAC-based)
     */
    public function addOnuToWhitelist(OltDevice $olt, array $data): bool
    {
        try {
            $baseOid = $this->oids['onu_whitelist']['physical'];
            
            // Get next available auth number
            $authNo = $this->getNextAuthNumber($olt);
            
            // Set slot
            $this->snmp->set($olt, "{$baseOid}.1.{$authNo}.2", 'i', $data['slot']);
            
            // Set PON
            $this->snmp->set($olt, "{$baseOid}.1.{$authNo}.3", 'i', $data['pon']);
            
            // Set MAC address
            $this->snmp->set($olt, "{$baseOid}.1.{$authNo}.4", 's', $data['mac_address']);
            
            // Create entry (action = 4)
            $this->snmp->set($olt, "{$baseOid}.1.{$authNo}.5", 'i', 4);
            
            Log::info("ONU added to whitelist on OLT {$olt->name}", $data);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to add ONU to whitelist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove ONU from whitelist
     */
    public function removeOnuFromWhitelist(OltDevice $olt, int $authNo): bool
    {
        try {
            $baseOid = $this->oids['onu_whitelist']['physical'];
            
            // Destroy entry (action = 6)
            $this->snmp->set($olt, "{$baseOid}.1.{$authNo}.5", 'i', 6);
            
            Log::info("ONU removed from whitelist on OLT {$olt->name}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to remove ONU from whitelist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable/Disable PON port
     */
    public function setPonPortStatus(OltDevice $olt, int $ponIndex, bool $enable): bool
    {
        try {
            $oid = $this->oids['interface_enable']['olt_pon'] . ".1.{$ponIndex}.1";
            
            // 1 = enable, 0 = disable
            $value = $enable ? 1 : 0;
            
            $this->snmp->set($olt, $oid, 'i', $value);
            
            Log::info("PON port {$ponIndex} " . ($enable ? 'enabled' : 'disabled') . " on OLT {$olt->name}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to set PON port status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable/Disable ONU data port
     */
    public function setOnuPortStatus(OltDevice $olt, int $portIndex, bool $enable): bool
    {
        try {
            $oid = $this->oids['interface_enable']['data_port'] . ".1.{$portIndex}.2";
            
            $value = $enable ? 1 : 0;
            
            $this->snmp->set($olt, $oid, 'i', $value);
            
            Log::info("ONU port {$portIndex} " . ($enable ? 'enabled' : 'disabled') . " on OLT {$olt->name}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to set ONU port status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create bandwidth profile
     */
    public function createBandwidthProfile(OltDevice $olt, array $data): ?BandwidthProfile
    {
        try {
            $baseOid = $this->oids['bandwidth_profile'];
            
            // Get next profile ID
            $profileId = $this->getNextBandwidthProfileId($olt);
            
            // Set profile name
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.2", 's', $data['profile_name']);
            
            // Set upstream min rate
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.3", 'i', $data['up_min_rate']);
            
            // Set upstream max rate
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.4", 'i', $data['up_max_rate']);
            
            // Set downstream min rate
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.5", 'i', $data['down_min_rate']);
            
            // Set downstream max rate
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.6", 'i', $data['down_max_rate']);
            
            // Set fixed rate (if provided)
            if (isset($data['fixed_rate'])) {
                $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.7", 'i', $data['fixed_rate']);
            }
            
            // Create profile (action = 4)
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.12", 'i', 4);
            
            // Save to database
            $profile = BandwidthProfile::create([
                'olt_device_id' => $olt->id,
                'profile_id' => $profileId,
                'profile_name' => $data['profile_name'],
                'up_min_rate' => $data['up_min_rate'],
                'up_max_rate' => $data['up_max_rate'],
                'down_min_rate' => $data['down_min_rate'],
                'down_max_rate' => $data['down_max_rate'],
                'fixed_rate' => $data['fixed_rate'] ?? null,
            ]);
            
            Log::info("Bandwidth profile created on OLT {$olt->name}", $data);
            
            return $profile;
        } catch (\Exception $e) {
            Log::error("Failed to create bandwidth profile: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete bandwidth profile
     */
    public function deleteBandwidthProfile(OltDevice $olt, int $profileId): bool
    {
        try {
            $baseOid = $this->oids['bandwidth_profile'];
            
            // Destroy profile (action = 6)
            $this->snmp->set($olt, "{$baseOid}.1.{$profileId}.12", 'i', 6);
            
            // Delete from database
            BandwidthProfile::where('olt_device_id', $olt->id)
                ->where('profile_id', $profileId)
                ->delete();
            
            Log::info("Bandwidth profile {$profileId} deleted from OLT {$olt->name}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete bandwidth profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Configure service on ONU port
     */
    public function configureService(OltDevice $olt, int $portIndex, array $data): bool
    {
        try {
            $baseOid = $this->oids['service_config'];
            $serviceId = $data['service_id'];
            
            // Set service type (0=unicast, 1=multicast)
            $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.2", 'i', $data['service_type']);
            
            // Set CVLAN mode (1=tag, 3=transparent)
            $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.3", 'i', $data['cvlan_mode']);
            
            // Set CVLAN
            $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.4", 'i', $data['cvlan']);
            
            // Set CVLAN CoS
            $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.5", 'i', $data['cvlan_cos']);
            
            // Set SVLAN (if provided)
            if (isset($data['svlan'])) {
                $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.8", 'i', $data['svlan']);
            }
            
            // Set bandwidth
            if (isset($data['up_min_bandwidth'])) {
                $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.10", 'i', $data['up_min_bandwidth']);
            }
            
            if (isset($data['up_max_bandwidth'])) {
                $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.11", 'i', $data['up_max_bandwidth']);
            }
            
            if (isset($data['down_bandwidth'])) {
                $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.12", 'i', $data['down_bandwidth']);
            }
            
            // Create service (action = 4)
            $this->snmp->set($olt, "{$baseOid}.1.{$portIndex}.{$serviceId}.20", 'i', 4);
            
            Log::info("Service configured on OLT {$olt->name}", $data);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to configure service: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get next available auth number
     */
    protected function getNextAuthNumber(OltDevice $olt): int
    {
        // In production, implement proper logic to get next available number
        // For now, return a random number
        return rand(1000, 9999);
    }

    /**
     * Get next bandwidth profile ID
     */
    protected function getNextBandwidthProfileId(OltDevice $olt): int
    {
        $oid = $this->oids['bandwidth_profile'] . '.10.1';
        $nextId = $this->snmp->get($olt, $oid);
        
        return $this->snmp->parseValue($nextId) ?? 1;
    }

    /**
     * Reboot ONU
     */
    public function rebootOnu(Onu $onu): bool
    {
        try {
            // Implementation depends on specific OLT model
            // This is a placeholder
            Log::info("ONU {$onu->onu_name} reboot requested");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to reboot ONU: " . $e->getMessage());
            return false;
        }
    }
}