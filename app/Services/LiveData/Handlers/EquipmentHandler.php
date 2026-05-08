<?php

namespace App\Services\LiveData\Handlers;

use Illuminate\Support\Facades\DB;
use App\Services\LiveData\Contracts\LiveDataHandlerInterface;
use App\Services\LiveData\Contracts\FormatterInterface;
use App\Services\LiveData\DTOs\LiveDataResult;

class EquipmentHandler implements LiveDataHandlerInterface
{
    public function __construct(protected FormatterInterface $formatter) {}

    public function supports(string $intent): bool
    {
        return str_starts_with($intent, 'equipment_') || str_starts_with($intent, 'board_equipment_');
    }

    public function handle(string $intent, ?string $question = null): LiveDataResult
    {
        $data = match ($intent) {
            'equipment_utilization'       => $this->equipmentUtilization(),
            'equipment_maintenance_schedule' => $this->equipmentMaintenanceSchedule(),
            'equipment_downtime_summary'     => $this->equipmentDowntimeSummary(),
            default => ['reply' => "Unsupported equipment intent: {$intent}", 'value' => null]
        };

        return LiveDataResult::fromArray($intent, $data);
    }

    private function equipmentUtilization(): array
    {
        $rows = DB::table('equipment as e')
            ->leftJoin('equipment_usage as eu', 'e.id', '=', 'eu.equipment_id')
            ->whereRaw("eu.end_date >= (CURRENT_DATE - INTERVAL '30 days') OR eu.end_date IS NULL")
            ->selectRaw('e.name, e.asset_code, COUNT(DISTINCT eu.id) as usage_count, MAX(eu.end_date) as last_used')
            ->groupBy('e.id', 'e.name', 'e.asset_code')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        $table = $this->formatter->buildTable(
            ['Equipment', 'Uses (30d)', 'Last Used'],
            $rows->map(fn($r) => [
                'name' => $r->name . ($r->asset_code ? " ({$r->asset_code})" : ''),
                'uses' => $r->usage_count,
                'last' => $r->last_used ? \Carbon\Carbon::parse($r->last_used)->format('d M Y') : 'Never'
            ])->toArray()
        );

        return [
            'reply' => "## Equipment Utilization (Last 30 Days)\n\n" . $table,
            'value' => $rows->count()
        ];
    }

    private function equipmentMaintenanceSchedule(): array
    {
        $rows = DB::table('v_equipment_reliability')
            ->whereRaw("next_maintenance_due BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '30 days')")
            ->where('maintenance_status', '!=', 'overdue')
            ->select('equipment_name', 'asset_code', 'next_maintenance_due', 'assigned_department')
            ->orderBy('next_maintenance_due')
            ->limit(10)
            ->get();

        $total = $rows->count();
        $reply = "## Upcoming Equipment Maintenance\n\n"
            . "**{$total}** piece(s) have maintenance due soon.\n\n";

        if ($total > 0) {
            $table = $this->formatter->buildTable(
                ['Equipment', 'Department', 'Due Date'],
                $rows->map(fn($r) => [
                    'name' => $r->equipment_name . ($r->asset_code ? " ({$r->asset_code})" : ''),
                    'dept' => $r->assigned_department ?? 'Unassigned',
                    'due' => \Carbon\Carbon::parse($r->next_maintenance_due)->format('d M Y')
                ])->toArray()
            );
            $reply .= $table;
        }

        return ['reply' => $reply, 'value' => $total];
    }

    private function equipmentDowntimeSummary(): array
    {
        $unreliable = DB::table('v_equipment_reliability')
            ->where('is_overdue', 1)
            ->orWhere('maintenance_status', 'overdue')
            ->select('equipment_name', 'asset_code', 'maintenance_overdue_days')
            ->orderByDesc('maintenance_overdue_days')
            ->limit(5)
            ->get();

        $total = $unreliable->count();
        $reply = "## Equipment Reliability Alert\n\n";

        if ($total > 0) {
            $table = $this->formatter->buildTable(
                ['Instrument', 'Asset Code', 'Overdue Days', 'Status'],
                $unreliable->map(function($eq) {
                    $days = (int) $eq->maintenance_overdue_days;
                    return [
                        'name' => $eq->equipment_name,
                        'code' => $eq->asset_code,
                        'days' => $days,
                        'status' => $days > 30 ? '🔴 Non-Operational' : '🟡 High Risk'
                    ];
                })->toArray()
            );
            $reply .= $table;
        } else {
            $reply .= "✓ No significant downtime or maintenance lapses detected.";
        }

        return ['reply' => $reply, 'value' => $total];
    }
}
