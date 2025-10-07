<?php

namespace Botble\FiberHomeOLTManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BandwidthProfileTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(BandwidthProfile::class);
        $this->addActions([
            EditAction::make()->route('fiberhome-olt.bandwidth-profiles.edit'),
            DeleteAction::make()->route('fiberhome-olt.bandwidth-profiles.destroy'),
        ]);

        $this->addBulkActions([
            DeleteBulkAction::make()->permission('fiberhome-olt.bandwidth-profiles.destroy'),
        ]);

        $this->addColumns([
            IdColumn::make(),
            'profile_name' => [
                'title' => 'Profile Name',
                'class' => 'text-start',
            ],
            'olt_device' => [
                'title' => 'OLT Device',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->oltDevice ? $item->oltDevice->name : '-';
                },
            ],
            'up_max_rate' => [
                'title' => 'Upstream Max',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->getUpMaxRateMbps() . ' Mbps';
                },
            ],
            'down_max_rate' => [
                'title' => 'Downstream Max',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->getDownMaxRateMbps() . ' Mbps';
                },
            ],
            CreatedAtColumn::make(),
        ]);
    }

    public function query(): Builder
    {
        return $this->model->query()
            ->select([
                'id',
                'profile_name',
                'olt_device_id',
                'up_max_rate',
                'down_max_rate',
                'created_at',
            ])
            ->with(['oltDevice']);
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('fiberhome-olt.bandwidth-profiles.create'));
    }
}