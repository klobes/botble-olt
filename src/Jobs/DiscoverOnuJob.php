<?php

namespace Botble\FiberHomeOLTManager\Jobs;

use Botble\FiberHomeOLTManager\Models\OltDevice;
use Botble\FiberHomeOLTManager\Services\VendorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class DiscoverOnuJob
 * 
 * Background job for discovering new ONUs on OLT devices
 * Identifies unauthorized/new ONUs and adds them to the system
 */
class DiscoverOnuJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The OLT device to discover ONUs on
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
     * @param VendorService $vendorService
     * @return void
     */
    public function handle(VendorService $vendorService)
    {
        try {
            Log::info("Starting ONU discovery for OLT: {$this->oltDevice->name}");

            // Get vendor driver
            $driver = $vendorService->getDriver($this->oltDevice);

            // Validate connection
            if (!$driver->validateConnection($this->oltDevice)) {
                Log::warning("OLT {$this->oltDevice->name} is offline, skipping discovery");
                return;
            }

            // Discover new ONUs
            $newOnus = $driver->discoverOnus($this->oltDevice);

            if (empty($newOnus)) {
                Log::info("No new ONUs discovered on OLT: {$this->oltDevice->name}");
                return;
            }

            $discoveredCount = 0;

            foreach ($newOnus as $onuData) {
                // Check if ONU already exists
                $existingOnu = $this->oltDevice->onus()
                    ->where('serial_number', $onuData['serial_number'])
                    ->first();

                if ($existingOnu) {
                    // Update existing ONU
                    $existingOnu->update([
                        'status' => $onuData['status'] ?? 'discovered',
                        'onu_index' => $onuData['onu_index'] ?? null,
                        'rx_power' => $onuData['rx_power'] ?? null,
                        'tx_power' => $onuData['tx_power'] ?? null,
                        'distance' => $onuData['distance'] ?? null,
                        'mac_address' => $onuData['mac_address'] ?? null,
                        'last_seen' => now(),
                    ]);
                } else {
                    // Create new ONU
                    $this->oltDevice->onus()->create([
                        'serial_number' => $onuData['serial_number'],
                        'onu_index' => $onuData['onu_index'] ?? null,
                        'status' => $onuData['status'] ?? 'discovered',
                        'rx_power' => $onuData['rx_power'] ?? null,
                        'tx_power' => $onuData['tx_power'] ?? null,
                        'distance' => $onuData['distance'] ?? null,
                        'mac_address' => $onuData['mac_address'] ?? null,
                        'last_seen' => now(),
                    ]);

                    $discoveredCount++;
                }
            }

            Log::info("Discovered {$discoveredCount} new ONUs on OLT: {$this->oltDevice->name}");

            // Update OLT last discovery time
            $this->oltDevice->update([
                'last_discovery' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to discover ONUs on OLT {$this->oltDevice->name}: " . $e->getMessage());
            throw $e;
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
        Log::error("DiscoverOnuJob failed for OLT {$this->oltDevice->name}: " . $exception->getMessage());
    }
}