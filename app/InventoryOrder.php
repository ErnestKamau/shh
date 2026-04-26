<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryOrder extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	public function order_items(){
		return $this->hasMany('App\InventoryOrderItem');
	}

	public function fulfilment_status(){
		$items = $this->order_items;
		$orderFulfilled = 0;

		foreach($items as $item){
			if($item->fulfilled == 1){
				$orderFulfilled+=1;
			}
		}

		return $orderFulfilled == 0 ? 'not_fulfilled' : ( $orderFulfilled == count($items) ? 'fulfilled' : 'partially_fulfilled' );
	}
}
