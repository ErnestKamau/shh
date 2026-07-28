<?php

namespace App\Http\Controllers;

use App\InventoryItem;
use App\StockTransfer;
use App\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockTransferController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	/**
	 * Resolve a stock transfer by UUID route param, or a new model for create flows.
	 * Legacy "new" links used time() as a placeholder ID; that is not a valid UUID.
	 */
	protected function findTransferOrNew(mixed $id): StockTransfer
	{
		if (is_string($id) && Str::isUuid($id)) {
			return StockTransfer::find($id) ?? new StockTransfer;
		}

		return new StockTransfer;
	}

	public function index()
	{
		$transfers = StockTransfer::query()
			->leftJoin('inventory_locations as il', 'il.id', '=', 'stock_transfers.inventory_location_id')
			->leftJoin('inventory_departments as ind', 'ind.id', '=', 'stock_transfers.department_id')
			->leftJoin('users as u', 'u.id', '=', 'stock_transfers.created_by')
			->where('stock_transfers.inventory_location_id', getCurrentUserLocation()->id)
			->selectRaw('stock_transfers.*, ind.name as department, il.name as location, u.name as user_name')
			->get();

		return view('layouts.inventory.stock-transfer.index', compact('transfers'));
	}

	public function show($id)
	{
		$transfer = $this->findTransferOrNew($id);
		$transfer_items = (is_string($id) && Str::isUuid($id))
			? StockTransferItem::where('stock_transfer_id', $id)->get()
			: collect();
		return view('layouts.inventory.stock-transfer.show', compact('transfer', 'transfer_items'));
	}

	public function transfer_items($request, $id){
		// return '<pre>'.json_encode($request->all(), JSON_PRETTY_PRINT).'</pre>';
		$items = $request->items;
		$transfer = $this->findTransferOrNew($id);
		if (!$transfer->exists) {
			return redirect()->back()->with('error', 'Stock Transfer not found.');
		}
		$companyDetails = getCompanyDetails();

		$itemsTransferredArray = array("out"=>array(), "in"=>array());
		$contactEmailStores = array();

		foreach($items['transfer_item_id'] as $i=>$tid){
			$inventoryC = new \App\Http\Controllers\InventoryItemController;

			$item = (is_string($tid) && Str::isUuid($tid))
				? (StockTransferItem::find($tid) ?? new StockTransferItem)
				: new StockTransferItem;

			$localSubCat = \App\InventorySubCategories::find($items['source_sub_category_id'][$i]);
			$targetSubCat = \App\InventorySubCategories::find($items['target_sub_category_id'][$i]);

			$batchCode = getNamingConventionCode("Internal-Transfer", false, 'INTERNAL-TRANSFER-');

			$req = new Request;
			$req->batchcode = $transfer->code;
			$req->category_id = $localSubCat->inventory_category_id;
			$req->sub_category_id = $localSubCat->id;
			$req->quantity = $items['transfer_quantity'][$i];
			$req->slot = $items['source_slot_id'][$i];
			$req->store = $items['source_store_id'][$i];
			$req->transfer_to = systemVariables("inter_store_department_id");
			$req->issued_to = \Auth::user()->id;
			$req->storage_state_id = null;
			$req->po_number = $transfer->code;

			$itemsTransferredArray['out'][] = array(
				"item"=>$localSubCat->code." - ".$localSubCat->name,
				"item_id"=>$localSubCat->id,
				"category_id"=>$localSubCat->inventory_category_id,
				"quantity"=>$items['transfer_quantity'][$i],
				"slot" => $items['source_slot_id'][$i],
				"store" => $items['source_store_id'][$i]
			);

			$contactEmailStores[] = $items['source_store_id'][$i];

			$issueOut = $inventoryC->transfer($req, true);

			$item->local_inventory_item_id = $issueOut->id;

			$transferrableQuantity = $items['transfer_quantity'][$i];

			// if($items['target_state_id'][$i] != $items['source_state_id'][$i]){
			// 	$conversion = \App\UnitOfMeasureConversion::where('unit_of_measure_conversions.material_type_id', $targetSubCat->material_type_id)
			// 		->join('item_states as s1', function($join){
			// 			$join->on('s1.material_type_id', 'unit_of_measure_conversions.material_type_id');
			// 			$join->on('s1.uom', 'unit_of_measure_conversions.uom1');
			// 		})
			// 		->join('item_states as s2', function($join){
			// 			$join->on('s2.material_type_id', 'unit_of_measure_conversions.material_type_id');
			// 			$join->on('s2.uom', 'unit_of_measure_conversions.uom2');
			// 		})
			// 		->selectRaw('unit_of_measure_conversions.conversion')
			// 		->where('s1.id', $items['source_state_id'][$i])
			// 		->where('s2.id', $items['target_state_id'][$i])->first();

			// 	if(isset($conversion->conversion)){
			// 		$transferrableQuantity = floatval($transferrableQuantity)*floatval($conversion->conversion);
			// 	}
			// 	else{
			// 		$conversion = \App\UnitOfMeasureConversion::where('unit_of_measure_conversions.material_type_id', $targetSubCat->material_type_id)
			// 		->join('item_states as s1', function($join){
			// 			$join->on('s1.material_type_id', 'unit_of_measure_conversions.material_type_id');
			// 			$join->on('s1.id', 'unit_of_measure_conversions.uom2');
			// 		})
			// 		->join('item_states as s2', function($join){
			// 			$join->on('s2.material_type_id', 'unit_of_measure_conversions.material_type_id');
			// 			$join->on('s2.id', 'unit_of_measure_conversions.uom1');
			// 		})
			// 		->selectRaw('unit_of_measure_conversions.conversion')
			// 		->where('s1.id', $items['source_state_id'][$i])
			// 		->where('s2.id', $items['target_state_id'][$i])->first();

			// 		if(isset($conversion->conversion)){
			// 			$transferrableQuantity = floatval($transferrableQuantity)*floatval($conversion->conversion);
			// 		}
			// 	}
			// }

			$myRequest = new Request;
			$myRequest->batchcode = $transfer->code;
			$myRequest->category_id = $targetSubCat->inventory_category_id;
			$myRequest->sub_category_id = $targetSubCat->id;
			$myRequest->supplier_id = systemVariables("internal_supplier_id");
			$myRequest->price = floatval($targetSubCat->unit_price)*floatval($items['transfer_quantity'][$i]);
			$myRequest->po_number = $transfer->code;
			$myRequest->quantity = $transferrableQuantity;
			$myRequest->slot = $items['target_slot_id'][$i];
			$myRequest->store = $items['target_store_id'][$i];
			$myRequest->expiry = $items['expiry'][$i];
			$myRequest->inventory_department_id = $transfer->department_id;
			$myRequest->override_location_id = $transfer->location_id;
			$myRequest->storage_state_id = $items['target_state_id'][$i] ?? null;

			$itemsTransferredArray['in'][] = array(
				"item"=>$targetSubCat->code." - ".$targetSubCat->name,
				"item_id"=>$targetSubCat->id,
				"category_id"=>$targetSubCat->inventory_category_id,
				"quantity"=>$transferrableQuantity,
				"slot" => $items['target_slot_id'][$i],
				"store" => $items['target_store_id'][$i]
			);
			$contactEmailStores[] = $items['target_store_id'][$i];

			$receivedItem = $inventoryC->add($myRequest, true);

			// return print_r($receivedItem);

			$item->target_inventory_item_id = $receivedItem->id;

			$item->save();
		}

		// return json_encode($itemsTransferredArray, JSON_PRETTY_PRINT);

		$ulOut = "<ol>";
		foreach($itemsTransferredArray['out'] as $iTA){
			$route = route('show-inventory-items', ['category'=>$iTA['category_id'], 'id'=>$iTA['item_id']]);
			$ulOut .="<li><a href='".$route."'>".$iTA['item']."</a></li>";
		}
		$ulOut .="</ol>";

		$ulIn = "<ol>";
		foreach($itemsTransferredArray['in'] as $iTA){
			$route = route('show-inventory-items', ['category'=>$iTA['category_id'], 'id'=>$iTA['item_id']]);
			$ulIn .="<li><a href='".$route."'>".$iTA['item']."</a></li>";
		}
		$ulIn .="</ol>";

		$body = '
			Hi,<br>
			<p>
				These inventory items were transferred in the Stock Transfer '.$transfer->code.':<br>
				'.$ulOut.'
			</p>
			<p>
				The transfers affected the following inventory items:
				'.$ulIn.'
			</p>

			Regards,<br>
			'.$companyDetails['name'].'
		';


		$subject = '['.$companyDetails["name"].'] Stock Transfer Notifications - '.$transfer->code;

		$emails = \App\InventoryStoreContact::join('users as u', 'u.id', 'inventory_store_contacts.user_id')
			->selectRaw('u.email')->whereIn('inventory_store_contacts.store', $contactEmailStores)->get()->pluck('email')->toArray();

		// return json_encode($emails, JSON_PRETTY_PRINT);

		$emails = array_unique($emails);

		notify_user($body, $emails, $subject);

		$transfer->status = "Completed";
		$transfer->save();

		return redirect()->back()->with('success', 'Stock Transfer Completed.');
	}

	public function save_items($request, $id){
		$items = $request->items;

		$transfer = $this->findTransferOrNew($id);
		if (!$transfer->exists) {
			return redirect()->back()->with('error', 'Stock Transfer not found.');
		}

		foreach($items['transfer_item_id'] as $i=>$tid){
			$item = (is_string($tid) && Str::isUuid($tid))
				? (StockTransferItem::find($tid) ?? new StockTransferItem)
				: new StockTransferItem;
			$item->stock_transfer_id = $transfer->id;
			$item->local_item_id = $items['source_sub_category_id'][$i];
			$item->local_inventory_item_name = $items['source_sub_category_name'][$i];
			$item->local_store_id = $items['source_store_id'][$i];
			$item->local_store_slot_id = $items['source_slot_id'][$i];
			$item->target_item_id = $items['target_sub_category_id'][$i];
			$item->target_inventory_item_name = $items['source_sub_category_name'][$i];
			$item->target_store_id = $items['target_store_id'][$i];
			$item->target_store_slot_id = $items['target_slot_id'][$i];
			$item->target_quantity = $items['transfer_quantity'][$i];
			$item->local_uom = $items['source_uom'][$i] ?? null;
			$item->target_uom = $items['transfer_uom'][$i] ?? null;
			$item->local_lot_no = $items['source_lot_number'][$i];
			$item->target_lot_no = $items['target_lot_number'][$i];
			$item->expiry = $items['expiry'][$i] ?? '2099-12-31';

			$item->save();
		}

		$transfer->status = "Transfer Items Updated";
		$transfer->save();

		return redirect()->back()->with('success', 'Stock Transfer Items updated.');
	}

	public function update_items(Request $request, $id){
		// return response()->json($request->all(), 200);

		if($request->has('save_items')){
			return $this->save_items($request, $id);
		}

		if($request->has('transfer_items')){
			return $this->transfer_items($request, $id);
		}

	}

	public function update(Request $request, $id)
	{
		$transfer = $this->findTransferOrNew($id);
		$transfer->location_id = $request->location_id ?? \Auth::user()->location_id;
		$transfer->department_id = $request->department_id ?? \Auth::user()->department_id;
		$transfer->description = $request->description;

		if(!isset($transfer->created_at)){
			$transfer->created_by = \Auth::user()->id;
			$transfer->inventory_location_id = getCurrentUserLocation()->id;
			$transfer->code = getNamingConventionCode("Stock-Taking", false, 'ST-');
		}

		$transfer->save();
		return redirect()->route('stock-transfer-sheet', ['id'=>$transfer->id])->with('success', 'Stock Transfer details updated.');
	}

	public function getDepartments($module, $request){
		$module = 'organizational';
		return \App\InventoryDepartment::where('module', $module)->where('location_id', $request->location)
		->get();
	}

	public function getSlotStock($request){
		$items = \App\InventoryItem::join('inventory_stores as s', 's.id', 'inventory_items.inventory_store_id')
			->join('inventory_store_slots as ss', function($join){
				$join->on('ss.id', '=', 'inventory_items.inventory_store_slot_id');
				$join->on('s.id', '=', 'ss.inventory_store_id');
			})
			->selectRaw('SUM(inventory_items.stock_in) as stock_in, SUM(inventory_items.stock_out) as stock_out')
			->where('s.inventory_location_id', $request->location)
			->where('inventory_items.inventory_sub_category_id', $request->item_id)
			->where('inventory_items.inventory_store_slot_id', $request->slot_id)->first();

		$available = floatval($items->stock_in) - floatval($items->stock_out);
		// return $items;
		return array(
			"available"=>$available < 0 ? 0 : $available,
			"stock_out"=>floatval($items->stock_out),
			"stock_in"=>floatval($items->stock_in),
		);
	}

	public function delete_item(Request $request){
		$transferItemId = $request->transfer_item_id;
		if (!is_string($transferItemId) || !Str::isUuid($transferItemId)) {
			return response()->json(array("status"=>false, "message"=>"Invalid item id."), 422);
		}

		$item = StockTransferItem::find($transferItemId);
		if (!$item) {
			return response()->json(array("status"=>false, "message"=>"Item not found."), 404);
		}

		$item->delete();

		return response()->json(array("status"=>true, "message"=>"Item deleted successfully!"), 200);
	}

	public function getMaterialTypeStates(Request $request){
		return \App\ItemState::where('material_type_id', $request->material_type_id)
			->join('reporting_units as ru', 'item_states.uom', 'ru.id')
			->selectRaw('item_states.id, item_states.name, ru.name as uom')
			->orderBy('item_states.name', 'asc')->get();
	}

	public function getJson(Request $request){
		$type = $request->type;

		if($type == "departments"){
			$module = 'organizational';
			$response = $this->getDepartments($module, $request);
		}

		if($type == 'stock'){
			$response = $this->getSlotStock($request);
		}

		return response()->json($response, 200);
	}
}