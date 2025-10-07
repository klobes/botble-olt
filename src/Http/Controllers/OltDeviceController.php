<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\FiberHomeOLTManager\Models\OLT;
use Botble\FiberHomeOLTManager\Services\SnmpManager;
use Botble\FiberHomeOLTManager\Services\OltDataCollector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Botble\FiberHomeOLTManager\Tables\OltDeviceTable;

class OltDeviceController extends BaseController
{
    protected SnmpManager $snmp;
    protected OltDataCollector $collector;

    public function __construct(SnmpManager $snmp, OltDataCollector $collector)
    {
        $this->snmp = $snmp;
        $this->collector = $collector;
    }

    public function index(Request $request)
    {
        page_title()->setTitle('OLT Devices');

        $olts = OLT::orderBy('created_at', 'desc')->get();

        return view('plugins/fiberhome-olt-manager::olt.index', compact('olts'));
    }

    public function create()
    {
        page_title()->setTitle('Add OLT Device');

        return view('plugins/fiberhome-olt-manager::olt.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:om_olts',
            'vendor' => 'required|string|in:fiberhome,huawei,zte',
            'model' => 'required|string|max:255',
            'snmp_community' => 'required|string|max:255',
            'snmp_version' => 'required|in:1,2c,3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
			//'status' => 'required|string|in:online,offline,error,maintenance',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create device
            $device = OLT::create(array_merge($request->all(), [
                'snmp_port' => $request->snmp_port ?? 161,
                'status' => 'pending'
            ]));

            // Test connection
            if ($this->snmp->testConnection($device)) {
                $device->update(['status' => 'online']);
                
                // Collect initial data in background
                try {
                    $this->collector->collectAll($device);
                } catch (\Exception $e) {
                    \Log::warning("Failed to collect initial data for OLT {$device->name}: " . $e->getMessage());
                }
                
                $message = 'OLT device added successfully and is online';
            } else {
                $device->update(['status' => 'offline']);
                $message = 'OLT device added but connection failed';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $device
                ]);
            }

            return redirect()
                ->route('fiberhome.olt.show', $device->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add OLT device: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to add OLT device: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $device = OLT::with(['cards', 'ponPorts', 'onus'])->findOrFail($id);
        
        page_title()->setTitle($device->name);

        // Get performance data for last 24 hours
        $performanceData = $device->performanceLogs()
            ->where('recorded_at', '>=', now()->subDay())
            ->orderBy('recorded_at')
            ->get();

        return view('plugins/fiberhome-olt-manager::olt.show', compact('device', 'performanceData'));
    }

    public function edit($id)
    {
        $device = OLT::findOrFail($id);
        
        page_title()->setTitle('Edit ' . $device->name);

        return view('plugins/fiberhome-olt-manager::olt.edit', compact('device'));
    }

    public function update(Request $request, $id)
    {
        $device = OLT::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:om_olts,ip_address,' . $id,
            'snmp_community' => 'required|string|max:255',
            'snmp_version' => 'required|in:1,2c,3',
            'snmp_port' => 'required|integer|min:1|max:65535',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $device->update($request->all());

        return redirect()
            ->route('fiberhome-olt.devices.show', $device->id)
            ->with('success', 'OLT device updated successfully');
    }

    public function destroy($id)
    {
        $device = OLT::findOrFail($id);
        $device->delete();

        return redirect()
            ->route('fiberhome-olt.devices.index')
            ->with('success', 'OLT device deleted successfully');
    }

    public function sync(Request $request, $id)
    {
        $device = OLT::findOrFail($id);

        if ($this->collector->collectAll($device)) {
            return response()->json([
                'success' => true,
                'message' => 'Data synchronized successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to synchronize data',
        ], 500);
    }

    public function testConnection(Request $request, $id = null)
    {
        // If ID is provided, test existing device
        if ($id) {
            $device = OLT::findOrFail($id);
            
            if ($this->snmp->testConnection($device)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed',
            ], 500);
        }
        
        // Test connection with provided parameters (for new device)
        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip',
            'snmp_community' => 'required|string',
            'snmp_version' => 'required|in:1,2c,3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create temporary device object for testing
        $tempDevice = new OLT([
            'ip_address' => $request->ip_address,
            'snmp_community' => $request->snmp_community,
            'snmp_version' => $request->snmp_version,
            'snmp_port' => $request->snmp_port ?? 161,
        ]);

        try {
            if ($this->snmp->testConnection($tempDevice)) {
                // Try to get system description
                $systemInfo = $this->snmp->getSystemInfo($tempDevice);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => [
                        'system_description' => $systemInfo['sysDescr'] ?? null,
                        'system_name' => $systemInfo['sysName'] ?? null,
                        'uptime' => $systemInfo['sysUpTime'] ?? null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed - Unable to communicate with device',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get DataTable for OLT devices
     */
    public function getTable(Request $request)
    {
        if ($request->ajax()) {
            $devices = OLT::with(['onus'])->select('om_olts.*');
            
            return datatables()
                ->eloquent($devices)
                ->addColumn('onu_count', function ($device) {
                    return $device->onus()->count();
                })
                ->addColumn('status', function ($device) {
                    $statusClass = [
                        'online' => 'success',
                        'offline' => 'danger',
                        'error' => 'warning'
                    ];
                    $class = $statusClass[$device->status] ?? 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($device->status) . '</span>';
                })
                ->addColumn('actions', function ($device) {
                    return view('plugins/fiberhome-olt-manager::devices.partials.actions', compact('device'))->render();
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }
        
        return app(OltDeviceTable::class)->render();
    }
    
    /**
     * Get OLT details as JSON
     */
    public function getDetails($id)
    {
        $device = OLT::with(['cards', 'ponPorts', 'onus'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $device->id,
                'name' => $device->name,
                'ip_address' => $device->ip_address,
                'model' => $device->model,
                'vendor' => $device->vendor,
                'status' => $device->status,
                'location' => $device->location,
                'description' => $device->description,
                'snmp_community' => $device->snmp_community,
                'snmp_version' => $device->snmp_version,
                'snmp_port' => $device->snmp_port,
                'onu_count' => $device->onus()->count(),
                'uptime' => $device->uptime,
                'last_sync' => $device->last_poll ? $device->last_poll->diffForHumans() : 'Never',
            ]
        ]);
    }
}