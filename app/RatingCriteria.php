<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RatingCriteria extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	protected $with = ['guides'];

	public function guides(){
		return $this->hasMany(SupplierRatingCriteriaGuide::class, 'criteria_id')->orderBy('upper_value', 'asc');
	}
}
