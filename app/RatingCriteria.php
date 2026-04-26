<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class RatingCriteria extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

	protected $with = ['guides'];

	public function guides(){
		return $this->hasMany(SupplierRatingCriteriaGuide::class, 'criteria_id')->orderBy('upper_value', 'asc');
	}
}
