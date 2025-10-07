<?php

namespace Botble\FiberHomeOLTManager\Console\Commands;

use Illuminate\Console\Command;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Services\OLTService;

class DiscoverONUCommand extends Command
{
    protected $signature = 'fiberhome:discover {--olt-id= : Specific OLT ID to discover}';
    
    protected $description = 'Discover ONUs connected to FiberHome OLT devices';
    
    protected $oltService;

    public function __construct(OLTService $oltService)
    {
        parent::__construct();
        $this->oltService = $oltService;
    }

    public function handle()
    {
        $this->info('Starting ONU discovery...');
        
        $oltId = $this->option('olt-id');
        
        if ($oltId) {
            $olt = OLT::find($oltId);
            if (!$olt) {
                $this->error("OLT with ID {$oltId} not found.");
                return 1;
            }
            $this->discoverONUs($olt);
        } else {
            $this->discoverAllONUs();
        }
        
        $this->info('ONU discovery completed.');
        return 0;
    }

    protected function discoverAllONUs()
    {
        $olts = OLT::where('status', 'online')->get();
        
        $this->info("Found {$olts->count()} online OLTs for discovery.");
        
        foreach ($olts as $olt) {
            try {
                $this->discoverONUs($olt);
            } catch (\Exception $e) {
                $this->error("Error discovering ONUs on OLT {$olt->name}: " . $e->getMessage());
            }
        }
    }

    protected function discoverONUs(OLT $olt)
    {
        $this->info("Discovering ONUs on OLT: {$olt->name}");
        
        try {
            $discovered = $this->oltService->discoverONUs($olt);
            $this->info("Discovered {$discovered} new ONUs on OLT {$olt->name}");
        } catch (\Exception $e) {
            $this->error("Failed to discover ONUs on OLT {$olt->name}: " . $e->getMessage());
        }
    }
}