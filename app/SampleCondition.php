<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleCondition extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'name',
		'active',
		'sample_type_id',
		'short_name',
		'reporting_time'
	];
	
	public function sample_type(){
	  return $this->belongsTo('App\SampleType');
	}
}
