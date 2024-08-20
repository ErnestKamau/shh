<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ZohoCustomers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "zoho_customers";

    
}
