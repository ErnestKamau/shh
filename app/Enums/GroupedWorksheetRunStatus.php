<?php

namespace App\Enums;

enum GroupedWorksheetRunStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
