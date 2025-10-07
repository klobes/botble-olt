<?php

namespace Botble\FiberHomeOLTManager\Console;

use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        \Botble\FiberHomeOLTManager\Console\Commands\PollOLTCommand::class,
        \Botble\FiberHomeOLTManager\Console\Commands\DiscoverONUCommand::class,
        \Botble\FiberHomeOLTManager\Console\Commands\ClearCacheCommand::class,
    ];

    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}