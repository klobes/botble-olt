<?php

namespace Botble\FiberHomeOLTManager\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class OLTRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:olts,ip_address,' . $this->route('id'),
            'model' => 'required|string|in:AN5516-01,AN5516-02,AN5516-04,AN5516-06,AN5516-10',
            'snmp_community' => 'required|string|max:255',
            'snmp_version' => 'required|string|in:2c,3',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function attributes()
    {
        return [
            'name' => trans('plugins/fiberhome-olt-manager::olt.name'),
            'ip_address' => trans('plugins/fiberhome-olt-manager::olt.ip_address'),
            'model' => trans('plugins/fiberhome-olt-manager::olt.model'),
            'snmp_community' => trans('plugins/fiberhome-olt-manager::olt.snmp_community'),
            'snmp_version' => trans('plugins/fiberhome-olt-manager::olt.snmp_version'),
            'description' => trans('plugins/fiberhome-olt-manager::olt.description'),
        ];
    }
}