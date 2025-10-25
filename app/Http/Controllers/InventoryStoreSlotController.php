<?php

namespace App\Http\Controllers;

use App\InventoryStore;
use App\InventoryStoreSlot;
use Illuminate\Http\Request;

class InventoryStoreSlotController extends Controller
{
  /**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($store){
		if(!$store){
			return redirect()->route('inventory-stores')->with('error', 'Store not specified.');
		}

		$contents= \App\InventoryItem::where('s.inventory_location_id', getCurrentUserLocation()->id)
			->where('inventory_items.inventory_store_id', $store)
			->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
			->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
			->join('inventory_stores as s', 's.id', '=', 'inventory_items.inventory_store_id')
			->leftJoin('item_states as ist', 'ist.id', '=', 'inventory_items.storage_state_id')
			->leftJoin('reporting_units as ru', 'ru.id', '=', 'ist.uom')
			->join('inventory_store_slots as ss', 'ss.id', '=', 'inventory_items.inventory_store_slot_id')
			->selectRaw('ist.id as storage_state_id, ist.name as storage_state, ru.name as state_unit_type, ic.name as category_name, isc.name, isc.unit_type as item_unit_type, isc.code, s.name as store, ss.name as slot, SUM(inventory_items.stock_in) as stock_in, SUM(inventory_items.stock_out) as stock_out, inventory_items.inventory_store_id as store_id, inventory_items.inventory_store_slot_id as slot_id, inventory_items.inventory_sub_category_id as item_id')->where('isc.active', 1)
			->groupBy('ist.id', 'ist.name', 'ru.name', 'ic.name', 'isc.name', 'isc.unit_type', 'isc.code', 's.name', 'ss.name', 'inventory_items.inventory_store_id', 'inventory_items.inventory_store_slot_id', 'inventory_items.inventory_sub_category_id')
			->orderBy('s.name', 'asc')->orderBy('ss.name', 'asc')->orderBy('isc.name', 'asc')->get();

		$contentsData = array();

		foreach($contents  as $c){
			$item = \App\InventoryItem::where('inventory_store_id', $c->store_id)->where('inventory_store_slot_id', $c->slot_id)
				->where('storage_state_id', $c->storage_state_id)->where('inventory_sub_category_id',$c->item_id)
				->select('created_at')->orderBy('created_at', 'asc')->first();

			$c->created_at = $item ? $item->created_at : null;
			$contentsData[] = $c;
		}

		$contents = $contentsData;

		$store = InventoryStore::find($store);

		return view('layouts.inventory.stores.stores', compact('store', 'contents'));
	}

	public function add(Request $request, $store)
	{
		$slotO = new InventoryStoreSlot;
		$slotO->name = $request->name;
		$slotO->inventory_store_id = $store;
		$slotO->save();

		return redirect()->back()->with('success', 'Store Slot Added.');
	}

	public function edit(Request $request, $slot)
	{
		$slotO = InventoryStoreSlot::find($slot);
		$slotO->name = $request->name;
		$slotO->save();

		return redirect()->back()->with('success', 'Store Slot Edited.');
	}

	public function delete($id){
		$slot = InventoryStoreSlot::find($id);

		$contents = $slot->contents()->count();

		if($contents == 0){
			$slot->delete();
			return redirect()->back()->with('success', 'Store Slot Removed.');
		}
		else{
			return redirect()->back()->with('error', 'Slot has contents configured.');
		}
	}
}
