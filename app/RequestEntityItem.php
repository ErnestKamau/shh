<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class RequestEntityItem extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	// protected $with = ['sub_category'];
	public function pending(){
		$requestEntity = RequestEntity::find($this->request_id);
		$siblingEntityIds = RequestEntity::where('parent_request_id', $requestEntity->id)
			->whereNotIn('status', ['Reversed', 'Rejected'])->pluck('id');

			$quantityIssued = RequestEntityItem::whereIn('request_id', $siblingEntityIds)->where('action', 'normal')
				->where('inventory_sub_category_id', $this->inventory_sub_category_id)
				->selectRaw('sum(quantity) as quantity')->pluck('quantity');
		return floatval($quantityIssued[0] ?? 0);
	}

	public function pending2(){	
		$action = "issued_received";

		$parentEntity = RequestEntity::find($this->request_id);

		$quantity = RequestEntityItem::join('request_entities as re','re.id', 'request_entity_items.request_id')
		->where('re.parent_request_id', $this->request_id)->where('action', $action);

		if(in_array($parentEntity->status, ["Items Issued Out","Goods Accepted"])){
			if($parentEntity->request_type == "Lend"){
				$quantity = $quantity->where('re.request_type', '!=', 'Material Issuance');
			}
		}

		if(in_array($parentEntity->status, ["Items Issued Out","Goods Accepted"])){
			if($parentEntity->request_type == "Loan"){
				$quantity = $quantity->where('re.request_type', '!=', 'Goods Receipt');
			}
		}

		$quantity = $quantity->where('inventory_sub_category_id', $this->inventory_sub_category_id)->where('item_brand_id', $this->item_brand_id)->where('comments', $this->comments)
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
		->where('re.parent_request_id', $this->request_id)->where('request_entity_items.action', $action)
		->where('request_entity_items.inventory_sub_category_id', $this->inventory_sub_category_id)
		->where('request_entity_items.item_brand_id', $this->item_brand_id)
		->selectRaw('sum(request_entity_items.quantity) as quantity, re.request_type')
		->groupBy('request_type')->get()->pluck('quantity', 'request_type');
	}

	public function category(){
		return InventoryCategories::join('inventory_sub_categories as isc', 'isc.inventory_category_id', '=', 'inventory_categories.id')
			->selectRaw('inventory_categories.*')
			->where('isc.id', $this->inventory_sub_category_id)->first();
	}

	public function sub_category(){
		return $this->belongsTo(InventorySubCategories::class, 'inventory_sub_category_id');
		// return InventoryCategories::join('inventory_sub_categories as isc', 'isc.inventory_category_id', '=', 'inventory_categories.id')
		// 	->selectRaw('inventory_categories.*')
		// 	->where('isc.id', $this->inventory_sub_category_id)->first();
	}
}