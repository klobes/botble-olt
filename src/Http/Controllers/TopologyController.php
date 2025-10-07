<?php

namespace Botble\FiberHomeOLTManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\FiberHomeOLTManager\Models\FiberCable;
use Botble\FiberHomeOLTManager\Models\JunctionBox;
use Botble\FiberHomeOLTManager\Models\Splitter;
//use Botble\FiberhomeOltManager\Models\OLT;
use Botble\FiberhomeOltManager\Models\Onu;
use Botble\FiberHomeOLTManager\Models\OLT;

use Illuminate\Http\Request;

class TopologyController extends BaseController
{
    public function index(Request $request)
    {
        page_title()->setTitle('Network Topology');

        $oltDevices = OLT::where('is_active', true)->get();
        $fiberCables = FiberCable::where('status', 'active')->get();
        $junctionBoxes = JunctionBox::where('status', 'active')->get();
        //$splitters = Splitter::where('status', 'active')->get();

        return view('plugins/fiberhome-olt-manager::topology.index', compact(
            'oltDevices', 'fiberCables', 'junctionBoxes',
        ));// 'splitters'
    }
	public function topology()
    {
        page_title()->setTitle(trans('plugins/fiberhome-olt-manager::topology.title'));

        $olts = OLT::with(['onus' => function ($query) {
            $query->select(['id', 'olt_id', 'serial_number', 'status', 'slot', 'port']);
        }])->get();

        return view('plugins/fiberhome-olt-manager::topology.index', compact('olts'));
    }


    public function tracePath(Request $request, Onu $onu)
    {
        page_title()->setTitle('Fiber Path Tracing');

        $path = $this->traceFiberPath($onu);
        $opticalBudget = $this->calculateOpticalBudget($path);

        return response()->json([
            'success' => true,
            'path' => $path,
            'optical_budget' => $opticalBudget,
        ]);
    }

    public function getJunctionBoxDetails(Request $request, JunctionBox $junctionBox)
    {
        $junctionBox->load(['splitters', 'spliceCassettes']);

        return response()->json([
            'success' => true,
            'junction_box' => $junctionBox,
        ]);
    }

    public function getAvailablePorts(Request $request, Splitter $splitter)
    {
        $availablePorts = $splitter->connections()
            ->where('status', 'active')
            ->whereNull('output_cable_segment_id')
            ->get();

        return response()->json([
            'success' => true,
            'available_ports' => $availablePorts,
        ]);
    }

   // public function calculateOpticalBudget(Request $request)
   // {
   //     $request->validate([
   //         'path' => 'required|array',
   //     ]);

   //     $budget = $this->calculateOpticalBudget($request->path);

   //     return response()->json([
    //        'success' => true,
   //         'budget' => $budget,
    //    ]);
    //}

    public function findOptimalPath(Request $request)
    {
        $request->validate([
            'source_id' => 'required|integer',
            'source_type' => 'required|string',
            'destination_id' => 'required|integer',
            'destination_type' => 'required|string',
        ]);

        $path = $this->findOptimalPath(
            $request->source_type,
            $request->source_id,
            $request->destination_type,
            $request->destination_id
        );

        return response()->json([
            'success' => true,
            'path' => $path,
        ]);
    }

    private function traceFiberPath(Onu $onu): array
    {
        $path = [];
        $currentSegment = $this->findSegmentToOnu($onu);

        while ($currentSegment) {
            $path[] = [
                'type' => 'cable_segment',
                'cable' => $currentSegment->fiberCable,
                'fiber_number' => $currentSegment->fiber_number,
                'length' => $currentSegment->segment_length,
                'attenuation' => $currentSegment->attenuation,
            ];

            $source = $this->getEquipment(
                $currentSegment->source_type,
                $currentSegment->source_id
            );

            $path[] = [
                'type' => strtolower($currentSegment->source_type),
                'equipment' => $source,
            ];

            if ($currentSegment->source_type === 'OltDevice') {
                break;
            }

            $currentSegment = $this->findPreviousSegment($source);
        }

        return array_reverse($path);
    }

    private function calculateOpticalBudget(array $path): array
    {
        $totalLoss = 0;
        $breakdown = [];

        foreach ($path as $item) {
            if ($item['type'] === 'cable_segment') {
                $loss = $item['attenuation'];
                $breakdown[] = [
                    'component' => "Cable ({$item['length']}m)",
                    'loss' => $loss,
                ];
            } elseif ($item['type'] === 'splitter') {
                $loss = $item['equipment']->insertion_loss;
                $breakdown[] = [
                    'component' => "Splitter {$item['equipment']->splitter_type}",
                    'loss' => $loss,
                ];
            } elseif ($item['type'] === 'splice') {
                $loss = $item['equipment']->splice_loss ?? 0.1;
                $breakdown[] = [
                    'component' => 'Splice',
                    'loss' => $loss,
                ];
            }

            $totalLoss += $loss;
        }

        return [
            'total_loss' => round($totalLoss, 2),
            'breakdown' => $breakdown,
            'expected_rx_power' => round(3.0 - $totalLoss, 2), // Assuming OLT TX power of 3.0 dBm
            'status' => $this->evaluateOpticalStatus(3.0 - $totalLoss),
        ];
    }

//    private function findOptimalPath(string $sourceType, int $sourceId, string $destinationType, int $destinationId): array
//    {
        // Implement path finding algorithm (Dijkstra's or similar)
        // This is a simplified version - in real implementation would use graph algorithms
        
//        $path = [];
        
        // Find all possible paths
//        $possiblePaths = $this->findAllPaths($sourceType, $sourceId, $destinationType, $destinationId);
        
        // Select path with minimum loss
//        $optimalPath = null;
 //       $minLoss = PHP_FLOAT_MAX;
        
//        foreach ($possiblePaths as $path) {
 //           $budget = $this->calculateOpticalBudget($path);
 //           if ($budget['total_loss'] < $minLoss) {
 //               $minLoss = $budget['total_loss'];
//                $optimalPath = $path;
//            }
//        }
        
//        return $optimalPath ?? [];
//    }

    private function findSegmentToOnu(Onu $onu): ?object
    {
        return CableSegment::where('destination_type', 'Onu')
            ->where('destination_id', $onu->id)
            ->where('status', 'active')
            ->first();
    }

    private function findPreviousSegment(object $equipment): ?object
    {
        return CableSegment::where('destination_type', get_class($equipment))
            ->where('destination_id', $equipment->id)
            ->where('status', 'active')
            ->first();
    }

    private function getEquipment(string $type, int $id): ?object
    {
        switch ($type) {
            case 'OltDevice':
                return OLT::find($id);
            case 'JunctionBox':
                return JunctionBox::find($id);
            case 'Splitter':
                return Splitter::find($id);
            case 'SpliceCassette':
                return SpliceCassette::find($id);
            default:
                return null;
        }
    }

    private function evaluateOpticalStatus(float $rxPower): string
    {
        if ($rxPower >= -8 && $rxPower <= -3) {
            return 'excellent';
        } elseif ($rxPower >= -15 && $rxPower < -8) {
            return 'good';
        } elseif ($rxPower >= -25 && $rxPower < -15) {
            return 'acceptable';
        } else {
            return 'poor';
        }
    }

    private function findAllPaths(string $sourceType, int $sourceId, string $destinationType, int $destinationId): array
    {
        // This is a simplified implementation
        // In a real system, you would implement a proper graph traversal algorithm
        
        $paths = [];
        
        // Get all segments from source
        $sourceSegments = CableSegment::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('status', 'active')
            ->get();
        
        foreach ($sourceSegments as $segment) {
            $path = [$this->buildPathItem($segment)];
            
            // Recursively find paths to destination
            $subPaths = $this->findPathsRecursive($segment, $destinationType, $destinationId);
            
            foreach ($subPaths as $subPath) {
                $paths[] = array_merge($path, $subPath);
            }
        }
        
        return $paths;
    }

    private function findPathsRecursive(object $currentSegment, string $destinationType, int $destinationId): array
    {
        $paths = [];
        
        $destination = $this->getEquipment(
            $currentSegment->destination_type,
            $currentSegment->destination_id
        );
        
        if (!$destination) {
            return $paths;
        }
        
        // Add current destination to path
        $path = [[
            'type' => strtolower($currentSegment->destination_type),
            'equipment' => $destination,
        ]];
        
        // Check if we've reached the destination
        if ($currentSegment->destination_type === $destinationType && 
            $currentSegment->destination_id === $destinationId) {
            return [$path];
        }
        
        // Continue searching
        $nextSegments = CableSegment::where('source_type', get_class($destination))
            ->where('source_id', $destination->id)
            ->where('status', 'active')
            ->get();
        
        foreach ($nextSegments as $nextSegment) {
            $subPaths = $this->findPathsRecursive($nextSegment, $destinationType, $destinationId);
            
            foreach ($subPaths as $subPath) {
                $paths[] = array_merge($path, $subPath);
            }
        }
        
        return $paths;
    }

    private function buildPathItem(object $segment): array
    {
        return [
            'type' => 'cable_segment',
            'cable' => $segment->fiberCable,
            'fiber_number' => $segment->fiber_number,
            'length' => $segment->segment_length,
            'attenuation' => $segment->attenuation,
        ];
    }
}