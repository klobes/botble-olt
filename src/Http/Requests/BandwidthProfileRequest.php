<?php

namespace Botble\FiberHomeOLTManager\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class BandwidthProfileRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:bandwidth_profiles,name,' . $this->route('id'),
            'download_speed' => 'required|integer|min:1|max:10000',
            'upload_speed' => 'required|integer|min:1|max:10000',
            'download_guaranteed' => 'nullable|integer|min:10|max:100',
            'upload_guaranteed' => 'nullable|integer|min:10|max:100',
            'priority' => 'required|string|in:low,medium,high,premium',
            'status' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function attributes()
    {
        return [
            'name' => trans('plugins/fiberhome-olt-manager::bandwidth.name'),
            'download_speed' => trans('plugins/fiberhome-olt-manager::bandwidth.download_speed'),
            'upload_speed' => trans('plugins/fiberhome-olt-manager::bandwidth.upload_speed'),
            'download_guaranteed' => trans('plugins/fiberhome-olt-manager::bandwidth.download_guaranteed'),
            'upload_guaranteed' => trans('plugins/fiberhome-olt-manager::bandwidth.upload_guaranteed'),
            'priority' => trans('plugins/fiberhome-olt-manager::bandwidth.priority'),
            'status' => trans('plugins/fiberhome-olt-manager::bandwidth.status'),
            'description' => trans('plugins/fiberhome-olt-manager::bandwidth.description'),
        ];
    }
}