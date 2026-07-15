<?php

namespace App\Services\Lab;

use App\Models\Equipments\Equipment;

/**
 * Zone resolution removed. Returns null so callers do not scope by lab zone.
 */
class EquipmentZoneResolver
{
    public function resolveZoneId(Equipment $equipment): ?string
    {
        return null;
    }
}
