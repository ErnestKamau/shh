<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TaxRegime extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'tax_regime';
}
