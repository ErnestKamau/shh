<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SupplierQuote extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function supplier()
	{
		return $this->belongsTo('App\Supplier');
	}
	
	public function request_item()
	{
		return $this->belongsTo('App\RequestEntityItem');
	}
}
