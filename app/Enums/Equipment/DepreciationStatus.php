<?php

namespace App\Enums\Equipment;

enum DepreciationStatus: string
{
    case Active = 'active';
    case FullyDepreciated = 'fully_depreciated';
    case Disabled = 'disabled';
    case Pending = 'pending';
}
