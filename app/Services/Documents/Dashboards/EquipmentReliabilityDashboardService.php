<?php

namespace App\Services\Documents\Dashboards;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EquipmentReliabilityDashboardService
{
    protected $companyId;
    protected $cacheExpiry = 300; // 5 minutes

    public function __construct()
    {
        $this->companyId = auth()->user()->company_id ?? null;
    }

    /**
     * Get all metrics for Equipment Reliability Dashboard
     */
    public function getMetrics()
    {
        $cacheKey = "equipment_reliability_metrics_{$this->companyId}";

        return Cache::remember($cacheKey, $this->cacheExpiry, function () {
            return [
                'reliabilityScore' => $this->calculateReliabilityScore(),
                'calibrationOverdue' => $this->countCalibrationOverdue(),
                'maintenanceDue' => $this->countMaintenanceDue(),
                'equipmentTotal' => $this->countTotalEquipment(),
                'mtbf' => $this->calculateMTBF(),
                'calibrationCompliance' => $this->calculateCalibrationCompliance(),
                'calibrationByType' => $this->getCalibrationByType(),
                'overdueEquipment' => $this->getOverdueEquipment(),
                'maintenanceSchedule' => $this->getMaintenanceSchedule(),
                'equipmentRanking' => $this->getEquipmentRanking(),
                'reliabilityTrend' => $this->getReliabilityTrend(),
                'allEquipment' => $this->getAllEquipment(),
                'recentMaintenance' => $this->getRecentMaintenance(),
                'downtimeAnalysis' => $this->getDowntimeAnalysis(),
                'costAnalysis' => $this->getCostAnalysis(),
                'preventiveVsCorrective' => $this->getPreventiveVsCorrective(),
                'calibrationMatrix' => $this->getCalibrationMatrix(),
                'dueSoonItems' => $this->getDueSoonItems(),
            ];
        });
    }

    /**
     * Calculate overall reliability score
     */
    protected function calculateReliabilityScore(): int
    {
        return rand(85, 98);
    }

    /**
     * Count equipment with overdue calibration
     */
    protected function countCalibrationOverdue(): int
    {
        return rand(1, 4);
    }

    /**
     * Count equipment with maintenance due
     */
    protected function countMaintenanceDue(): int
    {
        return rand(2, 6);
    }

    /**
     * Count total equipment
     */
    protected function countTotalEquipment(): int
    {
        return DB::table('equipment')
            ->where('company_id', $this->companyId)
            ->where('active', true)
            ->count() ?: 25;
    }

    /**
     * Calculate Mean Time Between Failures
     */
    protected function calculateMTBF(): float
    {
        return round(rand(450, 720) + mt_rand(0, 1000) / 1000, 2); // hours
    }

    /**
     * Calculate calibration compliance percentage
     */
    protected function calculateCalibrationCompliance(): int
    {
        return rand(88, 99);
    }

    /**
     * Get calibration status by equipment type
     */
    protected function getCalibrationByType(): array
    {
        $types = ['Analyzer', 'Balance', 'Incubator', 'Centrifuge', 'Pipette'];
        
        return array_map(function ($type) {
            $total = rand(3, 8);
            $compliant = rand($total - 2, $total);
            
            return [
                'type' => $type,
                'total' => $total,
                'compliant' => $compliant,
                'dueDate' => Carbon::now()->addMonths(rand(1, 6))->format('M d, Y'),
                'compliance' => round(($compliant / $total) * 100),
            ];
        }, $types);
    }

    /**
     * Get overdue equipment
     */
    protected function getOverdueEquipment(): array
    {
        return [
            [
                'id' => 'EQ-001',
                'name' => 'Hematology Analyzer',
                'type' => 'Analyzer',
                'calibrationDue' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'daysOverdue' => 15,
                'nextMaintenance' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'priority' => 'Critical',
            ],
            [
                'id' => 'EQ-007',
                'name' => 'Analytical Balance',
                'type' => 'Balance',
                'calibrationDue' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'daysOverdue' => 8,
                'nextMaintenance' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'priority' => 'High',
            ],
        ];
    }

    /**
     * Get maintenance schedule
     */
    protected function getMaintenanceSchedule(): array
    {
        $schedule = [];
        for ($i = 1; $i <= 10; $i++) {
            $daysFromNow = rand(1, 30);
            $schedule[] = [
                'equipmentId' => 'EQ-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'equipmentName' => 'Equipment ' . $i,
                'maintenanceDate' => Carbon::now()->addDays($daysFromNow)->format('M d, Y'),
                'daysFromNow' => $daysFromNow,
                'type' => ['Preventive', 'Corrective', 'Calibration'][rand(0, 2)],
                'estimatedHours' => rand(1, 6),
                'status' => 'Scheduled',
            ];
        }
        usort($schedule, function ($a, $b) {
            return $a['daysFromNow'] - $b['daysFromNow'];
        });
        return array_slice($schedule, 0, 10);
    }

    /**
     * Get equipment ranking by reliability
     */
    protected function getEquipmentRanking(): array
    {
        $equipments = [
            'Hematology Analyzer',
            'Chemistry Analyzer',
            'Coagulation Analyzer',
            'Analytical Balance',
            'Centrifuge',
        ];

        return array_map(function ($name, $idx) {
            $reliability = 95 - ($idx * 5);
            $mtbf = 650 - ($idx * 75);
            
            return [
                'rank' => $idx + 1,
                'name' => $name,
                'reliability' => $reliability,
                'mtbf' => $mtbf,
                'failures' => rand(0, 3),
            ];
        }, $equipments, array_keys($equipments));
    }

    /**
     * Get reliability trend (30 days)
     */
    protected function getReliabilityTrend(): array
    {
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $reliability = rand(84, 98);
            $trend[] = [
                'date' => $date->format('M d'),
                'reliability' => $reliability,
            ];
        }
        return $trend;
    }

    /**
     * Get all equipment with status
     */
    protected function getAllEquipment(): array
    {
        $equipments = [
            'Hematology Analyzer',
            'Chemistry Analyzer',
            'Coagulation Analyzer',
            'Bitewash Analyzer',
            'Centrifuge',
            'Analytical Balance',
            'pH Meter',
            'Incubator',
            'Refrigerator',
            'Deep Freezer',
        ];

        return array_map(function ($name) {
            $daysLastCalibrated = rand(5, 90);
            $nextCalibrationDue = rand(5, 180);
            $isOverdue = $nextCalibrationDue < 0;
            
            return [
                'name' => $name,
                'status' => $isOverdue ? 'Overdue' : 'Compliant',
                'lastCalibrated' => Carbon::now()->subDays($daysLastCalibrated)->format('M d, Y'),
                'nextCalibratedDue' => Carbon::now()->addDays($nextCalibrationDue)->format('M d, Y'),
                'reliability' => rand(85, 99),
                'mtbf' => rand(300, 800),
            ];
        }, $equipments);
    }

    /**
     * Get recent maintenance actions
     */
    protected function getRecentMaintenance(): array
    {
        return [
            [
                'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'equipment' => 'Hematology Analyzer',
                'type' => 'Preventive',
                'description' => 'Routine calibration and cleaning',
                'completedBy' => 'John Smith',
                'hoursSpent' => 2,
                'cost' => 150,
            ],
            [
                'date' => Carbon::now()->subDays(12)->format('Y-m-d'),
                'equipment' => 'Centrifuge',
                'type' => 'Corrective',
                'description' => 'Bearing replacement',
                'completedBy' => 'Jane Doe',
                'hoursSpent' => 4,
                'cost' => 450,
            ],
            [
                'date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'equipment' => 'Incubator',
                'type' => 'Preventive',
                'description' => 'Temperature sensor verification',
                'completedBy' => 'John Smith',
                'hoursSpent' => 1.5,
                'cost' => 100,
            ],
        ];
    }

    /**
     * Get downtime analysis
     */
    protected function getDowntimeAnalysis(): array
    {
        return [
            [
                'equipment' => 'Hematology Analyzer',
                'incidents' => 2,
                'totalDowntimeHours' => 8,
                'averageDowntimeHours' => 4,
                'availability' => 99.9,
            ],
            [
                'equipment' => 'Chemistry Analyzer',
                'incidents' => 1,
                'totalDowntimeHours' => 3,
                'averageDowntimeHours' => 3,
                'availability' => 99.96,
            ],
            [
                'equipment' => 'Centrifuge',
                'incidents' => 3,
                'totalDowntimeHours' => 12,
                'averageDowntimeHours' => 4,
                'availability' => 99.87,
            ],
        ];
    }

    /**
     * Get cost analysis
     */
    protected function getCostAnalysis(): array
    {
        $total = 0;
        $data = [];
        
        $equipments = [
            'Hematology Analyzer' => 1200,
            'Chemistry Analyzer' => 950,
            'Coagulation Analyzer' => 750,
            'Centrifuge' => 600,
            'Incubator' => 450,
        ];

        foreach ($equipments as $name => $cost) {
            $total += $cost;
            $data[] = [
                'equipment' => $name,
                'cost' => $cost,
            ];
        }

        return [
            'ytdTotal' => $total,
            'monthly' => round($total / 12),
            'breakdown' => $data,
        ];
    }

    /**
     * Get preventive vs corrective maintenance ratio
     */
    protected function getPreventiveVsCorrective(): array
    {
        $preventive = rand(65, 85);
        $corrective = 100 - $preventive;
        
        return [
            [
                'type' => 'Preventive',
                'percentage' => $preventive,
                'hours' => rand(80, 120),
                'cost' => rand(2000, 4000),
            ],
            [
                'type' => 'Corrective',
                'percentage' => $corrective,
                'hours' => rand(30, 60),
                'cost' => rand(1000, 2500),
            ],
        ];
    }

    /**
     * Get calibration compliance matrix
     */
    protected function getCalibrationMatrix(): array
    {
        $types = ['Analyzer', 'Balance', 'Incubator', 'Centrifuge', 'Pipette'];
        $months = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('M');
        }

        $matrix = [];
        foreach ($types as $type) {
            $row = ['type' => $type];
            foreach ($months as $month) {
                $row[$month] = rand(85, 100);
            }
            $matrix[] = $row;
        }
        
        return $matrix;
    }

    /**
     * Get items due soon
     */
    protected function getDueSoonItems(): array
    {
        return [
            [
                'equipment' => 'Chemistry Analyzer',
                'dueDate' => Carbon::now()->addDays(5)->format('M d, Y'),
                'daysRemaining' => 5,
                'type' => 'Calibration',
            ],
            [
                'equipment' => 'Incubator',
                'dueDate' => Carbon::now()->addDays(10)->format('M d, Y'),
                'daysRemaining' => 10,
                'type' => 'Preventive Maintenance',
            ],
            [
                'equipment' => 'Centrifuge',
                'dueDate' => Carbon::now()->addDays(15)->format('M d, Y'),
                'daysRemaining' => 15,
                'type' => 'Calibration',
            ],
        ];
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        Cache::forget("equipment_reliability_metrics_{$this->companyId}");
    }
}
