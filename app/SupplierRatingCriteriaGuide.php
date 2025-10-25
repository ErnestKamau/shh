<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SupplierRatingCriteriaGuide extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    public function criteria(){
        return $this->belongsTo(RatingCriteria::class, 'criteria_id');
    }
}
