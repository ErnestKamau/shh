<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Chain_of_Custody_Complaint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'chain_of_custody_complaints';
}
