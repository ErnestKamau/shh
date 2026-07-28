<?php

namespace App\Http\Controllers\StockTaking;

use App\Http\Controllers\Controller;
use App\StockTaking;
use App\InventoryItem;
use App\InventoryStore;
use App\StockTakingSheet;
use App\InventoryStoreSlotContent;
use App\User;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Main extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}

	public function index()
	{
		$takings = StockTaking::query()
			->where('stock_takings.inventory_location_id', getCurrentUserLocation()->id)
			->orderBy('created_at', 'desc')
			->get();

		$userIds = $takings->flatMap(fn ($taking) => [
			$taking->created_by,
			$taking->updated_by,
			$taking->approved_by,
		])->filter()->unique()->values();

		$users = User::query()
			->whereIn('id', $userIds)
			->get(['id', 'name'])
			->keyBy('id');

		foreach ($takings as $taking) {
			$taking->creator = $users->get($taking->created_by)?->name;
			$taking->approver_name = $users->get($taking->approved_by)?->name;
			$taking->updater_name = $users->get($taking->updated_by)?->name;
		}

		return view('layouts.inventory.stock-taking.index', compact('takings'));
	}

	public function show(Request $request, $id, $print=false)
	{
		$taking = StockTaking::find($id);
		$store_ids = explode(',', $taking->stores);

		$stores = DB::table('inventory_stores as s')
			->join('inventory_store_slots as ss', 'ss.inventory_store_id', 's.id')->whereIn('s.id', $store_ids)
			->get();

		$itemsNo = StockTakingSheet::where('stock_taking_id', $id)->get()->count();

		$items = InventoryItem::whereIn('inventory_items.inventory_store_id', $store_ids)
		->leftJoin('stock_taking_sheets as sts', function($join) use ($id){
			$join->on('sts.inventory_sub_category_id', '=', 'inventory_items.inventory_sub_category_id');
			$join->on('sts.store_id', '=', 'inventory_items.inventory_store_id');
			$join->on('sts.slot_id', '=', 'inventory_items.inventory_store_slot_id');
			$join->where('sts.stock_taking_id', '=', $id);
		});

		$items= InventoryItem::whereIn('inventory_items.inventory_store_id', $store_ids)
				->leftJoin('stock_taking_sheets as sts', function($join) use ($id){
					$join->on('sts.inventory_sub_category_id', '=', 'inventory_items.inventory_sub_category_id');
					$join->on('sts.store_id', '=', 'inventory_items.inventory_store_id');
					$join->on('sts.slot_id', '=', 'inventory_items.inventory_store_slot_id');
					$join->where('sts.stock_taking_id', '=', $id);
				})
				->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
				->join('inventory_stores as s', 's.id', '=', 'inventory_items.inventory_store_id')
				->join('inventory_store_slots as ss', 'ss.id', '=', 'inventory_items.inventory_store_slot_id')
				->selectRaw('sts.comments, sts.system_quantity, COALESCE(sts.lot_no, COALESCE(inventory_items.lot_no, "NA")) as lot_no, COALESCE(sts.expiry, inventory_items.expiry) as expiry, sts.available_quantity, isc.name, isc.unit_type, isc.unit_price, isc.code, s.name as store, ss.name as slot, SUM(inventory_items.stock_in) as stock_in, SUM(inventory_items.stock_out) as stock_out, inventory_items.inventory_store_id as store_id, inventory_items.inventory_store_slot_id as slot_id, inventory_items.inventory_sub_category_id as item_id')
				->groupBy('lot_no', 'inventory_items.inventory_store_id', 'inventory_items.inventory_store_slot_id', 'inventory_items.inventory_sub_category_id')
				->orderBy('s.name', 'asc')->orderBy('ss.name', 'asc')->orderBy('isc.name', 'asc')->get();

		// return response()->json($items, 200);

		$dataByStore = array();

		foreach($items as $i){
			if(!isset($dataByStore[$i->store.$i->store_id])){
				$dataByStore[$i->store.$i->store_id] = array();
			}

			if($taking->status != "Completed"){
				$i->quantity = floatval($i->stock_in) - floatval($i->stock_out);
				$i->quantity = $i->quantity < 0 ? 0 : $i->quantity;
			}
			else{
				$i->quantity = $i->system_quantity;
			}

			$dataByStore[$i->store.$i->store_id][] = $i;
		}

		// return response()->json($dataByStore, 200);

		return view($print ? 'layouts.inventory.stock-taking.sheet' : ($request->get('print') == 'true' ? 'layouts.inventory.stock-taking.print' :'layouts.inventory.stock-taking.show'), compact('taking', 'dataByStore'));
	}

	public function freeze_stores(Request $request, $id, $internal_action=false){
		$taking = StockTaking::find($id);
		$store_ids = explode(',', $taking->stores);

		$freezeAction = $internal_action===false ? $request->action : $internal_action;

		$stores = \App\InventoryStore::whereIn('id', $store_ids)->update(['is_frozen'=>$freezeAction]);
		$taking->stores_frozen = $freezeAction;
		$taking->save();

		if($internal_action){
			return true;
		}

		$taking->updated_by = \Auth::user()->id;
		$taking->save();

		return redirect()->back()->with('success', 'Stores '.($request->action == "0" ? 'Un-Frozen' : 'Frozen').' successfully.');
	}

	public function make_adjustments($request, $id){
		$taking = StockTaking::find($id);

		// return json_encode($request->all());

		foreach($request->inventory_sub_category_id as $i=>$sub_cat_id){
			if(trim($request->available_quantity[$i]) != ""){

				$inventoryC = new \App\Http\Controllers\InventoryItemController;
				$subcat = \App\InventorySubCategories::find($sub_cat_id);

				$systemQuantity = $request->system_quantity[$i];
				$availableQuantity = $request->available_quantity[$i];

				$stock_taking_item = StockTakingSheet::where('stock_taking_id', $id)->where('inventory_sub_category_id', $sub_cat_id)
				->where('store_id', $request->store_id[$i])->where('slot_id', $request->slot_id[$i])->first();

				if($systemQuantity > $availableQuantity){
					$theQuantity = floatval($systemQuantity) - floatval($availableQuantity);
					if($theQuantity > 0){
						$req = new Request;
						$req->batchcode = $taking->code;
						$req->category_id = $subcat->inventory_category_id;
						$req->sub_category_id = $subcat->id;
						$req->quantity = $theQuantity;
						$req->po_number = $taking->code;
						$req->slot = $request->slot_id[$i];
						$req->store = $request->store_id[$i];
						$req->lot_no = $request->lot_no[$i];
						$req->transfer_to = systemVariables("stock_taking_department_id");
						$req->issued_to = \Auth::user()->id;

						$issueOut = $inventoryC->transfer($req, true);

						$stock_taking_item->adjusted_inventory_item_id = $issueOut->id;
					}
				}
				else{

					$theQuantity = floatval($availableQuantity) - floatval($systemQuantity);

					if($theQuantity > 0){

						$myRequest = new Request;
						$myRequest->batchcode = $taking->code;
						$myRequest->category_id = $subcat->inventory_category_id;
						$myRequest->sub_category_id = $subcat->id;
						$myRequest->supplier_id = systemVariables("internal_supplier_id");
						$myRequest->price = floatval($subcat->unit_price)*floatval($theQuantity);
						$myRequest->po_number = $taking->code;
						$myRequest->quantity = $theQuantity;
						$myRequest->slot = $request->slot_id[$i];
						$myRequest->store = $request->store_id[$i];
						$myRequest->lot_no = $request->lot_no[$i];
						// $myRequest->expiry = $items['expiry'][$i];
						$myRequest->inventory_department_id = systemVariables("stock_taking_department_id");
						$myRequest->override_location_id = $taking->location_id;

						$receivedItem = $inventoryC->add($myRequest, true);

						// return print_r($receivedItem);

						$stock_taking_item->adjusted_inventory_item_id = $receivedItem->id;
					}
				}

				$stock_taking_item->save();
			}
		}
		$taking->status = "Completed";
		$taking->completed_at = \Carbon\Carbon::now();
		$taking->updated_by = \Auth::user()->id;
		$taking->save();

		$this->freeze_stores($request, $id, 0);
		return redirect()->back()->with('success', 'Stock Taking Adjustment Completed.');
	}

	public function save_capture(Request $request, $id){
		// return "stores - ".count($request->store_name). " others - ".count($request->inventory_sub_category_id);
		$taking = StockTaking::find($id);

		if($request->has('set_approval')){
			$taking->status = "Awaiting Adjustment Approval";
			$taking->updated_by = \Auth::user()->id;
			$taking->approved_by = \Auth::user()->id;
			$taking->reviewed = true;
			$taking->save();

			return redirect()->back()->with('success', 'Stock-Taking adjustment request set.');
		}

		if($request->has('make_adjustments')){
			$type = $request->make_adjustments;

			if($type == "deny"){
				$taking->status = "Rejected";
				$taking->save();



				$this->freeze_stores($request, $id, 0);
				return redirect()->back()->with('success', 'Stock Taking Adjustment Rejected.');
			}

			return $this->make_adjustments($request, $id);
		}

		foreach($request->inventory_sub_category_id as $i=>$sub_cat_id){
			$sheetItem = StockTakingSheet::find($request->sid[$i]) ?? new StockTakingSheet;

			$sheetItem->stock_taking_id = $id;
			$sheetItem->inventory_sub_category_id = $sub_cat_id;
			$sheetItem->code = $request->code[$i];
			$sheetItem->store_name = $request->store_name[$i];
			$sheetItem->store_id = $request->store_id[$i];
			$sheetItem->slot_id = $request->slot_id[$i];
			$sheetItem->slot_name = $request->slot_name[$i];
			$sheetItem->lot_no = $request->lot_no[$i];
			$sheetItem->expiry = $request->expiry[$i];
			$sheetItem->system_quantity = $request->system_quantity[$i];
			$sheetItem->available_quantity = $request->available_quantity[$i];
			$sheetItem->comments = $request->comments[$i];

			$sheetItem->save();

			$taking->status = "In Quantity Capture";
		}

		$taking->save();

		return redirect()->back()->with('success', 'Stock Taking Sheet Quantities Updated.');
	}

	public function adjust_stock(Request $request, $catid, $subid){

		if(!$request->has('quantity') || $request->quantity == 0){
			return redirect()->back()->with('error', 'Quantity missing.');
		}

		if(!$request->has('slot_id')){
			return redirect()->back()->with('error', 'Store Slot was not selected.');
		}

		if(!$request->has('store_id')){
			return redirect()->back()->with('error', 'Store was not selected.');
		}

		$SUBCAT = \App\InventorySubCategories::find($subid);
		$inventoryC = new \App\Http\Controllers\InventoryItemController;

		$stockAdj = getNamingConventionCode("Stock Taking Adjustment", false, 'Stock-Adjustment');

		$myRequest = new Request;
		$myRequest->category_id = $catid;
		$myRequest->sub_category_id = $subid;
		$myRequest->supplier_id = null;
		$myRequest->price = floatval($SUBCAT->unit_price)*floatval($request->quantity);
		$myRequest->po_number = $stockAdj;
		$myRequest->quantity = $request->quantity;
		$myRequest->slot = $request->slot_id;
		$myRequest->item_brand_id = 0;
		$myRequest->lot_no = $stockAdj;
		$myRequest->store = $request->store_id;
		$myRequest->expiry = $request->expiry ?? '2099-12-31';
		$myRequest->date_of_manufacture = NULL;
		$myRequest->inventory_department_id = systemVariables('stock_taking_department_id');

		$receivedItem = $inventoryC->add($myRequest, true);

		return redirect()->back()->with('success', 'Stock Adjustment Complete.');
	}

	public function update(Request $request, $id=false)
	{
		$storesArr = ['ids' => [], 'names' => []];

		foreach (($request->stores ?? []) as $st) {
			$parts = explode(' zZ ', (string) $st, 2);
			$storeId = trim($parts[0]);

			if ($storeId === '') {
				continue;
			}

			$storeName = isset($parts[1]) ? trim($parts[1]) : null;
			if ($storeName === null || $storeName === '') {
				$storeName = InventoryStore::find($storeId)?->name;
			}

			if ($storeName === null || $storeName === '') {
				continue;
			}

			$storesArr['ids'][] = $storeId;
			$storesArr['names'][] = $storeName;
		}

		if (count($storesArr['ids']) === 0) {
			return redirect()->back()->with('error', 'Please select at least one store.');
		}

		$stores_ids = implode(',', $storesArr['ids']);
		$stores_names = implode(',', $storesArr['names']);

		$stockTaking = new StockTaking;
		if ($id && \Illuminate\Support\Str::isUuid((string) $id)) {
			$stockTaking = StockTaking::find($id) ?? new StockTaking;
		}
		$stockTaking->description = $request->description;
		if (trim((string) $stockTaking->code) == '') {
			$stockTaking->code = getNamingConventionCode('StockTaking', false, 'ST-');
		}
		$stockTaking->stores = $stores_ids;
		$stockTaking->store_names = $stores_names;
		$stockTaking->created_by = \Auth::user()->id;
		$stockTaking->inventory_location_id = getCurrentUserLocation()->id;
		$stockTaking->save();

		return redirect()->back()->with('success', 'Stock Taking Information Updated.');
	}
}