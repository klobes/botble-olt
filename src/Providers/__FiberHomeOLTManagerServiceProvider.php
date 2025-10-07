<?php

namespace Botble\FiberHomeOLTManager\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Models\ONU;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Botble\FiberHomeOLTManager\Repositories\Eloquent\OLTRepository;
use Botble\FiberHomeOLTManager\Repositories\Eloquent\ONURepository;
use Botble\FiberHomeOLTManager\Repositories\Eloquent\BandwidthProfileRepository;
use Botble\FiberHomeOLTManager\Repositories\Interfaces\OLTInterface;
use Botble\FiberHomeOLTManager\Repositories\Interfaces\ONUInterface;
use Botble\FiberHomeOLTManager\Repositories\Interfaces\BandwidthProfileInterface;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Botble\FiberHomeOLTManager\Console\ConsoleServiceProvider;

class FiberHomeOLTManagerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(OLTInterface::class, function () {
            return new OLTRepository(new OLT());
        });

        $this->app->bind(ONUInterface::class, function () {
            return new ONURepository(new ONU());
        });

        $this->app->bind(BandwidthProfileInterface::class, function () {
            return new BandwidthProfileRepository(new BandwidthProfile());
        });
    }

    public function boot()
    {
        $this
            ->setNamespace('plugins/fiberhome-olt-manager')
            ->loadAndPublishConfigurations(['permissions','fiberhome-olt'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'admin', 'api'])
            ->publishAssets();

       // $this->app->register(EventServiceProvider::class);
       // $this->app->register(ConsoleServiceProvider::class);

        Event::listen(RouteMatched::class, function () {
            $this->registerMenuItems();
            $this->registerPanelSections();
        });
    }

    protected function registerMenuItems()
    {
        if (!setting('fiberhome_enabled', true)) {
            return;
        }

        DashboardMenu::beforeRetrieving(function (): void {
        DashboardMenu::make()
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-olt-manager',
                'priority' => 5,
                'parent_id' => null,
                'name' => 'plugins/fiberhome-olt-manager::menu.title',
                'icon' => 'fa fa-server',
                'url' => route('fiberhome.dashboard'),
                'permissions' => ['fiberhome-olt-manager.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-dashboard',
                'priority' => 1,
                'parent_id' => 'cms-plugins-fiberhome-olt-manager',
                'name' => 'plugins/fiberhome-olt-manager::menu.dashboard',
                'icon' => 'fa fa-dashboard',
                'url' => route('fiberhome.dashboard'),
                'permissions' => ['fiberhome-olt-manager.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-olt',
                'priority' => 2,
                'parent_id' => 'cms-plugins-fiberhome-olt-manager',
                'name' => 'plugins/fiberhome-olt-manager::menu.olt_management',
                'icon' => 'fa fa-server',
                'url' => route('fiberhome.olt.index'),
                'permissions' => ['fiberhome-olt-manager.olt.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-onu',
                'priority' => 3,
                'parent_id' => 'cms-plugins-fiberhome-olt-manager',
                'name' => 'plugins/fiberhome-olt-manager::menu.onu_management',
                'icon' => 'fa fa-wifi',
                'url' => route('fiberhome.onu.index'),
                'permissions' => ['fiberhome-olt-manager.onu.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-bandwidth',
                'priority' => 4,
                'parent_id' => 'cms-plugins-fiberhome-olt-manager',
                'name' => 'plugins/fiberhome-olt-manager::menu.bandwidth_profiles',
                'icon' => 'fa fa-tachometer',
                'url' => route('fiberhome.bandwidth.index'),
                'permissions' => ['fiberhome-olt-manager.bandwidth.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-topology',
                'priority' => 5,
                'parent_id' => 'cms-plugins-fiberhome-olt-manager',
                'name' => 'plugins/fiberhome-olt-manager::menu.network_topology',
                'icon' => 'fa fa-sitemap',
                'url' => route('fiberhome.topology.index'),
                'permissions' => ['fiberhome-olt-manager.topology.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-fiberhome-settings',
                'priority' => 6,
                'parent_id' => 'cms-plugins-fiberhome-olt-manager',
                'name' => 'plugins/fiberhome-olt-manager::menu.settings',
                'icon' => 'fa fa-cog',
                'url' => route('fiberhome.settings.index'),
                'permissions' => ['fiberhome-olt-manager.settings.index'],
            ]);
		});	
    }

    protected function registerPanelSections()
    {
		PanelSectionManager::beforeRendering(function (): void {
            
       
        PanelSectionManager::default()
            ->registerItem(
                PanelSectionItem::make('fiberhome-olt-manager')
                    ->setTitle(trans('plugins/fiberhome-olt-manager::dashboard.title'))
                    ->withDescription(trans('plugins/fiberhome-olt-manager::dashboard.description'))
                    ->withIcon('fa fa-server')
                    ->withPriority(5)
                    ->withRoute('fiberhome.dashboard')
                    ->withPermission('fiberhome-olt-manager.index')
            );
		 });
    }
}