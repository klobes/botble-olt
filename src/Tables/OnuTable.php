<?php

namespace Botble\FiberHomeOLTManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\FiberHomeOLTManager\Models\Onu;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OnuTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(Onu::class);
        $this->addActions([
            EditAction::make()->route('fiberhome-olt.onus.edit'),
            DeleteAction::make()->route('fiberhome-olt.onus.destroy'),
        ]);

        $this->addBulkActions([
            DeleteBulkAction::make()->permission('fiberhome-olt.onus.destroy'),
        ]);

        $this->addColumns([
            IdColumn::make(),
            'onu_name' => [
                'title' => 'ONU Name',
                'class' => 'text-start',
            ],
            'mac_address' => [
                'title' => 'MAC Address',
                'class' => 'text-start',
            ],
            'olt_device' => [
                'title' => 'OLT Device',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->oltDevice ? $item->oltDevice->name : '-';
                },
            ],
            'status' => [
                'title' => 'Status',
                'class' => 'text-center',
                'render' => function ($item) {
                    return BaseHelper::renderLabel($item->status, [
                        'online' => 'success',
                        'offline' => 'secondary',
                        'los' => 'warning',
                        'dying_gasp' => 'danger',
                    ]);
                },
            ],
            'rx_power' => [
                'title' => 'RX Power',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->rx_optical_power ? $item->getRxOpticalPowerDbm() . ' dBm' : '-';
                },
            ],
            'last_online' => [
                'title' => 'Last Online',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->last_online ? BaseHelper::formatDateTime($item->last_online) : 'Never';
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
                'onu_name',
                'mac_address',
                'olt_device_id',
                'status',
                'rx_optical_power',
                'last_online',
                'created_at',
            ])
            ->with(['oltDevice']);
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('fiberhome-olt.onus.create'));
    }
}