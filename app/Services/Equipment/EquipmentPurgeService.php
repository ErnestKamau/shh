<?php

namespace App\Services\Equipment;

use App\Models\Equipments\Equipment;
use Illuminate\Support\Facades\DB;

final class EquipmentPurgeService
{
    /**
     * Delete all equipment records for a company.
     *
     * @return array<string, int>
     */
    public function purgeForCompany(string $companyId): array
    {
        $summary = [
            'equipment' => 0,
        ];

        DB::transaction(function () use ($companyId, &$summary): void {
            $equipmentIds = Equipment::query()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if ($equipmentIds === []) {
                return;
            }

            DB::table('equipment')
                ->whereIn('daily_log_monitored_equipment_id', $equipmentIds)
                ->update([
                    'daily_log_monitored_equipment_id' => null,
                    'daily_log_monitored_by_another_equipment' => false,
                ]);

            $summary['equipment'] = Equipment::query()
                ->where('company_id', $companyId)
                ->delete();
        });

        return $summary;
    }
}
