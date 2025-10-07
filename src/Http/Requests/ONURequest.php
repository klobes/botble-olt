<?php

namespace Botble\FiberHomeOLTManager\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class ONURequest extends Request
{
    public function rules()
    {
        return [
            'serial_number' => 'required|string|max:255|unique:onus,serial_number,' . $this->route('id'),
            'olt_id' => 'required|exists:olts,id',
            'slot' => 'required|integer|min:1|max:20',
            'port' => 'required|integer|min:1|max:16',
            'onu_id' => 'required|integer|min:1|max:128',
            'customer_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'installation_address' => 'nullable|string|max:1000',
        ];
    }

    public function attributes()
    {
        return [
            'serial_number' => trans('plugins/fiberhome-olt-manager::onu.serial_number'),
            'olt_id' => trans('plugins/fiberhome-olt-manager::onu.olt'),
            'slot' => trans('plugins/fiberhome-olt-manager::onu.slot'),
            'port' => trans('plugins/fiberhome-olt-manager::onu.port'),
            'onu_id' => trans('plugins/fiberhome-olt-manager::onu.onu_id'),
            'customer_name' => trans('plugins/fiberhome-olt-manager::onu.customer_name'),
            'description' => trans('plugins/fiberhome-olt-manager::onu.description'),
            'installation_address' => trans('plugins/fiberhome-olt-manager::onu.installation_address'),
        ];
    }
}