
<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\ONU;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Botble\FiberHomeOLTManager\Models\OltPerformanceLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate system overview report
     */
    public function generateSystemOverview(): array
    {
        return [
            'summary' => [
                'total_olts' => OLT::count(),
                'online_olts' => OLT::where('status', 'online')->count(),
                'offline_olts' => OLT::where('status', 'offline')->count(),
                'total_onus' => ONU::count(),
                'online_onus' => ONU::where('status', 'online')->count(),
                'offline_onus' => ONU::where('status', 'offline')->count(),
                'total_bandwidth_profiles' => BandwidthProfile::count(),
            ],
            'olt_by_model' => OLT::select('model', DB::raw('count(*) as count'))
                ->groupBy('model')
                ->get()
                ->toArray(),
            'onu_by_olt' => ONU::select('olt_id', DB::raw('count(*) as count'))
                ->groupBy('olt_id')
                ->with('olt:id,name')
                ->get()
                ->toArray(),
            'bandwidth_usage' => $this->getBandwidthUsageStats(),
        ];
    }

    /**
     * Generate OLT performance report
     */
    public function generateOLTPerformanceReport(int $oltId, int $days = 7): array
    {
        $olt = OLT::find($oltId);
        
        if (!$olt) {
            return [];
        }

        $startDate = Carbon::now()->subDays($days);

        return [
            'olt' => $olt->toArray(),
            'performance_data' => OltPerformanceLog::where('olt_id', $oltId)
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray(),
            'statistics' => [
                'avg_cpu' => OltPerformanceLog::where('olt_id', $oltId)
                    ->where('created_at', '>=', $startDate)
                    ->avg('cpu_usage'),
                'max_cpu' => OltPerformanceLog::where('olt_id', $oltId)
                    ->where('created_at', '>=', $startDate)
                    ->max('cpu_usage'),
                'avg_memory' => OltPerformanceLog::where('olt_id', $oltId)
                    ->where('created_at', '>=', $startDate)
                    ->avg('memory_usage'),
                'max_memory' => OltPerformanceLog::where('olt_id', $oltId)
                    ->where('created_at', '>=', $startDate)
                    ->max('memory_usage'),
                'avg_temperature' => OltPerformanceLog::where('olt_id', $oltId)
                    ->where('created_at', '>=', $startDate)
                    ->avg('temperature'),
                'max_temperature' => OltPerformanceLog::where('olt_id', $oltId)
                    ->where('created_at', '>=', $startDate)
                    ->max('temperature'),
            ],
        ];
    }

    /**
     * Generate ONU status report
     */
    public function generateONUStatusReport(int $oltId = null): array
    {
        $query = ONU::with(['olt', 'bandwidthProfile']);

        if ($oltId) {
            $query->where('olt_id', $oltId);
        }

        $onus = $query->get();

        return [
            'total' => $onus->count(),
            'online' => $onus->where('status', 'online')->count(),
            'offline' => $onus->where('status', 'offline')->count(),
            'by_status' => $onus->groupBy('status')->map->count()->toArray(),
            'by_olt' => $onus->groupBy('olt_id')->map->count()->toArray(),
            'with_bandwidth_profile' => $onus->whereNotNull('bandwidth_profile_id')->count(),
            'without_bandwidth_profile' => $onus->whereNull('bandwidth_profile_id')->count(),
            'onus' => $onus->toArray(),
        ];
    }

    /**
     * Generate bandwidth usage report
     */
    public function generateBandwidthReport(): array
    {
        $profiles = BandwidthProfile::with(['onus'])->get();

        $report = [];

        foreach ($profiles as $profile) {
            $onuCount = $profile->onus->count();
            
            $report[] = [
                'profile' => $profile->toArray(),
                'onu_count' => $onuCount,
                'total_download_capacity' => $profile->download_speed * $onuCount,
                'total_upload_capacity' => $profile->upload_speed * $onuCount,
                'utilization' => $this->calculateUtilization($profile),
            ];
        }

        return [
            'profiles' => $report,
            'summary' => [
                'total_profiles' => $profiles->count(),
                'total_onus_with_profile' => ONU::whereNotNull('bandwidth_profile_id')->count(),
                'total_download_capacity' => collect($report)->sum('total_download_capacity'),
                'total_upload_capacity' => collect($report)->sum('total_upload_capacity'),
            ],
        ];
    }

    /**
     * Generate uptime report
     */
    public function generateUptimeReport(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $olts = OLT::all();
        $report = [];

        foreach ($olts as $olt) {
            $logs = OltPerformanceLog::where('olt_id', $olt->id)
                ->where('created_at', '>=', $startDate)
                ->get();

            $totalChecks = $logs->count();
            $onlineChecks = $logs->where('status', 'online')->count();
            $uptime = $totalChecks > 0 ? ($onlineChecks / $totalChecks) * 100 : 0;

            $report[] = [
                'olt' => $olt->toArray(),
                'uptime_percentage' => round($uptime, 2),
                'total_checks' => $totalChecks,
                'online_checks' => $onlineChecks,
                'offline_checks' => $totalChecks - $onlineChecks,
            ];
        }

        return [
            'period' => "{$days} days",
            'start_date' => $startDate->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
            'olts' => $report,
            'average_uptime' => collect($report)->avg('uptime_percentage'),
        ];
    }

    /**
     * Generate alert history report
     */
    public function generateAlertHistoryReport(int $days = 7): array
    {
        // This would query an alerts table if it exists
        // For now, return current alerts
        
        $alertService = app(AlertService::class);
        
        return [
            'period' => "{$days} days",
            'active_alerts' => $alertService->getActiveAlerts(),
            'summary' => [
                'total_alerts' => 0,
                'critical_alerts' => 0,
                'warning_alerts' => 0,
                'resolved_alerts' => 0,
            ],
        ];
    }

    /**
     * Export report to CSV
     */
    public function exportToCSV(array $data, string $filename): string
    {
        $filepath = storage_path("app/reports/{$filename}");
        
        $fp = fopen($filepath, 'w');
        
        // Write headers
        if (!empty($data)) {
            fputcsv($fp, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($fp, $row);
            }
        }
        
        fclose($fp);
        
        return $filepath;
    }

    /**
     * Helper methods
     */
    protected function getBandwidthUsageStats(): array
    {
        $profiles = BandwidthProfile::with(['onus'])->get();
        
        $totalDownload = 0;
        $totalUpload = 0;
        
        foreach ($profiles as $profile) {
            $onuCount = $profile->onus->count();
            $totalDownload += $profile->download_speed * $onuCount;
            $totalUpload += $profile->upload_speed * $onuCount;
        }
        
        return [
            'total_download_capacity' => $totalDownload,
            'total_upload_capacity' => $totalUpload,
            'profiles_in_use' => $profiles->filter(fn($p) => $p->onus->count() > 0)->count(),
        ];
    }

    protected function calculateUtilization(BandwidthProfile $profile): float
    {
        // This would calculate actual utilization if we had traffic data
        // For now, return estimated utilization based on ONU count
        $onuCount = $profile->onus->count();
        $maxCapacity = $profile->download_speed;
        
        // Assume average 30% utilization per ONU
        return min(100, ($onuCount * 30));
    }
}
