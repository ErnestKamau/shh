<?php

namespace App\Http\Controllers;

use App\InventoryOrderItem;
use Illuminate\Http\Request;

class InventoryOrderItemController extends Controller
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
	public function getItems($field, $fieldID)
	{
		$orderItems = InventoryOrderItem::where($field, $fieldID)->get();

		return response()->json($orderItems, 200);
	}

	public function delete($order_item)
	{
		$orderItem = InventoryOrderItem::find($order_item);

		if($orderItem->fulfilled == 0){
			$orderItem->delete();
			return response()->json(array(
				"status"=> true,
				"message"=> "The order item has been deleted successfully!"
			), 200);
		}
		else{
			return response()->json(array(
				"status"=> false,
				"message"=> "Cannot Delete! This order item has already been fulfilled!"
			), 200);
		}
	}
}
