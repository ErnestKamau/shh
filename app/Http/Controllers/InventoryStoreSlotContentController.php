<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\InventoryItem;
use App\InventoryStore;
use App\InventoryStoreSlot;
use App\InventoryStoreSlotContent;
use Illuminate\Http\Request;

class InventoryStoreSlotContentController extends Controller
{
  /**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($slot, $store){
		$store = InventoryStore::find($store);
		$slot = InventoryStoreSlot::find($slot);

		$contents= InventoryItem::where('inventory_items.inventory_store_id', $store->id)
			->where('inventory_items.inventory_store_slot_id', $slot->id)
			->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
			->join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
			->join('inventory_stores as s', 's.id', '=', 'inventory_items.inventory_store_id')
			->leftJoin('item_states as ist', 'ist.id', '=', 'inventory_items.storage_state_id')
			->leftJoin('reporting_units as ru', 'ru.id', '=', 'ist.uom')
			->join('inventory_store_slots as ss', 'ss.id', '=', 'inventory_items.inventory_store_slot_id')
			->selectRaw('ist.id as storage_state_id, ist.name as storage_state, ru.name as state_unit_type, ic.name as category_name, isc.name, isc.unit_type as item_unit_type, isc.code, s.name as store, ss.name as slot, SUM(inventory_items.stock_in) as stock_in, SUM(inventory_items.stock_out) as stock_out, inventory_items.inventory_store_id as store_id, inventory_items.inventory_store_slot_id as slot_id, inventory_items.inventory_sub_category_id as item_id')
			->groupBy('ist.id', 'ist.name', 'ru.name', 'ic.name', 'isc.name', 'isc.unit_type', 'isc.code', 's.name', 'ss.name', 'inventory_items.inventory_store_id', 'inventory_items.inventory_store_slot_id', 'inventory_items.inventory_sub_category_id')
			->orderBy('s.name', 'asc')->orderBy('ss.name', 'asc')->orderBy('isc.name', 'asc')->get();
		// return response()->json($contents, 200);

		$contentsData = array();

		foreach($contents  as $c){
			$item = \App\InventoryItem::where('inventory_store_id', $c->store_id)->where('inventory_store_slot_id', $c->slot_id)
				->where('storage_state_id', $c->storage_state_id)->where('inventory_sub_category_id',$c->item_id)
				->select('created_at')->orderBy('created_at', 'asc')->first();

			$c->created_at = $item ? $item->created_at : null;
			$contentsData[] = $c;
		}

		$contents = $contentsData;


		return view('layouts.inventory.stores.contents', compact('store', 'slot', 'contents'));
	}

	public function add(Request $request, $slot, $store, $internal)
	{
		$item = InventoryItem::where('batch_code', $request->item)->first();

		$sloted = intval($slot);

		$content = new InventoryStoreSlotContent;
		$content->inventory_store_slot_id = $sloted;
		$content->inventory_item_id = $item->id;
		$content->inventory_sub_category_id = $item->inventory_sub_category_id;
		$content->save();

		if($internal){
			return $content;
		}

		return redirect()->back()->with('success', 'Item added to the slot.');
	}

	public function delete($id){
		InventoryStoreSlotContent::find($id)->delete();
		return redirect()->back()->with('success', 'Slot Item Removed.');
	}
}
