<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryItem extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	public function category(){
		return $this->belongsTo('App\InventoryCategories', 'inventory_category_id');
	}

	public function creator(){
		return $this->belongsTo('App\User', 'created_by');
	}

	public function receiver(){
		return $this->belongsTo('App\User', 'received_by');
	}

	public function department(){
		return $this->belongsTo('App\InventoryDepartment', 'inventory_department_id');
	}

	public function note(){
		return $this->hasOne('App\InventoryItemNote', 'inventory_item_id');
	}

	public function supplier(){
		return $this->belongsTo('App\Supplier');
	}

	public function sub_category(){
		return $this->belongsTo('App\InventorySubCategories', 'inventory_sub_category_id');
	}

	public function slot(){
		return $this->hasOne('App\InventoryStoreSlotContent', 'inventory_item_id');
	}

	public function rating(){
		return $this->hasOne('App\InventorySupplierRating');
	}
}
