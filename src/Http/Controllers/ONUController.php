<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\FiberHomeOLTManager\Models\Onu;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Http\Requests\ONURequest;
use Botble\FiberHomeOLTManager\Services\ONUService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
class ONUController extends BaseController
{
    protected $onuService;

    public function __construct(ONUService $onuService)
    {
        $this->onuService = $onuService;
    }

    public function index()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::onu.title'));
        
        $olts = OLT::where('status', 'online')->get();
        $bandwidthProfiles = BandwidthProfile::where('status', 'active')->get();
        
        return view('plugins/fiberhome-olt-manager::onu.index', compact('olts', 'bandwidthProfiles'));
    }

    public function datatable(Request $request)
    {
        $query = Onu::query()
            ->select([
                'onus.id', 'onus.serial_number', 'onus.olt_id', 'onus.slot', 'onus.port', 
                'onus.onu_id', 'onus.status', 'onus.rx_power', 'onus.tx_power', 
                'onus.distance', 'onus.last_seen', 'onus.customer_name'
            ])
            ->leftJoin('olts', 'onus.olt_id', '=', 'olts.id')
            ->addSelect('olts.name as olt_name');

        return DataTables::of($query)
            ->addColumn('operations', function ($item) {
                $viewBtn = '<a href="#" class="btn btn-icon btn-sm btn-info" onclick="viewONUDetails(' . $item->id . ')" title="' . trans('core/base::forms.view') . '"><i class="fa fa-eye"></i></a>';
                $editBtn = '<a href="#" class="btn btn-icon btn-sm btn-primary" onclick="editONU(' . $item->id . ')" title="' . trans('core/base::forms.edit') . '"><i class="fa fa-edit"></i></a>';
                $configBtn = '<a href="#" class="btn btn-icon btn-sm btn-warning" onclick="configureONU(' . $item->id . ')" title="' . trans('plugins/fiberhome-olt-manager::onu.configure') . '"><i class="fa fa-cog"></i></a>';
                $rebootBtn = '<a href="#" class="btn btn-icon btn-sm btn-dark" onclick="rebootONU(' . $item->id . ')" title="' . trans('plugins/fiberhome-olt-manager::onu.reboot') . '"><i class="fa fa-refresh"></i></a>';
                
                return $viewBtn . ' ' . $editBtn . ' ' . $configBtn . ' ' . $rebootBtn;
            })
            ->editColumn('status', function ($item) {
                switch ($item->status) {
                    case 'online':
                        return '<span class="badge badge-success">' . trans('plugins/fiberhome-olt-manager::onu.online') . '</span>';
                    case 'offline':
                        return '<span class="badge badge-danger">' . trans('plugins/fiberhome-olt-manager::onu.offline') . '</span>';
                    case 'dying_gasp':
                        return '<span class="badge badge-warning">' . trans('plugins/fiberhome-olt-manager::onu.dying_gasp') . '</span>';
                    default:
                        return '<span class="badge badge-secondary">' . trans('plugins/fiberhome-olt-manager::onu.unknown') . '</span>';
                }
            })
            ->editColumn('rx_power', function ($item) {
                return $item->rx_power ? $item->rx_power . ' dBm' : 'N/A';
            })
            ->editColumn('tx_power', function ($item) {
                return $item->tx_power ? $item->tx_power . ' dBm' : 'N/A';
            })
            ->editColumn('distance', function ($item) {
                return $item->distance ? $item->distance . ' m' : 'N/A';
            })
            ->editColumn('last_seen', function ($item) {
                return $item->last_seen ? $item->last_seen->diffForHumans() : trans('plugins/fiberhome-olt-manager::onu.never');
            })
            ->editColumn('customer_name', function ($item) {
                return $item->customer_name ?: trans('plugins/fiberhome-olt-manager::onu.not_assigned');
            })
            ->rawColumns(['status', 'operations'])
            ->make(true);
    }

    public function available(Request $request)
    {
        $onus = Onu::select(['id', 'serial_number', 'olt_id', 'slot', 'port', 'customer_name'])
            ->with(['olt' => function ($query) {
                $query->select(['id', 'name']);
            }])
            ->orderBy('serial_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $onus
        ]);
    }

    public function show($id, BaseHttpResponse $response)
    {
        $onu = Onu::with(['olt', 'bandwidthProfile'])->findOrFail($id);
        
        return $response->setData([
            'data' => $onu->toArray(),
            'configuration' => $onu->configuration,
        ]);
    }

    public function edit($id, BaseHttpResponse $response)
    {
        $onu = Onu::findOrFail($id);
        $olts = OLT::where('status', 'online')->get();
        
        return $response->setData(array_merge(
            $onu->toArray(),
            ['olts' => $olts]
        ));
    }

    public function update($id, ONURequest $request, BaseHttpResponse $response)
    {
        try {
            $onu = Onu::findOrFail($id);
            $this->onuService->updateONU($onu, $request->validated());

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.updated_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.updated_error') . ': ' . $e->getMessage());
        }
    }

    public function configuration($id, BaseHttpResponse $response)
    {
        $onu = Onu::findOrFail($id);
        $configuration = $onu->configuration;

        return $response->setData($configuration ?: []);
    }

    public function configure($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $onu = Onu::findOrFail($id);
            
            $configuration = [
                'bandwidth_profile_id' => $request->input('bandwidth_profile_id'),
                'vlan' => $request->input('vlan'),
                'service_type' => $request->input('service_type'),
                'multicast_vlan' => $request->input('multicast_vlan'),
                'igmp_snooping' => $request->has('igmp_snooping'),
                'dhcp_snooping' => $request->has('dhcp_snooping'),
                'description' => $request->input('description'),
            ];

            $this->onuService->configureONU($onu, $configuration);

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.configured_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.configured_error') . ': ' . $e->getMessage());
        }
    }

    public function reboot($id, BaseHttpResponse $response)
    {
        try {
            $onu = Onu::findOrFail($id);
            $this->onuService->rebootONU($onu);

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.rebooted_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.rebooted_error') . ': ' . $e->getMessage());
        }
    }

    public function performance($id, Request $request, BaseHttpResponse $response)
    {
        $onu = Onu::findOrFail($id);
        
        $days = $request->input('days', 7);
        $performance = $this->onuService->getPerformanceHistory($onu, $days);

        return $response->setData($performance);
    }

    public function bandwidth($id, BaseHttpResponse $response)
    {
        $onu = Onu::findOrFail($id);
        $bandwidth = $onu->bandwidthProfile;

        return $response->setData($bandwidth ?: []);
    }
    
    /**
     * Get DataTable for ONUs
     */
    public function getTable(Request $request)
    {
        if ($request->ajax()) {
            $onus = Onu::with(['oltDevice'])->select('onus.*');
            
            return datatables()
                ->eloquent($onus)
                ->addColumn('olt_name', function ($onu) {
                    return $onu->oltDevice ? $onu->oltDevice->name : 'N/A';
                })
                ->addColumn('pon_port', function ($onu) {
                    return $onu->slot . '/' . $onu->port . ':' . $onu->onu_id;
                })
                ->addColumn('status', function ($onu) {
                    $statusClass = [
                        'online' => 'success',
                        'offline' => 'danger',
                        'los' => 'warning',
                        'dying_gasp' => 'danger'
                    ];
                    $class = $statusClass[$onu->status] ?? 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($onu->status) . '</span>';
                })
                ->addColumn('actions', function ($onu) {
                    return view('plugins/fiberhome-olt-manager::onu.partials.actions', compact('onu'))->render();
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }
    
    /**
     * Enable ONU
     */
    public function enable($id, BaseHttpResponse $response)
    {
        try {
            $onu = Onu::findOrFail($id);
            $this->onuService->enableONU($onu);

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.enabled_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.enabled_error') . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Disable ONU
     */
    public function disable($id, BaseHttpResponse $response)
    {
        try {
            $onu = Onu::findOrFail($id);
            $this->onuService->disableONU($onu);

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.disabled_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::onu.disabled_error') . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Get available ONUs (not configured)
     */
    public function availableNotConfiged(BaseHttpResponse $response)
    {
        $onus = Onu::whereNull('customer_name')
            ->orWhere('customer_name', '')
            ->get();
            
        return $response->setData($onus);
    }
}