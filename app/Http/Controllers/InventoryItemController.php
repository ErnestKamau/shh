<?php

namespace App\Http\Controllers;

use Session;
use App\InventoryItem;
use App\Datatables\Datatables;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MailController as Mailer;

class InventoryItemController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function index($type = '', $term = '')
	{
		viewableLocations();
		$items = InventoryItem::join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
			->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
			->leftJoin('item_brands as ib', function ($join) {
				$join->on('ib.id', 'inventory_items.item_brand_id');
				$join->on('ib.inventory_sub_category_id', 'inventory_items.inventory_sub_category_id');
			})
			->leftJoin('request_entities as re', 're.request_code', 'inventory_items.po_number')
			->join('inventory_stores as ins', 'ins.id', 'inventory_items.inventory_store_id')
			->join('inventory_store_slots as iss', 'iss.id', 'inventory_items.inventory_store_slot_id')
			->join('users as u', 'u.id', '=', 'inventory_items.created_by')
			->join('inventory_departments as id', 'id.id', '=', 'inventory_items.inventory_department_id')
			->where('ic.inventory_location_id', getCurrentUserLocation()->id)
			->selectRaw("isc.code, inventory_items.created_at, re.created_at as req_date, inventory_items.stock_in, id.name as department, inventory_items.stock_out, ic.name as category, isc.unit_type, isc.name as sub_category, COALESCE(ib.name, 'Non-Specific') as brand, ins.name as store, iss.name as slot, u.name as creator, u.email as creator_email, po_number as entity_code, NULL as comments, re.description, re.cost_center");

		$termOBJ = ['classification' => '', 'range' => [], 'category' => '', 'department' => ''];
		$typeParts = explode(',', $type);
		$termParts = explode(',', $term);

		if (count($typeParts) > 0) {
			if (in_array('department', $typeParts)) {
				$inx = array_search('department', $typeParts);
				$items = $items->where('re.cost_center', 'like', '%' . $termParts[$inx] . '%');

				$termOBJ['department'] = $termParts[$inx];
			}

			if (in_array('classification', $typeParts)) {
				$inx = array_search('classification', $typeParts);
				$items = $items->where('isc.item_classification', $termParts[$inx]);

				$termOBJ['classification'] = $termParts[$inx];
			}

			if (in_array('category', $typeParts)) {
				$inx = array_search('category', $typeParts);
				$items = $items->where('ic.id', $termParts[$inx]);
				$termOBJ['category'] = $termParts[$inx];
			}

			if (in_array('range', $typeParts)) {
				$inx = array_search('range', $typeParts);
				$range = explode('_', $termParts[$inx]);
				$items = $items->whereBetween('inventory_items.created_at', $range);
				$termOBJ['range'] = $range;
			} else {
				$range = [date('Y-m-01'), date('Y-m-30')];
				$items = $items->whereBetween('inventory_items.created_at', $range);
				$termOBJ['range'] = $range;
			}
		}

		$term = $termOBJ;
		$items = $items->distinct()->orderByDesc('req_date')->orderByDesc('inventory_items.created_at')->get();

		// return response()->json($term, 200);

		return view('layouts.inventory.activity.index', compact('items', 'term'));
	}

	public function activity_serverside(Request $request, $type = false, $term = 0)
	{
		$columns = array(
			array('db' => 'id',  'dt' => 0),
			array('db' => 'request_code',  'dt' => 1),
			array('db' => 'description', 'dt' => 2),
			array('db' => 'due_date', 'dt' => 3),
			array('db' => 'status',  'dt' => 4),
			array('db' => 'parent_id',   'dt' => 5),
			array('db' => 'parent_request_code',     'dt' => 6),
			array('db' => 'created_by',     'dt' => 7),
			array('db' => 'created_at',     'dt' => 8),
		);

		$items = InventoryItem::join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
			->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
			->join('users as u', 'u.id', '=', 'inventory_items.created_by')
			->leftJoin('request_entities as re', 're.request_code', 'inventory_items.po_number')
			->where('ic.inventory_location_id', getCurrentUserLocation()->id)
			->selectRaw('inventory_items.created_at,inventory_items.stock_in, inventory_items.stock_out, ic.name as category, isc.unit_type, isc.name as sub_category, isc.manufacturer, u.name as creator, u.email as creator_email, NULL as comments, re.description');

		if ($type != false && $term != 0) {
			$items = $items->where('isc.item_classification', $term);
		}

		// return response()->json($items->get(), 200);

		// $items =$items->groupBy('inventory_items.id');

		$results = new Datatables($items, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}

	public function add(Request $request, $internal = false)
	{
		// return json_encode($request->all());
		// Log::info("ADD Request ".json_encode($request, JSON_PRETTY_PRINT));

		$category = \App\InventoryCategories::find($request->category_id);
		$sub_category = \App\InventorySubCategories::find($request->sub_category_id);

		$cP = \substr($category->name, 0, 2) . "-" . substr($sub_category->name, 0, 2);

		$batchcode = getNamingConventionCode("Samples", false, strtoupper($cP));

		$item = new InventoryItem;

		$item->batch_code = $request->$batchcode ?? $batchcode;
		$item->inventory_category_id = $request->category_id;
		$item->inventory_sub_category_id = $request->sub_category_id;
		$item->po_number = $request->po_number ?? 'n/a';
		$item->supplier_id = $request->supplier_id;
		$item->created_by = \Auth::user()->id;
		$item->stock_in = $request->quantity;
		$item->expiry = $request->expiry;
		$item->date_of_manufacture = $request->date_of_manufacture;
		$item->barcode = $request->barcode ?? 'n/a';
		$item->price = $request->price;
		$item->inventory_store_id = $request->store ?? 0;
		$item->inventory_store_slot_id = $request->slot ?? 0;
		$item->item_brand_id = $request->item_brand_id ?? 0;
		$item->status = isset($request->requires_qc) ? 'pending' : 'approved';
		$item->inventory_location_id = $request->override_location_id ?? getCurrentUserLocation()->id;

		if ($internal) {
			$item->inventory_department_id = $request->inventory_department_id;
			$item->received_by = $request->received_by;
			$item->previous_batch_code = $request->previous_batch_code;
		}

		if ($request->storage_state_id) {
			$item->storage_state_id = $request->storage_state_id;
		} else {
			$material_type_id = $sub_category->material_type_id;
			$state = \App\ItemState::where('material_type_id', $material_type_id)
				->where('is_default', 1)->first();

			$item->storage_state_id = $state->id ?? null;
		}

		$item->lot_no = $request->lot_no;
		$item->save();

		$req = new Request;

		$req->item = $item->batch_code;

		$slotContentController = new InventoryStoreSlotContentController;
		$slotContent = $slotContentController->add($req, $request->slot, $request->store, true);

		calculateAvailableStock($sub_category->id);
		setItemReorderLevel($sub_category->id, $sub_category->reorder_level());

		if ($internal) {
			return $item;
		}

		if ($request->has('comments')) {
			$newNote = new \App\InventoryItemNote;
			$newNote->inventory_item_id = $item->id;
			$newNote->comments = $request->comments;

			if ($request->hasFile('file')) {
				$path = $request->file->path();
				$file = Storage::putFile('inventory_notes', new File($path));
				$file = explode('/', $file);

				$fName = '/storage/inventory_notes/' . urlencode(end($file));

				$newNote->document = (string) $fName;
			}

			$newNote->save();
		}

		return redirect()->back()->with('success', 'Inventory Items Added.');
	}

	public function transfer(Request $request, $internal = false)
	{
		$sub_category = \App\InventorySubCategories::find($request->sub_category_id);

		$item = new InventoryItem;
		$item->batch_code = $request->batchcode;
		$item->inventory_category_id = $request->category_id;
		$item->inventory_sub_category_id = $request->sub_category_id;
		$item->supplier_id = $request->supplier_id;
		$item->created_by = \Auth::user()->id;
		$item->stock_out = $request->quantity;
		$item->po_number = $request->po_number ?? 'n/a';
		$item->expiry = $request->expiry;
		$item->lot_no = $request->lot_no ?? null;
		$item->inventory_department_id = $request->transfer_to;
		$item->received_by = $request->issued_to;
		$item->inventory_location_id = getCurrentUserLocation()->id;
		$item->inventory_store_slot_id = $request->slot ?? 0;
		$item->inventory_store_id = $request->store ?? 0;
		$item->item_brand_id = $request->item_brand_id ?? 0;

		if ($request->storage_state_id) {
			$item->storage_state_id = $request->storage_state_id;
		} else {
			$material_type_id = $sub_category->material_type_id;
			$state = \App\ItemState::where('material_type_id', $material_type_id)
				->where('is_default', 1)->first();

			$item->storage_state_id = $state->id ?? null;
		}

		$item->save();

		calculateAvailableStock($sub_category->id);
		setItemReorderLevel($sub_category->id, $sub_category->reorder_level());

		if ($internal) {
			return $item;
		}
		return redirect()->back()->with('success', 'Inventory Items Transfered.');
	}

	public function item_disposal(Request $request)
	{
		// return response()->json($request->all(), 200);
		$category = \App\InventoryCategories::find($request->category_id);
		$sub_category = \App\InventorySubCategories::find($request->sub_category_id);

		$req = new Request;

		$cP = "IDS-" . \substr($category->name, 0, 2) . "-" . substr($sub_category->name, 0, 2);

		$req->category_id = $request->category_id;
		$req->batchcode = getNamingConventionCode("Samples", false, strtoupper($cP));
		$req->sub_category_id = $request->sub_category_id;
		$req->supplier_id = null;
		$req->price = 0;
		$req->transfer_to = $request->transfer_to;
		$req->quantity = $request->quantity;
		$req->slot = $request->store_slot_id;
		$req->store = $request->store_id;

		$newItem = $this->transfer($req, true);

		$disposalReason = $request->reason;

		if ($request->reason == "Other") {
			$otherReason = addDisposalReason($request->other_reason);
			$disposalReason = $otherReason->description;
		}

		$newNote = new \App\InventoryItemNote;
		$newNote->inventory_item_id = $newItem->id;
		$newNote->comments = $request->comments;
		$newNote->title = $disposalReason ?? '-';

		if ($request->hasFile('file')) {
			$path = $request->file->path();
			$file = Storage::putFile('inventory_notes', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/inventory_notes/' . urlencode(end($file));

			$newNote->document = (string) $fName;
		}

		$newNote->save();

		calculateAvailableStock($sub_category->id);
		setItemReorderLevel($sub_category->id, $sub_category->reorder_level());

		return redirect()->back()->with('success', 'Item Disposal Completed.');
	}

	public function stock_keeping(Request $request)
	{
		// return response()->json($request->all(), 200);
		$category = \App\InventoryCategories::find($request->category_id);
		$sub_category = \App\InventorySubCategories::find($request->sub_category_id);

		$req = new Request;

		$cP = "STK-" . \substr($category->name, 0, 2) . "-" . substr($sub_category->name, 0, 2);

		$req->category_id = $request->category_id;
		$req->batchcode = getNamingConventionCode("Samples", false, strtoupper($cP));
		$req->sub_category_id = $request->sub_category_id;
		$req->supplier_id = null;
		$req->price = 0;

		$available = $sub_category->available()['available'];
		$diff = floatval($available) - floatval($request->quantity);

		$req->quantity = abs($diff);

		if ($diff >= 0) {
			$req->transfer_to = $request->transfer_to;
			$newItem = $this->transfer($req, true);
		} else {
			$req->inventory_department_id = $request->transfer_to;
			$newItem = $this->add($req, true);
		}

		$newNote = new \App\InventoryItemNote;
		$newNote->inventory_item_id = $newItem->id;
		$newNote->comments = $request->comments;

		if ($request->hasFile('file')) {
			$path = $request->file->path();
			$file = Storage::putFile('inventory_notes', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/inventory_notes/' . urlencode(end($file));

			$newNote->document = (string) $fName;
		}

		$newNote->save();

		calculateAvailableStock($sub_category->id);
		setItemReorderLevel($sub_category->id, $sub_category->reorder_level());
		return redirect()->back()->with('success', 'Stock Keeping Completed.');
	}

	public function return_2_store(Request $request)
	{
		// return response()->json($request->all(), 200);


		$item = json_decode($request->item_to_return);
		$req = new Request;

		$req->category_id = $item->inventory_category_id;
		$req->sub_category_id = $item->inventory_sub_category_id;
		$req->supplier_id = null;
		$req->quantity = $request->quantity;
		$req->expiry = $request->expiry ?? $item->expiry;
		$req->price = 0;
		$req->inventory_department_id = $request->transfer_to;
		$req->received_by = $request->received_by;
		$req->previous_batch_code = $item->batch_code;
		$req->slot = $request->slot;
		$req->store = $request->store;

		$newItem = $this->add($req, true);

		$newNote = new \App\InventoryItemNote;
		$newNote->inventory_item_id = $newItem->id;
		$newNote->comments = $request->comments;

		if ($request->hasFile('file')) {
			$path = $request->file->path();
			$file = Storage::putFile('inventory_notes', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/inventory_notes/' . urlencode(end($file));

			$newNote->document = (string) $fName;
		}

		$newNote->save();

		$sub_category = \App\InventorySubCategories::find($request->sub_category_id);
		calculateAvailableStock($sub_category->id);
		setItemReorderLevel($sub_category->id, $sub_category->reorder_level());
		return redirect()->back()->with('success', 'Items Returned to store.');
	}

	public function sendReorderNotifications()
	{
		$restockItems = getRestockNotifications();

		// return response()->json($restockItems);

		$tbody = '<tbody>';
		$id = 0;
		foreach ($restockItems['items'] as $item) {
			$id++;
			$tbody .= '<tr>
				<td>' . $id . '</td>
				<td>' . $item['url_name'] . '</td>
				<td><a class="dropdown-item" href="' . $item["alert_url"] . '">
				<small>' . $item['alert_url'] . '</small>
			</a></td>
			</tr>';
		}
		$tbody .= '</tbody>';
		$body = 'Hi,<br><br>
			There are ' . $restockItems['count'] . ' items in your inventory that require restocking. 
			<h5>Item List</h5>
			<table style="border-collapse: collapse; font-size: 12px"
				<thead>
					<tr>
					<th>No</th>
					<th>Name</th>
					<th>Link</th>
					</tr>
				</thead>
				' . $tbody . '
			</table>
			<br>Regards,<br>
			Inventory System
			';

		// return $body;

		$contacts = [];
		$users = getInventoryWorkflowUsers('procurement');

		foreach ($users as $s) {
			$contacts[] = $s->email;
		}

		$contacts = array_unique($contacts);

		$mailData = array(
			'contacts' => array_filter($contacts),
			'body' => $body,
			'subject' => '[Inventory Reorder Notification] '.$restockItems['count'].' items require restocking.'
		);

		$mailer = new Mailer;

		$mailer->html_email($mailData, 'default');

		return response()->json(["status"=>true]);
	}
}
