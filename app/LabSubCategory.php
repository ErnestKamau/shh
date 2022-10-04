<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class LabSubCategory extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'lab_sub_category';
}
