<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerContact extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = "crm_customer_contacts";
}
