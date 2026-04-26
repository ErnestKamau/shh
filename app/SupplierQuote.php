<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class SupplierQuote extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

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
