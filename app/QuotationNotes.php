<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationNotes extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'supplier_quote_notes';
}
