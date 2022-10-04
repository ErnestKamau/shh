<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class VerificationLog extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    //
}
