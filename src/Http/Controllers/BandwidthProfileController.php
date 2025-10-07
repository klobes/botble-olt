<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\FiberHomeOLTManager\Models\BandwidthProfile;
use Botble\FiberHomeOLTManager\Http\Requests\BandwidthProfileRequest;
use Botble\FiberHomeOLTManager\Services\BandwidthService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BandwidthProfileController extends BaseController
{
    protected $bandwidthService;

    public function __construct(BandwidthService $bandwidthService)
    {
        $this->bandwidthService = $bandwidthService;
    }

    public function index()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::bandwidth.title'));
        
        return view('plugins/fiberhome-olt-manager::bandwidth-profile.index');
    }

    public function datatable(Request $request)
    {
        $query = BandwidthProfile::query()->select([
            'id', 'name', 'download_speed', 'upload_speed', 
            'download_guaranteed', 'upload_guaranteed', 'priority', 'status', 'created_at'
        ]);

        return DataTables::of($query)
            ->addColumn('operations', function ($item) {
                $editBtn = '<a href="#" class="btn btn-icon btn-sm btn-primary" onclick="editBandwidthProfile(' . $item->id . ')" title="' . trans('core/base::forms.edit') . '"><i class="fa fa-edit"></i></a>';
                $assignBtn = '<a href="#" class="btn btn-icon btn-sm btn-info" onclick="assignBandwidthProfile(' . $item->id . ')" title="' . trans('plugins/fiberhome-olt-manager::bandwidth.assign') . '"><i class="fa fa-users"></i></a>';
                $deleteBtn = '<a href="#" class="btn btn-icon btn-sm btn-danger deleteDialog" data-section="' . route('fiberhome.bandwidth.destroy', $item->id) . '" title="' . trans('core/base::forms.delete') . '"><i class="fa fa-trash"></i></a>';
                
                return $editBtn . ' ' . $assignBtn . ' ' . $deleteBtn;
            })
            ->editColumn('download_speed', function ($item) {
                return $item->download_speed . ' Mbps';
            })
            ->editColumn('upload_speed', function ($item) {
                return $item->upload_speed . ' Mbps';
            })
            ->editColumn('download_guaranteed', function ($item) {
                return $item->download_guaranteed . '%';
            })
            ->editColumn('upload_guaranteed', function ($item) {
                return $item->upload_guaranteed . '%';
            })
            ->editColumn('priority', function ($item) {
                $priorityClass = '';
                $priorityText = trans('plugins/fiberhome-olt-manager::bandwidth.' . $item->priority);
                
                switch ($item->priority) {
                    case 'premium':
                        $priorityClass = 'badge-danger';
                        break;
                    case 'high':
                        $priorityClass = 'badge-warning';
                        break;
                    case 'medium':
                        $priorityClass = 'badge-info';
                        break;
                    case 'low':
                        $priorityClass = 'badge-secondary';
                        break;
                }
                
                return '<span class="badge ' . $priorityClass . '">' . $priorityText . '</span>';
            })
            ->editColumn('status', function ($item) {
                if ($item->status === 'active') {
                    return '<span class="badge badge-success">' . trans('plugins/fiberhome-olt-manager::bandwidth.active') . '</span>';
                } else {
                    return '<span class="badge badge-secondary">' . trans('plugins/fiberhome-olt-manager::bandwidth.inactive') . '</span>';
                }
            })
            ->editColumn('created_at', function ($item) {
                return $item->created_at->diffForHumans();
            })
            ->rawColumns(['priority', 'status', 'operations'])
            ->make(true);
    }

    public function create()
    {
        return view('plugins.fiberhome-olt-manager::bandwidth-profile.create');
    }

    public function store(BandwidthProfileRequest $request, BaseHttpResponse $response)
    {
        try {
            $profile = $this->bandwidthService->createProfile($request->validated());

            return $response
                ->setNextUrl(route('fiberhome.bandwidth.index'))
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.created_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.created_error') . ': ' . $e->getMessage());
        }
    }

    public function show($id, BaseHttpResponse $response)
    {
        $profile = BandwidthProfile::findOrFail($id);
        
        return $response->setData([
            'data' => $profile->toArray(),
            'assigned_onus' => $profile->assignedONUs()->count(),
        ]);
    }

    public function edit($id, BaseHttpResponse $response)
    {
        $profile = BandwidthProfile::findOrFail($id);
        
        return $response->setData($profile->toArray());
    }

    public function update($id, BandwidthProfileRequest $request, BaseHttpResponse $response)
    {
        try {
            $profile = BandwidthProfile::findOrFail($id);
            $this->bandwidthService->updateProfile($profile, $request->validated());

            return $response
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.updated_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.updated_error') . ': ' . $e->getMessage());
        }
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        try {
            $profile = BandwidthProfile::findOrFail($id);
            
            // Check if profile is in use
            if ($profile->assignedONUs()->count() > 0) {
                return $response
                    ->setError()
                    ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.cannot_delete_assigned'));
            }

            $this->bandwidthService->deleteProfile($profile);

            return $response->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.deleted_success'));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.deleted_error') . ': ' . $e->getMessage());
        }
    }

    public function assign($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $profile = BandwidthProfile::findOrFail($id);
            $onuIds = $request->input('onu_ids', []);
            $replaceExisting = $request->input('replace_existing', true);
            $schedule = $request->input('schedule', 'immediate');
            $customTime = $request->input('custom_time');

            $assignedCount = $this->bandwidthService->assignProfileToONUs(
                $profile, 
                $onuIds, 
                $replaceExisting, 
                $schedule, 
                $customTime
            );

            return $response
                ->setData(['assigned_count' => $assignedCount])
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.assigned_success', ['count' => $assignedCount]));
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/fiberhome-olt-manager::bandwidth.assigned_error') . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Get DataTable for Bandwidth Profiles
     */
    public function getTable(Request $request)
    {
        if ($request->ajax()) {
            $profiles = BandwidthProfile::select('bandwidth_profiles.*');
            
            return datatables()
                ->eloquent($profiles)
                ->addColumn('onu_count', function ($profile) {
                    return $profile->assignedONUs()->count();
                })
                ->addColumn('priority', function ($profile) {
                    $priorityClass = [
                        'low' => 'secondary',
                        'medium' => 'info',
                        'high' => 'warning',
                        'premium' => 'success'
                    ];
                    $class = $priorityClass[$profile->priority] ?? 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($profile->priority) . '</span>';
                })
                ->addColumn('actions', function ($profile) {
                    return view('plugins/fiberhome-olt-manager::bandwidth-profile.partials.actions', compact('profile'))->render();
                })
                ->rawColumns(['priority', 'actions'])
                ->make(true);
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }
}