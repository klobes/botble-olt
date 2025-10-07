<?php

namespace Botble\FiberHomeOLTManager\Services;

use Botble\FiberHomeOLTManager\Models\EquipmentMaintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MaintenanceService
{
    /**
     * Create a new maintenance record
     */
    public function createMaintenance(array $data): EquipmentMaintenance
    {
        return EquipmentMaintenance::create($data);
    }

    /**
     * Get maintenance history for equipment
     */
    public function getMaintenanceHistory(string $equipmentType, int $equipmentId): array
    {
        return EquipmentMaintenance::where('equipment_type', $equipmentType)
            ->where('equipment_id', $equipmentId)
            ->orderBy('maintenance_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get upcoming maintenance tasks
     */
    public function getUpcomingMaintenance(int $days = 30): array
    {
        return EquipmentMaintenance::where('maintenance_date', '>=', now())
            ->where('maintenance_date', '<=', now()->addDays($days))
            ->orderBy('maintenance_date')
            ->get()
            ->toArray();
    }

    /**
     * Schedule maintenance for equipment
     */
    public function scheduleMaintenance(string $equipmentType, int $equipmentId, array $data): EquipmentMaintenance
    {
        $data['equipment_type'] = $equipmentType;
        $data['equipment_id'] = $equipmentId;

        return $this->createMaintenance($data);
    }

    /**
     * Update maintenance record
     */
    public function updateMaintenance(int $id, array $data): ?EquipmentMaintenance
    {
        $maintenance = EquipmentMaintenance::find($id);
        
        if (!$maintenance) {
            return null;
        }

        $maintenance->update($data);
        
        return $maintenance;
    }

    /**
     * Get maintenance statistics
     */
    public function getMaintenanceStats(string $equipmentType = null, int $days = 30): array
    {
        $query = EquipmentMaintenance::query();
        
        if ($equipmentType) {
            $query->where('equipment_type', $equipmentType);
        }
        
        $query->where('maintenance_date', '>=', now()->subDays($days));
        
        return [
            'total_maintenance' => $query->count(),
            'by_type' => $query->selectRaw('maintenance_type, COUNT(*) as count')
                ->groupBy('maintenance_type')
                ->pluck('count', 'maintenance_type')
                ->toArray(),
            'total_cost' => $query->sum('cost'),
            'upcoming_tasks' => $query->where('maintenance_date', '>=', now())->count(),
        ];
    }

    /**
     * Get maintenance recommendations based on usage patterns
     */
    public function getMaintenanceRecommendations(string $equipmentType, int $equipmentId): array
    {
        $history = $this->getMaintenanceHistory($equipmentType, $equipmentId);
        
        $recommendations = [];
        
        // Analyze maintenance patterns
        $lastMaintenance = collect($history)->first();
        
        if ($lastMaintenance) {
            $lastDate = Carbon::parse($lastMaintenance['maintenance_date']);
            $daysSinceLast = now()->diffInDays($lastDate);
            
            // Recommend inspection if more than 6 months
            if ($daysSinceLast > 180) {
                $recommendations[] = [
                    'type' => 'inspection',
                    'priority' => 'medium',
                    'description' => 'Routine inspection recommended (6+ months since last)',
                    'suggested_date' => now()->addDays(30),
                ];
            }
        }
        
        // Check for specific equipment types
        switch ($equipmentType) {
            case 'JunctionBox':
                $recommendations[] = [
                    'type' => 'inspection',
                    'priority' => 'low',
                    'description' => 'Junction box cleaning and inspection',
                    'suggested_date' => now()->addDays(90),
                ];
                break;
                
            case 'Splitter':
                $recommendations[] = [
                    'type' => 'inspection',
                    'priority' => 'low',
                    'description' => 'Splitter port cleaning and testing',
                    'suggested_date' => now()->addDays(180),
                ];
                break;
                
            case 'FiberCable':
                $recommendations[] = [
                    'type' => 'inspection',
                    'priority' => 'medium',
                    'description' => 'Fiber cable inspection and OTDR testing',
                    'suggested_date' => now()->addDays(365),
                ];
                break;
        }
        
        return $recommendations;
    }

    /**
     * Create maintenance report
     */
    public function createMaintenanceReport(array $filters = []): array
    {
        $query = EquipmentMaintenance::query();
        
        if (isset($filters['start_date'])) {
            $query->where('maintenance_date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('maintenance_date', '<=', $filters['end_date']);
        }
        
        if (isset($filters['equipment_type'])) {
            $query->where('equipment_type', $filters['equipment_type']);
        }
        
        if (isset($filters['maintenance_type'])) {
            $query->where('maintenance_type', $filters['maintenance_type']);
        }
        
        $maintenance = $query->orderBy('maintenance_date', 'desc')->get();
        
        return [
            'summary' => $this->getMaintenanceStats(
                $filters['equipment_type'] ?? null,
                isset($filters['start_date']) ? now()->diffInDays($filters['start_date']) : 365
            ),
            'detailed_report' => $maintenance->toArray(),
            'recommendations' => $this->generateReportRecommendations($maintenance),
        ];
    }

    /**
     * Generate report recommendations
     */
    private function generateReportRecommendations($maintenance): array
    {
        $recommendations = [];
        
        // Analyze patterns
        $byType = $maintenance->groupBy('maintenance_type');
        $byEquipment = $maintenance->groupBy('equipment_type');
        
        // Recommendations based on patterns
        if ($byType->has('repair') && $byType->get('repair')->count() > 5) {
            $recommendations[] = [
                'type' => 'planning',
                'priority' => 'high',
                'description' => 'High number of repairs detected. Consider preventive maintenance schedule.',
            ];
        }
        
        if ($byEquipment->has('FiberCable') && $byEquipment->get('FiberCable')->count() > 10) {
            $recommendations[] = [
                'type' => 'inspection',
                'priority' => 'medium',
                'description' => 'Multiple fiber cable maintenance events. Consider network audit.',
            ];
        }
        
        return $recommendations;
    }
}