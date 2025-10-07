
<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\Onu as ONU;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class AlertService
{
    /**
     * Check and send alerts for OLT issues
     */
    public function checkOLTAlerts(OLT $olt): array
    {
        $alerts = [];

        // Check CPU usage
        if ($olt->cpu_usage > setting('fiberhome_alert_threshold_cpu', 80)) {
            $alerts[] = [
                'type' => 'cpu',
                'severity' => 'warning',
                'message' => "OLT {$olt->name} CPU usage is {$olt->cpu_usage}%",
                'olt_id' => $olt->id,
            ];
        }

        // Check memory usage
        if ($olt->memory_usage > setting('fiberhome_alert_threshold_memory', 85)) {
            $alerts[] = [
                'type' => 'memory',
                'severity' => 'warning',
                'message' => "OLT {$olt->name} memory usage is {$olt->memory_usage}%",
                'olt_id' => $olt->id,
            ];
        }

        // Check temperature
        if ($olt->temperature && $olt->temperature > setting('fiberhome_alert_threshold_temperature', 70)) {
            $alerts[] = [
                'type' => 'temperature',
                'severity' => 'critical',
                'message' => "OLT {$olt->name} temperature is {$olt->temperature}\u00b0C",
                'olt_id' => $olt->id,
            ];
        }

        // Check status
        if ($olt->status === 'offline') {
            $alerts[] = [
                'type' => 'status',
                'severity' => 'critical',
                'message' => "OLT {$olt->name} is offline",
                'olt_id' => $olt->id,
            ];
        }

        // Send alerts if enabled
        if (!empty($alerts) && setting('fiberhome_enable_alerts', true)) {
            $this->sendAlerts($alerts);
        }

        return $alerts;
    }

    /**
     * Check and send alerts for ONU issues
     */
    public function checkONUAlerts(ONU $onu): array
    {
        $alerts = [];

        // Check status
        if ($onu->status === 'offline') {
            $alerts[] = [
                'type' => 'status',
                'severity' => 'warning',
                'message' => "ONU {$onu->serial_number} is offline",
                'onu_id' => $onu->id,
            ];
        }

        // Check signal strength
        if (isset($onu->rx_power) && $onu->rx_power < -28) {
            $alerts[] = [
                'type' => 'signal',
                'severity' => 'warning',
                'message' => "ONU {$onu->serial_number} has weak signal: {$onu->rx_power} dBm",
                'onu_id' => $onu->id,
            ];
        }

        // Send alerts if enabled
        if (!empty($alerts) && setting('fiberhome_enable_alerts', true)) {
            $this->sendAlerts($alerts);
        }

        return $alerts;
    }

    /**
     * Send alerts via configured channels
     */
    protected function sendAlerts(array $alerts): void
    {
        foreach ($alerts as $alert) {
            // Log alert
            Log::channel('fiberhome')->warning('Alert: ' . $alert['message'], $alert);

            // Send email if enabled
            if (setting('fiberhome_enable_email_alerts', false)) {
                $this->sendEmailAlert($alert);
            }

            // Send webhook if enabled
            if (setting('fiberhome_enable_webhook_alerts', false)) {
                $this->sendWebhookAlert($alert);
            }
        }
    }

    /**
     * Send email alert
     */
    protected function sendEmailAlert(array $alert): void
    {
        try {
            $recipients = explode(',', setting('fiberhome_email_recipients', ''));
            $recipients = array_map('trim', $recipients);

            foreach ($recipients as $recipient) {
                if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    Mail::raw($alert['message'], function ($message) use ($recipient, $alert) {
                        $message->to($recipient)
                            ->subject("FiberHome Alert: {$alert['type']} - {$alert['severity']}");
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email alert: ' . $e->getMessage());
        }
    }

    /**
     * Send webhook alert
     */
    protected function sendWebhookAlert(array $alert): void
    {
        try {
            $webhookUrl = setting('fiberhome_webhook_url', '');

            if ($webhookUrl) {
                Http::post($webhookUrl, [
                    'alert' => $alert,
                    'timestamp' => now()->toIso8601String(),
                    'source' => 'FiberHome OLT Manager',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send webhook alert: ' . $e->getMessage());
        }
    }

    /**
     * Get all active alerts
     */
    public function getActiveAlerts(): array
    {
        $alerts = [];

        // Get OLT alerts
        $olts = OLT::where('status', 'offline')
            ->orWhere('cpu_usage', '>', setting('fiberhome_alert_threshold_cpu', 80))
            ->orWhere('memory_usage', '>', setting('fiberhome_alert_threshold_memory', 85))
            ->get();

        foreach ($olts as $olt) {
            $alerts = array_merge($alerts, $this->checkOLTAlerts($olt));
        }

        // Get ONU alerts
        $onus = ONU::where('status', 'offline')->get();

        foreach ($onus as $onu) {
            $alerts = array_merge($alerts, $this->checkONUAlerts($onu));
        }

        return $alerts;
    }

    /**
     * Clear old alerts
     */
    public function clearOldAlerts(int $days = 7): int
    {
        // Implementation to clear old alerts from database
        return 0;
    }
}
