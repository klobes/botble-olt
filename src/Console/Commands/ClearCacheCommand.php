<?php

namespace Botble\FiberHomeOLTManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'fiberhome:clear-cache';
    
    protected $description = 'Clear FiberHome OLT Manager cache';
    
    public function handle()
    {
        $this->info('Clearing FiberHome OLT Manager cache...');
        
        // Clear specific cache keys
        Cache::forget('fiberhome_olt_status');
        Cache::forget('fiberhome_onu_data');
        Cache::forget('fiberhome_performance_metrics');
        Cache::forget('fiberhome_bandwidth_profiles');
        
        // Clear pattern-based cache
        Cache::flush();
        
        $this->info('Cache cleared successfully.');
        return 0;
    }
}