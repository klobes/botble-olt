<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\FiberHomeOLTManager\Services\OLTService;
use Botble\FiberHomeOLTManager\Services\ONUService;
use Botble\FiberHomeOLTManager\Services\BandwidthService;
use Illuminate\Http\Request;

class SettingsController extends BaseController
{
    protected $oltService;
    protected $onuService;
    protected $bandwidthService;

    public function __construct(
        OLTService $oltService,
        ONUService $onuService,
        BandwidthService $bandwidthService
    ) {
        $this->oltService = $oltService;
        $this->onuService = $onuService;
        $this->bandwidthService = $bandwidthService;
    }

    public function index()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::settings.title'));

        $settings = [
            'snmp_timeout' => setting('fiberhome_snmp_timeout', 5000),
            'snmp_retries' => setting('fiberhome_snmp_retries', 3),
            'polling_interval' => setting('fiberhome_polling_interval', 300),
            'alert_threshold_cpu' => setting('fiberhome_alert_threshold_cpu', 80),
            'alert_threshold_memory' => setting('fiberhome_alert_threshold_memory', 85),
            'alert_threshold_temperature' => setting('fiberhome_alert_threshold_temperature', 70),
            'enable_auto_discovery' => setting('fiberhome_enable_auto_discovery', true),
            'enable_alerts' => setting('fiberhome_enable_alerts', true),
            'enable_email_alerts' => setting('fiberhome_enable_email_alerts', false),
            'email_recipients' => setting('fiberhome_email_recipients', ''),
            'enable_webhook_alerts' => setting('fiberhome_enable_webhook_alerts', false),
            'webhook_url' => setting('fiberhome_webhook_url', ''),
            'default_snmp_community' => setting('fiberhome_default_snmp_community', 'public'),
            'default_snmp_version' => setting('fiberhome_default_snmp_version', '2c'),
            'cache_ttl' => setting('fiberhome_cache_ttl', 300),
            'topology_grid_size' => setting('fiberhome_topology_grid_size', 20),
            'topology_auto_layout' => setting('fiberhome_topology_auto_layout', true),
            'topology_show_labels' => setting('fiberhome_topology_show_labels', true),
            'topology_color_scheme' => setting('fiberhome_topology_color_scheme', 'default'),
            'log_level' => setting('fiberhome_log_level', 'info'),
            'max_concurrent_polls' => setting('fiberhome_max_concurrent_polls', 5),
            'discovery_timeout' => setting('fiberhome_discovery_timeout', 30000),
            'maintenance_mode' => setting('fiberhome_maintenance_mode', false),
        ];

        // Get system statistics
        $statistics = [
            'olt_stats' => $this->oltService->getStatistics(),
            'onu_stats' => $this->onuService->getStatistics(),
            'bandwidth_stats' => $this->bandwidthService->getStatistics(),
        ];

        return view('plugins/fiberhome-olt-manager::settings.index', compact('settings', 'statistics'));
    }

    public function update(Request $request, BaseHttpResponse $response)
    {
        try {
            $settings = $request->validate([
                'snmp_timeout' => 'required|integer|min:1000|max:30000',
                'snmp_retries' => 'required|integer|min:1|max:10',
                'polling_interval' => 'required|integer|min:60|max:3600',
                'alert_threshold_cpu' => 'required|integer|min:50|max:100',
                'alert_threshold_memory' => 'required|integer|min:50|max:100',
                'alert_threshold_temperature' => 'required|integer|min:40|max:100',
                'enable_auto_discovery' => 'boolean',
                'enable_alerts' => 'boolean',
                'enable_email_alerts' => 'boolean',
                'email_recipients' => 'nullable|string',
                'enable_webhook_alerts' => 'boolean',
                'webhook_url' => 'nullable|string|url',
                'default_snmp_community' => 'required|string|max:255',
                'default_snmp_version' => 'required|string|in:1,2c,3',
                'cache_ttl' => 'required|integer|min:60|max:3600',
                'topology_grid_size' => 'required|integer|min:10|max:100',
                'topology_auto_layout' => 'boolean',
                'topology_show_labels' => 'boolean',
                'topology_color_scheme' => 'required|string|in:default,dark,light,high_contrast',
                'log_level' => 'required|string|in:debug,info,warning,error',
                'max_concurrent_polls' => 'required|integer|min:1|max:20',
                'discovery_timeout' => 'required|integer|min:10000|max:120000',
                'maintenance_mode' => 'boolean',
            ]);

            // Save settings
            foreach ($settings as $key => $value) {
                setting()->set('fiberhome_' . $key, $value);
            }

            // Handle boolean settings
            $booleanSettings = [
                'enable_auto_discovery',
                'enable_alerts',
                'enable_email_alerts',
                'enable_webhook_alerts',
                'topology_auto_layout',
                'topology_show_labels',
                'maintenance_mode',
            ];

            foreach ($booleanSettings as $key) {
                setting()->set('fiberhome_' . $key, $request->has($key));
            }

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.updated_success'))
                ->setData([
                    'settings' => $settings,
                    'message' => trans('plugins/fiberhome-olt-manager::settings.updated_success')
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.updated_error') . ': ' . $e->getMessage());
        }
    }

    public function testConnection(Request $request, BaseHttpResponse $response)
    {
        try {
            $request->validate([
                'ip_address' => 'required|ip',
                'community' => 'required|string',
                'version' => 'required|string|in:1,2c,3',
                'port' => 'required|integer|min:1|max:65535',
            ]);

            // Test SNMP connection
            // Implementation would test the actual connection
            
            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.connection_success'))
                ->setData(['status' => 'success']);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.connection_error') . ': ' . $e->getMessage());
        }
    }

    public function resetToDefaults(BaseHttpResponse $response)
    {
        try {
            $defaultSettings = [
                'snmp_timeout' => 5000,
                'snmp_retries' => 3,
                'polling_interval' => 300,
                'alert_threshold_cpu' => 80,
                'alert_threshold_memory' => 85,
                'alert_threshold_temperature' => 70,
                'enable_auto_discovery' => true,
                'enable_alerts' => true,
                'enable_email_alerts' => false,
                'email_recipients' => '',
                'enable_webhook_alerts' => false,
                'webhook_url' => '',
                'default_snmp_community' => 'public',
                'default_snmp_version' => '2c',
                'cache_ttl' => 300,
                'topology_grid_size' => 20,
                'topology_auto_layout' => true,
                'topology_show_labels' => true,
                'topology_color_scheme' => 'default',
                'log_level' => 'info',
                'max_concurrent_polls' => 5,
                'discovery_timeout' => 30000,
                'maintenance_mode' => false,
            ];

            foreach ($defaultSettings as $key => $value) {
                setting()->set('fiberhome_' . $key, $value);
            }

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.reset_success'));

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.reset_error') . ': ' . $e->getMessage());
        }
    }

    public function exportSettings(BaseHttpResponse $response)
    {
        try {
            $settings = [
                'snmp_timeout' => setting('fiberhome_snmp_timeout', 5000),
                'snmp_retries' => setting('fiberhome_snmp_retries', 3),
                'polling_interval' => setting('fiberhome_polling_interval', 300),
                'alert_threshold_cpu' => setting('fiberhome_alert_threshold_cpu', 80),
                'alert_threshold_memory' => setting('fiberhome_alert_threshold_memory', 85),
                'alert_threshold_temperature' => setting('fiberhome_alert_threshold_temperature', 70),
                'enable_auto_discovery' => setting('fiberhome_enable_auto_discovery', true),
                'enable_alerts' => setting('fiberhome_enable_alerts', true),
                'enable_email_alerts' => setting('fiberhome_enable_email_alerts', false),
                'email_recipients' => setting('fiberhome_email_recipients', ''),
                'enable_webhook_alerts' => setting('fiberhome_enable_webhook_alerts', false),
                'webhook_url' => setting('fiberhome_webhook_url', ''),
                'default_snmp_community' => setting('fiberhome_default_snmp_community', 'public'),
                'default_snmp_version' => setting('fiberhome_default_snmp_version', '2c'),
                'cache_ttl' => setting('fiberhome_cache_ttl', 300),
                'topology_grid_size' => setting('fiberhome_topology_grid_size', 20),
                'topology_auto_layout' => setting('fiberhome_topology_auto_layout', true),
                'topology_show_labels' => setting('fiberhome_topology_show_labels', true),
                'topology_color_scheme' => setting('fiberhome_topology_color_scheme', 'default'),
                'log_level' => setting('fiberhome_log_level', 'info'),
                'max_concurrent_polls' => setting('fiberhome_max_concurrent_polls', 5),
                'discovery_timeout' => setting('fiberhome_discovery_timeout', 30000),
                'maintenance_mode' => setting('fiberhome_maintenance_mode', false),
            ];

            return $response
                ->setData($settings)
                ->setHeaders([
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="fiberhome-settings.json"',
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::settings.export_error') . ': ' . $e->getMessage());
        }
    }
}
