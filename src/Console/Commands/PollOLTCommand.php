<?php

namespace Botble\FiberHomeOLTManager\Console\Commands;

use Illuminate\Console\Command;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Services\OLTService;

class PollOLTCommand extends Command
{
    protected $signature = 'fiberhome:poll {--olt-id= : Specific OLT ID to poll} {--force : Force polling even if recently polled}';
    
    protected $description = 'Poll FiberHome OLT devices for performance data';
    
    protected $oltService;

    public function __construct(OLTService $oltService)
    {
        parent::__construct();
        $this->oltService = $oltService;
    }

    public function handle()
    {
        $this->info('Starting OLT polling...');
        
        $oltId = $this->option('olt-id');
        $force = $this->option('force');
        
        if ($oltId) {
            $olt = OLT::find($oltId);
            if (!$olt) {
                $this->error("OLT with ID {$oltId} not found.");
                return 1;
            }
            $this->pollOLT($olt, $force);
        } else {
            $this->pollAllOLTs($force);
        }
        
        $this->info('OLT polling completed.');
        return 0;
    }

    protected function pollAllOLTs($force = false)
    {
        $query = OLT::where('status', 'online');
        
        if (!$force) {
            $pollingInterval = setting('fiberhome_polling_interval', 300);
            $query->where(function ($q) use ($pollingInterval) {
                $q->whereNull('last_polled')
                  ->orWhere('last_polled', '<', now()->subSeconds($pollingInterval));
            });
        }
        
        $olts = $query->get();
        
        $this->info("Found {$olts->count()} OLTs to poll.");
        
        $bar = $this->output->createProgressBar($olts->count());
        $bar->start();
        
        foreach ($olts as $olt) {
            try {
                $this->pollOLT($olt, true);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nError polling OLT {$olt->name}: " . $e->getMessage());
            }
        }
        
        $bar->finish();
        $this->line('');
    }

    protected function pollOLT(OLT $olt, $force = false)
    {
        try {
            $this->oltService->pollOLT($olt);
            $this->info("Successfully polled OLT: {$olt->name}");
        } catch (\Exception $e) {
            $this->error("Failed to poll OLT {$olt->name}: " . $e->getMessage());
            $olt->update(['status' => 'offline']);
        }
    }
}