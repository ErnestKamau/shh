<?php

namespace App\Http\Controllers;

use App\InventoryOrder;
use App\InventoryOrderItem;
use Illuminate\Http\Request;
use App\Datatables\Datatables;

class InventoryOrderController extends Controller
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
	public function add(Request $request, $supplier=false)
	{
		// return response()->json($request->all(), 200);

		$pref = env('ORG_PREFIX', 'LIMS')."-";
		$newOrder = new InventoryOrder;
		$newOrder->order_number = getNamingConventionCode("ORDERS", false, $pref);
		$newOrder->supplier_id = $supplier == false ? $request->supplier_id : $supplier;
		$newOrder->comments = $request->order_comments ?? null;
		$newOrder->company_id = getUserCompany();
		$newOrder->created_by = \Auth::user()->id;

		$newOrder->save();

		$items = $request->items;

		foreach($items['sub_category_id'] as $i=>$item){
			$ids = explode(' ', $item);

			$orderItem = new InventoryOrderItem;
			$orderItem->inventory_order_id = $newOrder->id;
			$orderItem->inventory_category_id = $ids[0];
			$orderItem->inventory_sub_category_id = $ids[1];
			$orderItem->quantity = $items['quantity'][$i];

			$orderItem->save();
		}

		return redirect()->back()->with('success', 'Order has been created.');
	}

	public function edit(Request $request, $supplier, $order_id)
	{
		$pref = env('ORG_PREFIX', 'LIMS')."-";
		$newOrder = InventoryOrder::find($order_id);
		$newOrder->comments = $request->order_comments ?? null;
		// $newOrder->order_number = getNamingConventionCode("ORDERS", false, $pref);
		$newOrder->company_id = getUserCompany();
		$newOrder->created_by = \Auth::user()->id;

		$newOrder->save();

		$items = $request->items;

		foreach($items['sub_category_id'] as $i=>$item){
			$ids = explode(' ', $item);

			$orderItem = InventoryOrderItem::find($items['order_item_id'][$i]) ?? new InventoryOrderItem;
			$orderItem->inventory_order_id = $newOrder->id;
			$orderItem->inventory_category_id = $ids[0];
			$orderItem->inventory_sub_category_id = $ids[1];
			$orderItem->quantity = $items['quantity'][$i];

			$orderItem->save();
		}

		return redirect()->back()->with('success', 'Order has been created.');
	}

	public function server_side(Request $request, $field=false, $fieldID=false){
		$columns = array(
			array( 'db' => 'comments',  'dt' => -2 ),
			array( 'db' => 'supplier_id',  'dt' => -2 ),
			array( 'db' => 'id', 'dt' => -1 ),
			array( 'db' => 'order_number', 'dt' => 0 ),
			array( 'db' => 'supplier',  'dt' => 1 ),
			array( 'db' => 'creator',   'dt' => 2 ),
			array( 'db' => 'status',     'dt' => 3 ),
			array( 'db' => 'created_at',     'dt' => 4 ),
			array( 'db' => 'order_items',     'dt' => 5 ),
		);

		$orders = InventoryOrder::join('suppliers as s', 's.id', '=', 'inventory_orders.supplier_id')
			->join('users as u', 'u.id', '=', 'inventory_orders.created_by')
			->join('inventory_order_items as ioi', 'ioi.inventory_order_id', '=', 'inventory_orders.id')
			->selectRaw('inventory_orders.id, inventory_orders.comments, inventory_orders.order_number, s.id as supplier_id, s.name as supplier, u.name as creator, inventory_orders.status, inventory_orders.created_at, count(ioi.id) as order_items')
			->groupBy('inventory_orders.id', 'order_number', 'comments', 's.id', 's.name', 'u.name', 'status', 'inventory_orders.created_at');

		if($field){
			$orders = $orders->where('inventory_orders.'.$field, $fieldID);
		}

		$results = new Datatables($orders, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}

	public function server_side_po(Request $request, $field=false, $fieldID=false, $type='Purchase Orders'){
		$columns = array(
			array( 'db' => 'id',  'dt' => 0),
			array( 'db' => 'request_code',  'dt' => 1 ),
			array( 'db' => 'description', 'dt' => 2 ),
			array( 'db' => 'due_date', 'dt' => 3 ),
			array( 'db' => 'status',  'dt' => 4 ),
			array( 'db' => 'parent_id',   'dt' => 5 ),
			array( 'db' => 'parent_request_code',     'dt' => 6 ),
			array( 'db' => 'created_by',     'dt' => 7 ),
			array( 'db' => 'created_at',     'dt' => 8 ),
		);

		$orders = \App\RequestEntity::join('request_entities as re', 'request_entities.parent_request_id', 're.id')
			->join('users as u', 'u.id', '=', 'request_entities.created_by')
			->where('request_entities.request_type', $type)
			->selectRaw('request_entities.id, request_entities.created_at, request_entities.request_code, request_entities.description, request_entities.due_date, request_entities.status, re.id as parent_id, re.request_code as parent_request_code, u.name as created_by');
		if($field){
			$orders = $orders->where('request_entities.'.$field, $fieldID);
		}

		$results = new Datatables($orders, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}
}
