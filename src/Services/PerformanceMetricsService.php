<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\ONU;
use Botble\FiberHomeOLTManager\Models\OltPerformanceLog;
use Carbon\Carbon;

class PerformanceMetricsService
{
    /**
     * Get system performance summary
     */
    public function getSystemPerformanceSummary(): array
    {
        return [
            'cpu' => $this->getAverageCpuUsage(),
            'memory' => $this->getAverageMemoryUsage(),
            'temperature' => $this->getAverageTemperature(),
            'uptime' => $this->getAverageUptime(),
            'network_usage' => $this->getNetworkUsageStats(),
            'olt_health' => $this->getOltHealthSummary(),
        ];
    }

    /**
     * Get average CPU usage across all OLTs
     */
    public function getAverageCpuUsage(): array
    {
        $avgCpu = OLT::where('cpu_usage', '>', 0)->avg('cpu_usage');
        $maxCpu = OLT::where('cpu_usage', '>', 0)->max('cpu_usage');
        
        return [
            'average' => round($avgCpu, 2),
            'maximum' => round($maxCpu, 2),
            'status' => $this->getCpuStatus($avgCpu),
        ];
    }

    /**
     * Get average memory usage across all OLTs
     */
    public function getAverageMemoryUsage(): array
    {
        $avgMemory = OLT::where('memory_usage', '>', 0)->avg('memory_usage');
        $maxMemory = OLT::where('memory_usage', '>', 0)->max('memory_usage');
        
        return [
            'average' => round($avgMemory, 2),
            'maximum' => round($maxMemory, 2),
            'status' => $this->getMemoryStatus($avgMemory),
        ];
    }

    /**
     * Get average temperature across all OLTs
     */
    public function getAverageTemperature(): array
    {
        $avgTemp = OLT::where('temperature', '>', 0)->avg('temperature');
        $maxTemp = OLT::where('temperature', '>', 0)->max('temperature');
        
        return [
            'average' => round($avgTemp, 1),
            'maximum' => round($maxTemp, 1),
            'status' => $this->getTemperatureStatus($avgTemp),
        ];
    }

    /**
     * Get average uptime across all OLTs
     */
    public function getAverageUptime(): array
    {
        $totalUptime = 0;
        $count = 0;
        
        OLT::where('uptime', '>', 0)->chunk(100, function ($olts) use (&$totalUptime, &$count) {
            foreach ($olts as $olt) {
                $totalUptime += $olt->uptime;
                $count++;
            }
        });
        
        $averageUptime = $count > 0 ? $totalUptime / $count : 0;
        
        return [
            'average_days' => round($averageUptime / 86400, 1), // Convert to days
            'status' => $this->getUptimeStatus($averageUptime),
        ];
    }

    /**
     * Get network usage statistics
     */
    public function getNetworkUsageStats(): array
    {
        $totalOnus = ONU::count();
        $onlineOnus = ONU::where('status', 'online')->count();
        $offlineOnus = $totalOnus - $onlineOnus;
        
        $totalOlts = OLT::count();
        $onlineOlts = OLT::where('status', 'online')->count();
        $offlineOlts = $totalOlts - $onlineOlts;
        
        return [
            'onu_online_percentage' => $totalOnus > 0 ? round(($onlineOnus / $totalOnus) * 100, 1) : 0,
            'onu_offline_percentage' => $totalOnus > 0 ? round(($offlineOnus / $totalOnus) * 100, 1) : 0,
            'olt_online_percentage' => $totalOlts > 0 ? round(($onlineOlts / $totalOlts) * 100, 1) : 0,
            'olt_offline_percentage' => $totalOlts > 0 ? round(($offlineOlts / $totalOlts) * 100, 1) : 0,
        ];
    }

    /**
     * Get OLT health summary
     */
    public function getOltHealthSummary(): array
    {
        $healthyOlts = OLT::where('status', 'online')
            ->where('cpu_usage', '<', 80)
            ->where('memory_usage', '<', 85)
            ->where('temperature', '<', 70)
            ->count();
            
        $warningOlts = OLT::where(function ($query) {
            $query->where('cpu_usage', '>=', 80)
                ->orWhere('memory_usage', '>=', 85)
                ->orWhere('temperature', '>=', 70);
        })->where('status', 'online')->count();
        
        $criticalOlts = OLT::where('status', 'offline')->count();
        
        $totalOlts = OLT::count();
        
        return [
            'healthy' => $healthyOlts,
            'warning' => $warningOlts,
            'critical' => $criticalOlts,
            'healthy_percentage' => $totalOlts > 0 ? round(($healthyOlts / $totalOlts) * 100, 1) : 0,
            'warning_percentage' => $totalOlts > 0 ? round(($warningOlts / $totalOlts) * 100, 1) : 0,
            'critical_percentage' => $totalOlts > 0 ? round(($criticalOlts / $totalOlts) * 100, 1) : 0,
        ];
    }

    /**
     * Get historical performance data for charts
     */
    public function getHistoricalPerformance(string $metric, int $hours = 24): array
    {
        $data = OltPerformanceLog::where('created_at', '>=', Carbon::now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get();
            
        $labels = [];
        $values = [];
        
        foreach ($data as $log) {
            $labels[] = $log->created_at->format('H:i');
            $values[] = $log->$metric ?? 0;
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Helper methods for status determination
     */
    private function getCpuStatus(?float $cpu): string
    {
        if ($cpu === null) return 'unknown';
        if ($cpu < 70) return 'good';
        if ($cpu < 85) return 'warning';
        return 'critical';
    }

    private function getMemoryStatus(?float $memory): string
    {
        if ($memory === null) return 'unknown';
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

    private function getUptimeStatus(int $uptime): string
    {
        $days = $uptime / 86400;
        if ($days > 30) return 'excellent';
        if ($days > 7) return 'good';
        if ($days > 1) return 'warning';
        return 'critical';
    }
}