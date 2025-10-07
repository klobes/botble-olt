<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\Onu;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Botble\FiberHomeOLTManager\Services\PerformanceMetricsService;
use Illuminate\Http\Request;
use Botble\Base\Facades\Assets;

class DashboardController extends BaseController
{
    protected $metricsService;

    public function __construct(PerformanceMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function index()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::dashboard.title'));
        Assets::addScriptsDirectly(['vendor/core/plugins/fiberhome-olt-manager/js/dashboard.js']);

        // Get statistics
        $stats = [
            'total_olts' => OLT::count(),
            'online_olts' => OLT::where('status', 'online')->count(),
            'total_onus' => Onu::count(),
            'online_onus' => Onu::where('status', 'online')->count(),
            'total_profiles' => BandwidthProfile::count(),
            'active_profiles' => BandwidthProfile::where('status', 'active')->count(),
        ];

        // Get recent alerts
        $alerts = $this->getRecentAlerts();

        // Get performance summary
        $performance = $this->metricsService->getSystemPerformanceSummary();

        return view('plugins/fiberhome-olt-manager::dashboard', compact('stats', 'alerts', 'performance'));
    }

    public function topology()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::topology.title'));

        $olts = OLT::with(['onus' => function ($query) {
            $query->select(['id', 'olt_id', 'serial_number', 'status', 'slot', 'port']);
        }])->get();

        return view('plugins/fiberhome-olt-manager::topology.index', compact('olts'));
    }

    private function getRecentAlerts()
    {
        $alerts = [];

        // Check OLT status alerts
        $offlineOlts = OLT::where('status', 'offline')
            ->where('last_polled', '<', now()->subMinutes(15))
            ->get();

        foreach ($offlineOlts as $olt) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa fa-exclamation-triangle',
                'title' => trans('plugins/fiberhome-olt-manager::alerts.olt_offline'),
                'message' => trans('plugins/fiberhome-olt-manager::alerts.olt_offline_message', ['name' => $olt->name]),
                'time' => $olt->last_polled,
            ];
        }

        // Check ONU status alerts
        $offlineOnus = Onu::where('status', 'offline')
            ->where('last_seen', '<', now()->subMinutes(30))
            ->take(5)
            ->get();

        foreach ($offlineOnus as $onu) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa fa-warning',
                'title' => trans('plugins/fiberhome-olt-manager::alerts.onu_offline'),
                'message' => trans('plugins/fiberhome-olt-manager::alerts.onu_offline_message', ['serial' => $onu->serial_number]),
                'time' => $onu->last_seen,
            ];
        }

        // Check performance alerts
        $highCpuOlts = OLT::where('cpu_usage', '>', setting('fiberhome_alert_threshold_cpu', 80))
            ->get();

        foreach ($highCpuOlts as $olt) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa fa-tachometer',
                'title' => trans('plugins/fiberhome-olt-manager::alerts.high_cpu_usage'),
                'message' => trans('plugins/fiberhome-olt-manager::alerts.high_cpu_message', ['name' => $olt->name, 'usage' => $olt->cpu_usage]),
                'time' => now(),
            ];
        }

        // Sort alerts by time (newest first)
        usort($alerts, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($alerts, 0, 10); // Return only 10 most recent alerts
    }
    
    /**
     * Get dashboard data for real-time updates
     */
    public function getData(Request $request)
    {
        // Get statistics
        $statistics = [
            'total_olts' => OLT::count(),
            'online_olts' => OLT::where('status', 'online')->count(),
            'total_onus' => Onu::count(),
            'online_onus' => Onu::where('status', 'online')->count(),
            'offline_onus' => Onu::where('status', 'offline')->count(),
        ];
        
        // Get performance data
        $performance = [
            'cpu' => OLT::where('status', 'online')->avg('cpu_usage') ?? 0,
            'memory' => OLT::where('status', 'online')->avg('memory_usage') ?? 0,
            'temperature' => OLT::where('status', 'online')->avg('temperature') ?? 0,
        ];
        
        // Get ONU status distribution
        $onu_status = [
            'online' => Onu::where('status', 'online')->count(),
            'offline' => Onu::where('status', 'offline')->count(),
            'los' => Onu::where('status', 'los')->count(),
            'dying_gasp' => Onu::where('status', 'dying_gasp')->count(),
        ];
        
        // Get recent alerts
        $alerts = $this->getRecentAlerts();
        
        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'performance' => $performance,
            'onu_status' => $onu_status,
            'alerts' => $alerts,
        ]);
    }
}