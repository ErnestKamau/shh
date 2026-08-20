<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class InventorySubCategories extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

  public function available(){
    $id = $this->id;
    $stockDetails = $this->stock();

    return array(
      "available" => floatval($stockDetails['stock_in']['total']) - floatval($stockDetails['stock_out']['total']),
      "pending" => floatval($stockDetails['pending']['total'])
    );
	}

	public function availableByStoreSlot($req_id){
		$req = \App\RequestEntity::find($req_id);

		$myCCs = explode(',', $req->cost_center ?? '');
		$zStore = null;
		if (\Illuminate\Support\Facades\Schema::hasTable('store_to_cost_centers')) {
			$zStore = \App\StoreToCostCenter::whereIn('cost_center', $myCCs)->first();
		}
		if(isset($zStore->store_id)){
			$zStoreSlot = \App\InventoryStoreSlot::where('inventory_store_id', $zStore->store_id)->first();
		}

		$defaultStore['store'] = isset($zStore->store_id) ? $zStore->store_id : 0;
		$defaultStore['slot'] = isset($zStoreSlot) && isset($zStoreSlot->id) ? $zStoreSlot->id : 0;


		$stock = \App\InventoryItem::join('inventory_stores as ins', 'inventory_items.inventory_store_id', 'ins.id')
			->join('inventory_store_slots as iss', 'iss.id', 'inventory_items.inventory_store_slot_id')
			->selectRaw('SUM(stock_in) as stockin, SUM(stock_out) as stockout')
			->where('inventory_sub_category_id', $this->id);

		if($defaultStore['store'] > 0){
			$stock = $stock->where('inventory_items.inventory_store_id', $defaultStore['store']);
		}

		if($defaultStore['slot'] > 0){
			$stock = $stock->where('inventory_items.inventory_store_slot_id', $defaultStore['slot']);
		}

		$stock = $stock->first();

		return floatval($stock->stockin) - floatval($stock->stockout);
	}

	public function suppliers(){
		return \App\SupplierCategory::where('inventory_sub_category_id', $this->id)
			->join('suppliers as s', 's.id', '=', 'supplier_categories.supplier_id')
			->select('s.id', 's.name', 's.email', 's.phone')->distinct('id')->get();
	}

	public function getBrands(){
		return \App\ItemBrand::where('inventory_sub_category_id', $this->id)->orderBy('name', 'asc')
			->selectRaw('id, name, image')->where('status', 1)->get();
	}

  public function category(){
    return $this->belongsTo('App\InventoryCategories', 'inventory_category_id');
	}

	function inventory_items_eff(){ //Use join to call all relevant data
		return InventoryItem::join('users as creator', 'creator.id', '=', 'inventory_items.created_by')
			->where('inventory_sub_category_id', $this->id)->orderBy('created_at', 'desc');
	}

  public function inventory_items(){
    return $this->hasMany('App\InventoryItem', 'inventory_sub_category_id')->orderBy('created_at', 'desc');
	}

	public function sorted_items(){
		$items = \App\InventoryItem::where('inventory_sub_category_id', $this->id)->orderBy('expiry', 'asc')->get();
		$response = array("items"=>array());

		foreach($items as $item){
			$dep = $item->department->name ?? 'Procurement';
			if(intval($item->stock_in) != 0){
				if($dep == 'Procurement'){
					$response['items'][] = $item;
				}
				else{
					$response[$dep][] = $item;
				}
			}
			else{
				if(!isset($response[$dep])){
					$response[$dep] = array();
				}
				$response[$dep][] = $item;
			}
		}

		return $response;
	}

  public function stock(){
    $id = $this->id;

    $inventoryItems = \App\InventoryItem::selectRaw('status, SUM(stock_in) as stock_in, SUM(stock_out) as stock_out')
    ->where('inventory_items.inventory_sub_category_id', $id)->groupBy('stock_in', 'stock_out', 'status')->get();

    $inventoryItemsArr = array(
      "stock_in"=>array("total"=>0),
      "stock_out"=>array("total"=>0),
      "pending"=>array("total"=>0),
    );

    foreach($inventoryItems as $iItem){
      if($iItem->status == "pending"){
        $inventoryItemsArr['pending']['total'] += floatval($iItem->stock_in);
      }else{
        $inventoryItemsArr['stock_in']['total'] += floatval($iItem->stock_in);
        $inventoryItemsArr['stock_out']['total'] += floatval($iItem->stock_out);
      }
    }
    return $inventoryItemsArr;
	}

	public function daily_demand(){
		return ($this->working_days== 0 || $this->annual_consumption == 0) ? 0 : $this->annual_consumption/($this->working_days ?? 365);
	}

	public function total_lead_time(){
		return $this->internal_lead_time+$this->external_lead_time;
	}

	public function lead_time_consumption(){
		return $this->daily_demand()*$this->total_lead_time();
	}

	public function safety_stock(){
		return $this->lead_time_consumption()*($this->estimated_variation_in_demand_average_consumption/100);
	}

	public function reorder_level(){
		return $this->lead_time_consumption()+$this->safety_stock();
	}

	public function standard_order_quantity(){
		return $this->reorder_level() > $this->maximum_order_quantity ? $this->reorder_level() : $this->maximum_order_quantity;
	}

	public function max_standard_inventory(){
		return $this->reorder_level()+$this->standard_order_quantity();
	}
}
