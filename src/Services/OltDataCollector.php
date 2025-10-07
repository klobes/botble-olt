<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Models\OltCard;
use Botble\FiberHomeOLTManager\Models\OltPonPort;
use Botble\FiberHomeOLTManager\Models\Onu;
use Botble\FiberHomeOLTManager\Models\OltPerformanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OltDataCollector
{
    protected SnmpManager $snmp;
    protected array $oids;

    public function __construct(SnmpManager $snmp)
    {
        $this->snmp = $snmp;
        $this->oids = config('plugins.fiberhome-olt-manager.fiberhome-olt.oids');
    }

    /**
     * Collect all data from OLT
     */
    public function collectAll(OltDevice $olt): bool
    {
        try {
            $this->collectSystemInfo($olt);
            $this->collectCards($olt);
            $this->collectPonPorts($olt);
            $this->collectOnus($olt);
            $this->collectPerformance($olt);
            
            $olt->update([
                'status' => 'online',
                'last_seen' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to collect data from OLT {$olt->name}: " . $e->getMessage());
            
            $olt->update(['status' => 'error']);
            
            return false;
        }
    }

    /**
     * Collect system information
     */
    public function collectSystemInfo(OltDevice $olt): void
    {
        $frameOid = $this->oids['system_info']['frame'];
        $frames = $this->snmp->walk($olt, $frameOid);
        
        $systemInfo = [];
        foreach ($frames as $oid => $value) {
            $systemInfo[] = $this->snmp->parseValue($value);
        }
        
        $olt->update(['system_info' => $systemInfo]);
    }

    /**
     * Collect card information
     */
    public function collectCards(OltDevice $olt): void
    {
        $cardOid = $this->oids['system_info']['card'];
        $cards = $this->snmp->walk($olt, $cardOid);
        
        $cardData = $this->parseCardData($cards);
        
        foreach ($cardData as $slotIndex => $data) {
            OltCard::updateOrCreate(
                [
                    'olt_device_id' => $olt->id,
                    'slot_index' => $slotIndex,
                ],
                $data
            );
        }
    }

    /**
     * Collect PON port information
     */
    public function collectPonPorts(OltDevice $olt): void
    {
        $ponOid = $this->oids['system_info']['olt_pon'];
        $pons = $this->snmp->walk($olt, $ponOid);
        
        $ponData = $this->parsePonData($pons);
        
        foreach ($ponData as $ponIndex => $data) {
            OltPonPort::updateOrCreate(
                [
                    'olt_device_id' => $olt->id,
                    'pon_index' => $ponIndex,
                ],
                $data
            );
        }
    }

    /**
     * Collect ONU information
     */
    public function collectOnus(OltDevice $olt): void
    {
        $onuOid = $this->oids['system_info']['onu_pon'];
        $onus = $this->snmp->walk($olt, $onuOid);
        
        $onuData = $this->parseOnuData($onus);
        
        foreach ($onuData as $onuIndex => $data) {
            $onu = Onu::updateOrCreate(
                [
                    'olt_device_id' => $olt->id,
                    'onu_index' => $onuIndex,
                ],
                $data
            );
            
            // Update online/offline timestamps
            if ($data['status'] === 'online' && $onu->wasChanged('status')) {
                $onu->update(['last_online' => now()]);
            } elseif ($data['status'] !== 'online' && $onu->wasChanged('status')) {
                $onu->update(['last_offline' => now()]);
            }
        }
    }

    /**
     * Collect performance data
     */
    public function collectPerformance(OltDevice $olt): void
    {
        $cpuOid = $this->oids['performance']['cpu'];
        $memOid = $this->oids['performance']['memory'];
        $tempOid = $this->oids['performance']['temperature'];
        
        $cpu = $this->snmp->parseValue($this->snmp->get($olt, $cpuOid));
        $memory = $this->snmp->parseValue($this->snmp->get($olt, $memOid));
        $temperature = $this->snmp->parseValue($this->snmp->get($olt, $tempOid));
        
        OltPerformanceLog::create([
            'olt_device_id' => $olt->id,
            'cpu_utilization' => $cpu,
            'memory_utilization' => $memory,
            'temperature' => $temperature,
            'recorded_at' => now(),
        ]);
        
        // Clean old logs (keep last 7 days)
        OltPerformanceLog::where('olt_device_id', $olt->id)
            ->where('recorded_at', '<', now()->subDays(7))
            ->delete();
    }

    /**
     * Parse card data from SNMP walk
     */
    protected function parseCardData(array $snmpData): array
    {
        $cards = [];
        
        foreach ($snmpData as $oid => $value) {
            // Extract slot index and field from OID
            preg_match('/\.(\d+)\.(\d+)$/', $oid, $matches);
            if (count($matches) < 3) continue;
            
            $slotIndex = (int)$matches[1];
            $field = (int)$matches[2];
            
            if (!isset($cards[$slotIndex])) {
                $cards[$slotIndex] = [];
            }
            
            $value = $this->snmp->parseValue($value);
            
            // Map field numbers to card properties
            match($field) {
                2 => $cards[$slotIndex]['card_type'] = $value,
                3 => $cards[$slotIndex]['hardware_version'] = $value,
                4 => $cards[$slotIndex]['software_version'] = $value,
                5 => $cards[$slotIndex]['status'] = $value == 1 ? 'normal' : 'offline',
                6 => $cards[$slotIndex]['num_of_ports'] = $value,
                7 => $cards[$slotIndex]['available_ports'] = $value,
                8 => $cards[$slotIndex]['cpu_util'] = $value,
                9 => $cards[$slotIndex]['mem_util'] = $value,
                default => null,
            };
        }
        
        return $cards;
    }

    /**
     * Parse PON port data from SNMP walk
     */
    protected function parsePonData(array $snmpData): array
    {
        $pons = [];
        
        foreach ($snmpData as $oid => $value) {
            preg_match('/\.(\d+)\.(\d+)$/', $oid, $matches);
            if (count($matches) < 3) continue;
            
            $ponIndex = (int)$matches[1];
            $field = (int)$matches[2];
            
            if (!isset($pons[$ponIndex])) {
                $pons[$ponIndex] = ['pon_index' => $ponIndex];
            }
            
            $value = $this->snmp->parseValue($value);
            
            match($field) {
                1 => $pons[$ponIndex]['pon_type'] = $value,
                2 => $pons[$ponIndex]['pon_name'] = $value,
                3 => $pons[$ponIndex]['description'] = $value,
                4 => $pons[$ponIndex]['is_enabled'] = $value == 1,
                5 => $pons[$ponIndex]['online_status'] = $value == 1 ? 'online' : 'offline',
                6 => $pons[$ponIndex]['speed'] = $value,
                8 => $pons[$ponIndex]['tx_optical_power'] = $value,
                9 => $pons[$ponIndex]['optical_voltage'] = $value,
                10 => $pons[$ponIndex]['optical_current'] = $value,
                11 => $pons[$ponIndex]['optical_temperature'] = $value,
                12 => $pons[$ponIndex]['auth_onu_num'] = $value,
                13 => $pons[$ponIndex]['upstream_speed'] = $value,
                default => null,
            };
        }
        
        return $pons;
    }

    /**
     * Parse ONU data from SNMP walk
     */
    protected function parseOnuData(array $snmpData): array
    {
        $onus = [];
        
        foreach ($snmpData as $oid => $value) {
            preg_match('/\.(\d+)\.(\d+)$/', $oid, $matches);
            if (count($matches) < 3) continue;
            
            $onuIndex = (int)$matches[1];
            $field = (int)$matches[2];
            
            if (!isset($onus[$onuIndex])) {
                $onus[$onuIndex] = ['onu_index' => $onuIndex];
            }
            
            $value = $this->snmp->parseValue($value);
            
            match($field) {
                1 => $onus[$onuIndex]['onu_type'] = $value,
                2 => $onus[$onuIndex]['onu_name'] = $value,
                3 => $onus[$onuIndex]['description'] = $value,
                4 => $onus[$onuIndex]['is_enabled'] = $value == 1,
                5 => $onus[$onuIndex]['speed'] = $value,
                6 => $onus[$onuIndex]['rx_optical_power'] = $value,
                7 => $onus[$onuIndex]['tx_optical_power'] = $value,
                8 => $onus[$onuIndex]['optical_voltage'] = $value,
                9 => $onus[$onuIndex]['optical_current'] = $value,
                10 => $onus[$onuIndex]['optical_temperature'] = $value,
                default => null,
            };
        }
        
        return $onus;
    }
}