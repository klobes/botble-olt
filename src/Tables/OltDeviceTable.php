<?php

namespace Botble\FiberHomeOLTManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\FiberHomeOLTManager\Models\OltDevice;
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

class OltDeviceTable extends TableAbstract
{
    public function setup(): void
    {
        $this->model(OltDevice::class);
        $this->addActions([
            EditAction::make()->route('fiberhome-olt.devices.edit'),
            DeleteAction::make()->route('fiberhome-olt.devices.destroy'),
        ]);

        $this->addBulkActions([
            DeleteBulkAction::make()->permission('fiberhome-olt.devices.destroy'),
        ]);

        $this->addColumns([
            IdColumn::make(),
            NameColumn::make(),
            'ip_address' => [
                'title' => 'IP Address',
                'class' => 'text-start',
            ],
            'vendor' => [
                'title' => 'Vendor',
                'class' => 'text-start',
            ],
            'model' => [
                'title' => 'Model',
                'class' => 'text-start',
            ],
            'status' => [
                'title' => 'Status',
                'class' => 'text-center',
                'render' => function ($item) {
                    return BaseHelper::renderLabel($item->status, [
                        'online' => 'success',
                        'offline' => 'secondary',
                        'error' => 'danger',
                    ]);
                },
            ],
            'last_seen' => [
                'title' => 'Last Seen',
                'class' => 'text-start',
                'render' => function ($item) {
                    return $item->last_seen ? BaseHelper::formatDateTime($item->last_seen) : 'Never';
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
                'name',
                'ip_address',
                'vendor',
                'model',
                'status',
                'last_seen',
                'created_at',
            ]);
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('fiberhome-olt.devices.create'));
    }
}