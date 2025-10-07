<?php

namespace Botble\FiberHomeOLTManager\Jobs;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Services\OLTService;
use Botble\FiberHomeOLTManager\Services\VendorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class PollOltJob
 * 
 * Background job for polling OLT devices
 * Collects performance metrics, ONU status, and system information
 */
class PollOltJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The OLT device to poll
     *
     * @var OltDevice
     */
    protected $oltDevice;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param OltDevice $oltDevice
     */
    public function __construct(OltDevice $oltDevice)
    {
        $this->oltDevice = $oltDevice;
    }

    /**
     * Execute the job.
     *
     * @param OLTService $oltService
     * @param VendorService $vendorService
     * @return void
     */
    public function handle(OLTService $oltService, VendorService $vendorService)
    {
        try {
            Log::info("Starting poll for OLT: {$this->oltDevice->name}");

            // Get vendor driver
            $driver = $vendorService->getDriver($this->oltDevice);

            // Validate connection
            if (!$driver->validateConnection($this->oltDevice)) {
                $this->oltDevice->update([
                    'status' => 'offline',
                    'last_poll' => now(),
                ]);
                Log::warning("OLT {$this->oltDevice->name} is offline");
                return;
            }

            // Update OLT status
            $this->oltDevice->update([
                'status' => 'online',
                'last_poll' => now(),
            ]);

            // Collect system information
            $this->collectSystemInfo($driver);

            // Collect performance metrics
            $this->collectPerformanceMetrics($driver);

            // Collect cards information
            $this->collectCardsInfo($driver);

            // Collect PON ports information
            $this->collectPonPortsInfo($driver);

            // Collect ONUs information
            $this->collectOnusInfo($driver);

            Log::info("Successfully polled OLT: {$this->oltDevice->name}");

        } catch (\Exception $e) {
            Log::error("Failed to poll OLT {$this->oltDevice->name}: " . $e->getMessage());
            
            $this->oltDevice->update([
                'status' => 'error',
                'last_poll' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Collect system information
     *
     * @param mixed $driver
     * @return void
     */
    protected function collectSystemInfo($driver)
    {
        try {
            $systemInfo = $driver->getSystemInfo($this->oltDevice);

            $this->oltDevice->update([
                'system_description' => $systemInfo['description'] ?? null,
                'uptime' => $systemInfo['uptime'] ?? null,
                'system_name' => $systemInfo['name'] ?? null,
                'location' => $systemInfo['location'] ?? $this->oltDevice->location,
                'contact' => $systemInfo['contact'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to collect system info for OLT {$this->oltDevice->name}: " . $e->getMessage());
        }
    }

    /**
     * Collect performance metrics
     *
     * @param mixed $driver
     * @return void
     */
    protected function collectPerformanceMetrics($driver)
    {
        try {
            $metrics = $driver->getPerformanceMetrics($this->oltDevice);

            // Store performance log
            $this->oltDevice->performanceLogs()->create([
                'cpu_usage' => $metrics['cpu_usage'] ?? null,
                'memory_usage' => $metrics['memory_usage'] ?? null,
                'temperature' => $metrics['temperature'] ?? null,
                'recorded_at' => now(),
            ]);

            // Clean up old logs (keep last 7 days)
            $this->oltDevice->performanceLogs()
                ->where('recorded_at', '<', now()->subDays(7))
                ->delete();

        } catch (\Exception $e) {
            Log::error("Failed to collect performance metrics for OLT {$this->oltDevice->name}: " . $e->getMessage());
        }
    }

    /**
     * Collect cards information
     *
     * @param mixed $driver
     * @return void
     */
    protected function collectCardsInfo($driver)
    {
        try {
            $cards = $driver->getCards($this->oltDevice);

            foreach ($cards as $cardData) {
                $this->oltDevice->cards()->updateOrCreate(
                    ['slot_id' => $cardData['slot_id']],
                    [
                        'card_type' => $cardData['card_type'] ?? null,
                        'status' => $cardData['status'] ?? null,
                        'hw_version' => $cardData['hw_version'] ?? $cardData['version'] ?? null,
                        'sw_version' => $cardData['sw_version'] ?? null,
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to collect cards info for OLT {$this->oltDevice->name}: " . $e->getMessage());
        }
    }

    /**
     * Collect PON ports information
     *
     * @param mixed $driver
     * @return void
     */
    protected function collectPonPortsInfo($driver)
    {
        try {
            $ports = $driver->getPonPorts($this->oltDevice);

            foreach ($ports as $portData) {
                $this->oltDevice->ponPorts()->updateOrCreate(
                    ['port_index' => $portData['port_index']],
                    [
                        'status' => $portData['status'] ?? null,
                        'onu_count' => $portData['onu_count'] ?? 0,
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to collect PON ports info for OLT {$this->oltDevice->name}: " . $e->getMessage());
        }
    }

    /**
     * Collect ONUs information
     *
     * @param mixed $driver
     * @return void
     */
    protected function collectOnusInfo($driver)
    {
        try {
            $onus = $driver->getOnus($this->oltDevice);

            foreach ($onus as $onuData) {
                $this->oltDevice->onus()->updateOrCreate(
                    ['serial_number' => $onuData['serial_number']],
                    [
                        'onu_index' => $onuData['onu_index'] ?? null,
                        'status' => $onuData['status'] ?? null,
                        'rx_power' => $onuData['rx_power'] ?? null,
                        'tx_power' => $onuData['tx_power'] ?? null,
                        'distance' => $onuData['distance'] ?? null,
                        'mac_address' => $onuData['mac_address'] ?? null,
                        'last_seen' => now(),
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to collect ONUs info for OLT {$this->oltDevice->name}: " . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("PollOltJob failed for OLT {$this->oltDevice->name}: " . $exception->getMessage());
        
        $this->oltDevice->update([
            'status' => 'error',
            'last_poll' => now(),
        ]);
    }
}