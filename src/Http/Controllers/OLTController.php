<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Http\Requests\OLTRequest;
use Botble\FiberHomeOLTManager\Services\OLTService;
use Botble\FiberHomeOLTManager\Services\SNMPService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Botble\Base\Facades\Assets;


class OLTController extends BaseController
{
    protected $oltService;
    protected $snmpService;

    public function __construct(OLTService $oltService, SNMPService $snmpService)
    {
        $this->oltService = $oltService;
        $this->snmpService = $snmpService;
    }

    public function index()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::olt.title'));
		Assets::addScripts(['datatables'])
        ->addScriptsDirectly(['vendor/core/plugins/fiberhome-olt-manager/js/olt/view.js']);
		$olts = OLT::orderBy('created_at', 'desc')->paginate(20);
		return view('plugins/fiberhome-olt-manager::olt.index', ['olts' => $olts]);
    }

    public function datatable(Request $request)
    {
        $query = OLT::query()->select([
            'id', 'name', 'ip_address', 'model', 'status', 
            'cpu_usage', 'memory_usage', 'temperature', 'last_polled'
        ]);

        return DataTables::of($query)
            ->addColumn('operations', function ($item) {
                $editBtn = '<a href="#" class="btn btn-icon btn-sm btn-primary" onclick="editOLT(' . $item->id . ')" title="' . trans('core/base::forms.edit') . '"><i class="fa fa-edit"></i></a>';
                $viewBtn = '<a href="#" class="btn btn-icon btn-sm btn-info" onclick="viewOLTDetails(' . $item->id . ')" title="' . trans('core/base::forms.view') . '"><i class="fa fa-eye"></i></a>';
                $deleteBtn = '<a href="#" class="btn btn-icon btn-sm btn-danger deleteDialog" data-section="' . route('fiberhome.olt.destroy', $item->id) . '" title="' . trans('core/base::forms.delete') . '"><i class="fa fa-trash"></i></a>';
                
                return $editBtn . ' ' . $viewBtn . ' ' . $deleteBtn;
            })
            ->editColumn('status', function ($item) {
                if ($item->status === 'online') {
                    return '<span class="badge badge-success">' . trans('plugins/fiberhome-olt-manager::olt.online') . '</span>';
                } else {
                    return '<span class="badge badge-danger">' . trans('plugins/fiberhome-olt-manager::olt.offline') . '</span>';
                }
            })
            ->editColumn('cpu_usage', function ($item) {
                return $item->cpu_usage . '%';
            })
            ->editColumn('memory_usage', function ($item) {
                return $item->memory_usage . '%';
            })
            ->editColumn('temperature', function ($item) {
                return $item->temperature . '°C';
            })
            ->editColumn('last_polled', function ($item) {
                return $item->last_polled ? $item->last_polled->diffForHumans() : trans('plugins/fiberhome-olt-manager::olt.never');
            })
            ->rawColumns(['status', 'operations'])
            ->make(true);
    }

    public function create()
    {
        return view('plugins.fiberhome-olt-manager::olt.create');
    }

    public function store(OLTRequest $request, BaseHttpResponse $response)
    {
        try {
            $olt = $this->oltService->createOLT($request->validated());
            
            // Test connection
            if ($this->snmpService->testConnection($olt->ip_address, $olt->snmp_community)) {
                $olt->update(['status' => 'online']);
                
                // Initial poll
                $this->oltService->pollOLT($olt);
            } else {
                $olt->update(['status' => 'offline']);
            }

            return $response
                ->setNextUrl(route('fiberhome.olt.index'))
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.created_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.created_error') . ': ' . $e->getMessage());
        }
    }

    public function show($id, BaseHttpResponse $response)
    {
        $olt = OLT::findOrFail($id);
        
        return $response->setData([
            'data' => $olt->toArray(),
            'ports' => $olt->ports()->count(),
            'onus' => $olt->onus()->count(),
        ]);
    }

    public function edit($id, BaseHttpResponse $response)
    {
        $olt = OLT::findOrFail($id);
        
        return $response->setData($olt->toArray());
    }

    public function update($id, OLTRequest $request, BaseHttpResponse $response)
    {
        try {
            $olt = OLT::findOrFail($id);
            $this->oltService->updateOLT($olt, $request->validated());

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.updated_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.updated_error') . ': ' . $e->getMessage());
        }
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        try {
            $olt = OLT::findOrFail($id);
            $this->oltService->deleteOLT($olt);

            return $response->setMessage(trans('plugins/fiberhome-olt-manager::olt.deleted_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.deleted_error') . ': ' . $e->getMessage());
        }
    }

    public function ports($id, BaseHttpResponse $response)
    {
        $olt = OLT::findOrFail($id);
        $ports = $olt->ports()->withCount('onus')->get();

        return $response->setData($ports);
    }

    public function poll($id, BaseHttpResponse $response)
    {
        try {
            $olt = OLT::findOrFail($id);
            $this->oltService->pollOLT($olt);

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.polled_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.polled_error') . ': ' . $e->getMessage());
        }
    }

    public function discover($id, BaseHttpResponse $response)
    {
        try {
            $olt = OLT::findOrFail($id);
            $discovered = $this->oltService->discoverONUs($olt);

            return $response
                ->setData(['discovered' => $discovered])
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.discovered_success', ['count' => $discovered]));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::olt.discovered_error') . ': ' . $e->getMessage());
        }
    }
}