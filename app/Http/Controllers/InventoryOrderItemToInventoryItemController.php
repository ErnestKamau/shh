<?php

namespace App\Http\Controllers;

use App\InventoryOrder;
use App\InventoryOrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\InventoryItemController;
use App\InventoryOrderItemToInventoryItem;

class InventoryOrderItemToInventoryItemController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function acceptItems(Request $request, $order_id)
	{
		// return response()->json($request->all(), 200);

		$items = $request->items ?? array('receive'=>array());
		$theOrder = InventoryOrder::find($order_id);

		foreach($items['receive'] as $i=>$received){
			if($received == "1"){
				$order_item = $items['order_item_id'][$i];
				$quantity = $items['quantity'][$i];
				$price = $items['price'][$i];
				$slot = $items['slot'][$i];
				$expiry = $items['expiry'][$i] ?? null;
				$qc = $items['requires_qc'][$i] == "1" ? true : false;

				$sub_cat = explode(" ", $items['sub_category_id'][$i]);

				$store_slot = explode(" ", $slot);

				$myRequest = new Request;
				$myRequest->category_id = $sub_cat[0];
				$myRequest->sub_category_id = $sub_cat[1];
				$myRequest->supplier_id = $theOrder->supplier_id;
				$myRequest->price = $price;
				$myRequest->po_number = $theOrder->order_number;
				$myRequest->quantity = $quantity;
				$myRequest->slot = $store_slot[1];
				$myRequest->store = $store_slot[0];
				$myRequest->expiry = $expiry;
				$myRequest->inventory_department_id = 0;
				if($qc){ //If QC is true
					$myRequest->requires_qc = $qc;
				}

				$inventoryController = new InventoryItemController;

				$inventoryItem = $inventoryController->add($myRequest, true);

				$orderToInventory = new InventoryOrderItemToInventoryItem;
				$orderToInventory->inventory_order_id = $order_id;
				$orderToInventory->inventory_item_id = $inventoryItem->id;
				$orderToInventory->inventory_order_item_id = $order_item;
				$orderToInventory->save();


				$theOrderItem = InventoryOrderItem::find($order_item);
				$theOrderItem->fulfilled = 1;
				$theOrderItem->save();
			}
		}

		$theOrder->status = $theOrder->fulfilment_status();
		$theOrder->save();

    return redirect()->back()->with('success', 'Items Accepted.');
	}
}
