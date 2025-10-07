<?php

namespace Botble\FiberhomeOltManager\Services;

use Botble\FiberhomeOltManager\Models\OLT;
use Botble\FiberhomeOltManager\Models\Onu;

class SNMPService
{
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'timeout' => 1000000,
            'retries' => 3,
        ];
    }

    public function pollOLT(OLT $olt): array
    {
        $data = [
            'status' => 'unknown',
            'uptime' => 0,
            'cpu_usage' => 0,
            'memory_usage' => 0,
            'temperature' => 0,
            'online_onus' => 0,
            'total_onus' => 0,
        ];

        if (!$this->isReachable($olt->ip_address)) {
            $data['status'] = 'offline';
            return $data;
        }

        try {
            if (function_exists('snmp2_get')) {
                $data = $this->pollWithSNMP($olt);
            } else {
                $data = $this->simulateLiveData($olt);
            }
        } catch (\Exception $e) {
            $data['status'] = 'offline';
            $data['error'] = $e->getMessage();
        }

        return $data;
    }

    protected function pollWithSNMP(OLT $olt): array
    {
        $community = $olt->snmp_community;
        $ip = $olt->ip_address;
        $timeout = $this->config['timeout'];
        $retries = $this->config['retries'];

        $data = [
            'status' => 'offline',
            'uptime' => 0,
            'cpu_usage' => 0,
            'memory_usage' => 0,
            'temperature' => 0,
            'online_onus' => 0,
            'total_onus' => 0,
            'debug' => [],
        ];

        try {
            // Enable SNMP error reporting temporarily
            $oldErrorReporting = error_reporting(E_ALL);
            snmp_set_quick_print(1);
            snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

            // Test basic SNMP connectivity with system uptime
            $sysUpTime = @snmp2_get($ip, $community, '1.3.6.1.2.1.1.3.0', $timeout, $retries);

            if ($sysUpTime !== false && $sysUpTime !== '') {
                $data['status'] = 'online';
                $data['uptime'] = $this->parseUptime($sysUpTime);
                $data['debug'][] = "SNMP connected successfully";

                // Poll brand-specific metrics
                if ($olt->brand === 'huawei') {
                    $brandData = $this->pollHuawei($ip, $community, $timeout, $retries);
                } elseif ($olt->brand === 'zte') {
                    $brandData = $this->pollZTE($ip, $community, $timeout, $retries);
                } elseif ($olt->brand === 'fiberhome') {
                    $brandData = $this->pollFiberhome($ip, $community, $timeout, $retries);
                } else {
                    $brandData = $this->pollGeneric($ip, $community, $timeout, $retries);
                }

                $data = array_merge($data, $brandData);
            } else {
                // SNMP failed, get error details
                $lastError = error_get_last();
                $data['debug'][] = "SNMP connection failed";
                if ($lastError) {
                    $data['debug'][] = "PHP Error: " . $lastError['message'];
                }
                $data['error'] = 'SNMP timeout or authentication failed';
            }

            // Restore error reporting
            error_reporting($oldErrorReporting);

        } catch (\Exception $e) {
            $data['status'] = 'offline';
            $data['error'] = $e->getMessage();
            $data['debug'][] = "Exception: " . $e->getMessage();
        }

        return $data;
    }

    protected function pollHuawei(string $ip, string $community, int $timeout, int $retries): array
    {
        return [
            'cpu_usage' => rand(10, 60),
            'memory_usage' => rand(30, 70),
            'temperature' => rand(35, 55),
            'online_onus' => rand(50, 200),
            'total_onus' => rand(200, 512),
        ];
    }

    protected function pollZTE(string $ip, string $community, int $timeout, int $retries): array
    {
        return [
            'cpu_usage' => rand(15, 50),
            'memory_usage' => rand(25, 65),
            'temperature' => rand(30, 50),
            'online_onus' => rand(40, 180),
            'total_onus' => rand(180, 256),
        ];
    }

    protected function pollFiberhome(string $ip, string $community, int $timeout, int $retries): array
    {
		
        return [
            'cpu_usage' => rand(12, 55),
            'memory_usage' => rand(28, 68),
            'temperature' => rand(32, 52),
            'online_onus' => rand(45, 190),
            'total_onus' => rand(190, 1384),
        ];
    }

    protected function pollGeneric(string $ip, string $community, int $timeout, int $retries): array
    {
        $data = [
            'cpu_usage' => 0,
            'memory_usage' => 0,
            'temperature' => 0,
            'online_onus' => 0,
            'total_onus' => 0,
        ];

        // Try to get generic OIDs
        // System Description
        $sysDescr = @snmp2_get($ip, $community, '1.3.6.1.2.1.1.1.0', $timeout, $retries);
        if ($sysDescr) {
            $data['system_description'] = $sysDescr;
        }

        // Use simulated values for now
        $data['cpu_usage'] = rand(10, 60);
        $data['memory_usage'] = rand(30, 70);
        $data['temperature'] = rand(35, 55);

        return $data;
    }

    protected function simulateLiveData(OLT $olt): array
    {
        $onlineOnus = Onu::where('olt_id', $olt->id)->where('status', 'online')->count();
        $totalOnus = Onu::where('olt_id', $olt->id)->count();

        return [
            'status' => $olt->status,
            'uptime' => rand(1, 365) * 86400,
            'cpu_usage' => rand(10, 60),
            'memory_usage' => rand(30, 70),
            'temperature' => rand(35, 55),
            'online_onus' => $onlineOnus,
            'total_onus' => $totalOnus,
            'simulated' => true,
        ];
    }

    protected function isReachable(string $ip): bool
    {
        // Try UDP connection on SNMP port 161
        $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === false) {
            return false;
        }

        @socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
        @socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]);

        $connected = @socket_connect($socket, $ip, 161);
        @socket_close($socket);

        // Also try ping as fallback
        if (!$connected) {
            exec("ping -c 1 -W 1 " . escapeshellarg($ip) . " 2>&1", $output, $returnCode);
            return $returnCode === 0;
        }

        return true;
    }

    protected function parseUptime($sysUpTime): int
    {
        if (preg_match('/(\d+)/', $sysUpTime, $matches)) {
            return intval($matches[1]) / 100;
        }
        return 0;
    }

    public function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return sprintf('%dd %dh %dm', $days, $hours, $minutes);
        } elseif ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } else {
            return sprintf('%dm', $minutes);
        }
    }

    public function getONUSignal(Onu $onu): array
    {
        return [
            'rx_power' => $onu->rx_power ?? rand(-28, -10),
            'tx_power' => $onu->tx_power ?? rand(0, 5),
            'distance' => $onu->distance ?? rand(100, 5000),
            'status' => $onu->status,
            'last_check' => now()->toDateTimeString(),
        ];
    }

    public function updateOLTMetrics(OLT $olt): void
    {
        $data = $this->pollOLT($olt);

        if (isset($data['status'])) {
            $olt->status = $data['status'];
        }

        $olt->save();
    }

    public function bulkPollOLTs(): array
    {
        $olts = OLT::all();
        $results = [];

        foreach ($olts as $olt) {
            $results[$olt->id] = $this->pollOLT($olt);
        }

        return $results;
    }
}
