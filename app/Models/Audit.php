<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Audit extends \OwenIt\Auditing\Models\Audit
{
    use HasUuids;
}
