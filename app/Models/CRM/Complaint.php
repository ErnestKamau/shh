<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    //
}
