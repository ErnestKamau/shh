<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RequestEntityItem extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
		//
	public function pending(){
		$action = "issued_received";

		$quantity = RequestEntityItem::join('request_entities as re','re.id', 'request_entity_items.request_id')
		->where('re.parent_request_id', $this->request_id)->where('action', $action)
		->where('inventory_sub_category_id', $this->inventory_sub_category_id)
		->selectRaw('sum(request_entity_items.quantity) as quantity')->get()->pluck('quantity');

		// $quantity = RequestEntityItem::where('action', '=', $action)
		// 	->where('request_id', $this->request_id)
		// 	->where('inventory_sub_category_id', $this->inventory_sub_category_id)
		// 	->selectRaw('sum(request_entity_items.quantity) as quantity')->get()->pluck('quantity');
		return $quantity[0] ?? 0;
	}

	public function issued_received_breakdown(){
		$action = "issued_received";

		return RequestEntityItem::join('request_entities as re','re.id', 'request_entity_items.request_id')
		->where('re.parent_request_id', $this->request_id)->where('action', $action)
		->where('inventory_sub_category_id', $this->inventory_sub_category_id)
		->selectRaw('sum(request_entity_items.quantity) as quantity, re.request_type')
		->groupBy('request_type')->get()->pluck('quantity', 'request_type');
	}

	public function category(){
		return InventoryCategories::join('inventory_sub_categories as isc', 'isc.inventory_category_id', '=', 'inventory_categories.id')
			->selectRaw('inventory_categories.*')
			->where('isc.id', $this->inventory_sub_category_id)->first();
	}
	
}
