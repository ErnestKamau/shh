<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class LabCategoryItems extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'lab_category_items';
}
