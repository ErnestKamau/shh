<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationDetails extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_details';
}
