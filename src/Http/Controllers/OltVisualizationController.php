<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Services\OltVisualizationService;
use Illuminate\Http\Request;

class OltVisualizationController extends BaseController
{
    protected $visualizationService;

    public function __construct(OltVisualizationService $visualizationService)
    {
        $this->visualizationService = $visualizationService;
    }

    /**
     * Show OLT visualization page
     */
    public function show($id)
    {
        $olt = OLT::with(['cards', 'ponPorts'])->findOrFail($id);
        
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::olt.visualization_title', ['name' => $olt->name]));
        
        return view('plugins/fiberhome-olt-manager::olt.visualization', compact('olt'));
    }

    /**
     * Get OLT structure data via AJAX
     */
    public function getStructure($id, BaseHttpResponse $response)
    {
        try {
            $olt = OLT::with(['cards', 'ponPorts'])->findOrFail($id);
            $structure = $this->visualizationService->getOltStructure($olt);
            
            return $response->setData($structure);
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to get OLT structure: ' . $e->getMessage());
        }
    }

    /**
     * Preview OLT structure before creation (based on model selection)
     */
    public function preview(Request $request, BaseHttpResponse $response)
    {
        try {
            $model = $request->input('model');
            $vendor = $request->input('vendor', 'fiberhome');
            
            // Create temporary OLT object for preview
            $tempOlt = new OLT([
                'model' => $model,
                'vendor' => $vendor,
                'name' => 'Preview',
            ]);
            
            $structure = $this->visualizationService->getOltStructure($tempOlt);
            
            return $response->setData($structure);
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to generate preview: ' . $e->getMessage());
        }
    }

    /**
     * Get port details
     */
    public function getPortDetails($oltId, $portId, BaseHttpResponse $response)
    {
        try {
            $olt = OLT::findOrFail($oltId);
            $port = $olt->ponPorts()->findOrFail($portId);
            
            $details = [
                'port' => $port->toArray(),
                'onus' => $port->onus()->with('bandwidthProfile')->get()->toArray(),
                'statistics' => [
                    'total_onus' => $port->onus()->count(),
                    'online_onus' => $port->onus()->where('status', 'online')->count(),
                    'offline_onus' => $port->onus()->where('status', 'offline')->count(),
                    'los_onus' => $port->onus()->where('status', 'los')->count(),
                ],
            ];
            
            return $response->setData($details);
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to get port details: ' . $e->getMessage());
        }
    }
}