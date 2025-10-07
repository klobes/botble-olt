<?php

namespace Botble\FiberHomeOLTManager\PanelSections;

use Botble\Base\PanelSections\PanelSection;
use Botble\Base\PanelSections\PanelSectionItem;

class OltManagerPanelSetting extends PanelSection
{
	
    public function setup(): void
    {
        $this
            ->setId('olt-manager')
            ->setTitle(trans('plugins/fiberhome-olt-manager::dashboard.title'))
            ->withPriority(5)
            ->addItems([
                PanelSectionItem::make('fiberhome-olt-manager')
                    ->setTitle(trans('plugins/fiberhome-olt-manager::dashboard.title'))
                    ->withDescription(trans('plugins/fiberhome-olt-manager::dashboard.description'))
                    ->withIcon('fa fa-server')
                    ->withPriority(5)
                    ->withRoute('fiberhome.dashboard')
                    ->withPermission('fiberhome-olt-manager.index'),
            ]);
    }
}
