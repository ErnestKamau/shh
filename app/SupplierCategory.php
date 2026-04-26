<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class SupplierCategory extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
  public function item(){
    return $this->belongsTo('App\InventorySubCategories', 'inventory_sub_category_id');
	}

  public function supplier(){
    return $this->belongsTo('App\Supplier');
  }
}
