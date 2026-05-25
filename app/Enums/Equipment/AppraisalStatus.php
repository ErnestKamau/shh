<?php

namespace App\Enums\Equipment;

enum AppraisalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
