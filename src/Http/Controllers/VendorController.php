<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\FiberHomeOLTManager\Models\VendorConfiguration;
use Botble\FiberHomeOLTManager\Models\OnuType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends BaseController
{
	/**
     * Get all vendors
     */
    public function getVendors()
    {
        $vendors = config('olt-vendors.vendors', []);
        
        $result = [];
        foreach ($vendors as $key => $vendor) {
            $result[] = [
                'value' => $key,
                'text' => $vendor['name'],
                'models_count' => count($vendor['models'] ?? []),
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
    
    /**
     * Get models for a specific vendor
     */
    public function getModels(Request $request, $vendor)
    {
        $vendors = config('olt-vendors.vendors', []);
        
        if (!isset($vendors[$vendor])) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }
        
        $models = $vendors[$vendor]['models'] ?? [];
        
        $result = [];
        foreach ($models as $key => $model) {
            $result[] = [
                'value' => $key,
                'text' => $model['name'],
                'description' => $model['description'] ?? '',
                'max_ports' => $model['max_ports'] ?? null,
                'max_onus' => $model['max_onus'] ?? null,
                'technology' => $model['technology'] ?? [],
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
    
    /**
     * Get model details
     */
    public function getModelDetails(Request $request, $vendor, $model)
    {
        $vendors = config('olt-vendors.vendors', []);
        
        if (!isset($vendors[$vendor])) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }
        
        if (!isset($vendors[$vendor]['models'][$model])) {
            return response()->json([
                'success' => false,
                'message' => 'Model not found',
            ], 404);
        }
        
        $modelData = $vendors[$vendor]['models'][$model];
        
        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => $vendor,
                'vendor_name' => $vendors[$vendor]['name'],
                'model' => $model,
                'name' => $modelData['name'],
                'description' => $modelData['description'] ?? '',
                'max_ports' => $modelData['max_ports'] ?? null,
                'max_onus' => $modelData['max_onus'] ?? null,
                'technology' => $modelData['technology'] ?? [],
            ],
        ]);
    }
    public function configurations(Request $request)
    {
        page_title()->setTitle('Vendor Configurations');

        $configurations = VendorConfiguration::orderBy('vendor')->orderBy('model')->get();

        return view('plugins/fiberhome-olt-manager::vendor.configurations', compact('configurations'));
    }

    public function createConfiguration(Request $request)
    {
        page_title()->setTitle('Add Vendor Configuration');

        $vendors = ['fiberhome', 'huawei', 'zte', 'other'];

        return view('plugins/fiberhome-olt-manager::vendor.create-configuration', compact('vendors'));
    }

    public function storeConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor' => 'required|in:fiberhome,huawei,zte,other',
            'model' => 'required|string|max:255',
            'oid_mappings' => 'required|array',
            'capabilities' => 'required|array',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        VendorConfiguration::create([
            'vendor' => $request->vendor,
            'model' => $request->model,
            'oid_mappings' => $request->oid_mappings,
            'capabilities' => $request->capabilities,
            'default_settings' => $request->default_settings ?? null,
            'notes' => $request->notes ?? null,
        ]);

        return redirect()
            ->route('fiberhome-olt.vendor.configurations')
            ->with('success', 'Vendor configuration created successfully');
    }

    public function onuTypes(Request $request)
    {
        page_title()->setTitle('ONU Types');

        $onuTypes = OnuType::with('vendorConfiguration')
            ->orderBy('vendor')
            ->orderBy('model')
            ->get();

        return view('plugins/fiberhome-olt-manager::vendor.onu-types', compact('onuTypes'));
    }

    public function createOnuType(Request $request)
    {
        page_title()->setTitle('Add ONU Type');

        $vendors = ['fiberhome', 'huawei', 'zte', 'other'];
        $configurations = VendorConfiguration::orderBy('vendor')->orderBy('model')->get();

        return view('plugins/fiberhome-olt-manager::vendor.create-onu-type', compact('vendors', 'configurations'));
    }

    public function storeOnuType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor' => 'required|in:fiberhome,huawei,zte,other',
            'model' => 'required|string|max:255',
            'type_name' => 'required|string|max:255',
            'ethernet_ports' => 'required|integer|min:0',
            'pots_ports' => 'required|integer|min:0',
            'catv_ports' => 'required|integer|min:0',
            'wifi_support' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        OnuType::create([
            'vendor' => $request->vendor,
            'model' => $request->model,
            'type_name' => $request->type_name,
            'ethernet_ports' => $request->ethernet_ports,
            'pots_ports' => $request->pots_ports,
            'catv_ports' => $request->catv_ports,
            'wifi_support' => $request->wifi_support ?? false,
            'capabilities' => $request->capabilities ?? null,
            'default_config' => $request->default_config ?? null,
            'description' => $request->description ?? null,
        ]);

        return redirect()
            ->route('fiberhome-olt.vendor.onu-types')
            ->with('success', 'ONU type created successfully');
    }
}