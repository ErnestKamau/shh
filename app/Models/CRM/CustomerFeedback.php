<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerFeedback extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'customerfeedbacks';
}
