<?php

namespace App\Http\Controllers;

use App\InventoryItem;
use App\InventoryStore;
use Illuminate\Http\Request;

class InventoryStoreController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(){
		$stores = InventoryStore::orderBy('name', 'asc')->where('inventory_stores.inventory_location_id', getCurrentUserLocation()->id)->get();

		$contents= InventoryItem::where('s.inventory_location_id', getCurrentUserLocation()->id)
			->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
			->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
			->join('inventory_stores as s', 's.id', '=', 'inventory_items.inventory_store_id')
			->leftJoin('item_states as ist', 'ist.id', '=', 'inventory_items.storage_state_id')
			->leftJoin('reporting_units as ru', 'ru.id', '=', 'ist.uom')
			->join('inventory_store_slots as ss', 'ss.id', '=', 'inventory_items.inventory_store_slot_id')
			->selectRaw('ist.id as storage_state_id, ist.name as storage_state, ru.name as state_unit_type, ic.name as category_name, isc.name, isc.unit_type as item_unit_type, isc.code, s.name as store, ss.name as slot, SUM(inventory_items.stock_in) as stock_in, SUM(inventory_items.stock_out) as stock_out, inventory_items.inventory_store_id as store_id, inventory_items.inventory_store_slot_id as slot_id, inventory_items.inventory_sub_category_id as item_id')
			->groupBy('ist.id', 'ist.name', 'ru.name', 'ic.name', 'isc.name', 'isc.unit_type', 'isc.code', 's.name', 'ss.name', 'inventory_items.inventory_store_id', 'inventory_items.inventory_store_slot_id', 'inventory_items.inventory_sub_category_id')
			->orderBy('s.name', 'asc')->orderBy('ss.name', 'asc')->orderBy('isc.name', 'asc')->get();

		$contentsData = array();

		foreach($contents  as $c){
			$item = InventoryItem::where('inventory_store_id', $c->store_id)->where('inventory_store_slot_id', $c->slot_id)
				->where('storage_state_id', $c->storage_state_id)->where('inventory_sub_category_id',$c->item_id)
				->select('created_at')->orderBy('created_at', 'asc')->first();

			$c->created_at = $item ? $item->created_at : null;
			$contentsData[] = $c;
		}

		$contents = $contentsData;

		// return response()->json($contents, 200);

		return view('layouts.inventory.stores.index', compact('stores', 'contents'));
	}

	public function add(Request $request)
	{
		$store = new InventoryStore;
		$store->name = $request->name;
		$store->type_of_store = $request->type_of_store ?? 'inventory_store';
		$store->company_id = getUserCompany();
		$store->inventory_location_id = getCurrentUserLocation()->id;
		$store->save();

		return redirect()->back()->with('success', 'Store Added.');
	}

	public function edit(Request $request, $id)
	{
		$store = InventoryStore::find($id);
		$store->name = $request->name;
		$store->type_of_store = $request->type_of_store ?? 'inventory_store';
		$store->save();

		return redirect()->back()->with('success', 'Store Edited.');
	}

	public function delete($id){
		$store = InventoryStore::find($id);

		$slots = $store->slots->count();

		if($slots == 0){
			$store->delete();
			return redirect()->back()->with('success', 'Store Removed.');
		}
		else{
			return redirect()->back()->with('error', 'Store has slots configured.');
		}
	}

	public function store_slots_by_item($item){
		$data = getUserStores(false, false, $item);
		
		$stores = array();

		foreach($data as $s){
			if(!isset($stores[$s->id])){
				$store = array("id"=>$s->id, "name"=>$s->name, "slots"=>array());
				$stores[$s->id] = $store;
			}
			$stores[$s->id]["slots"][] = ["name"=>$s->slot_name, "id"=>$s->slot_id];
		}

		return json_encode(array_values($stores));
	}
}
