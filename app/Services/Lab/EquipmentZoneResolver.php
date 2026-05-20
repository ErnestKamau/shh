<?php

namespace App\Services\Lab;

use App\Models\Equipments\Equipment;

class EquipmentZoneResolver
{
    public function zoneIdForEquipment(Equipment $equipment): ?string
    {
        $equipment->loadMissing(['lab', 'assetLocation.lab']);

        if ($equipment->lab?->zone_id) {
            return (string) $equipment->lab->zone_id;
        }

        if ($equipment->assetLocation?->lab?->zone_id) {
            return (string) $equipment->assetLocation->lab->zone_id;
        }

        return null;
    }
}
