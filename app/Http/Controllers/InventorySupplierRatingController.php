<?php

namespace App\Http\Controllers;

use App\InventorySupplierRating;
use Illuminate\Http\Request;

class InventorySupplierRatingController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function add(Request $request){
		$rating = new InventorySupplierRating;
		$rating->supplier_id = $request->supplier_id;
		$rating->inventory_item_id = $request->inventory_item_id;
		$rating->rating = $request->rating;
		$rating->title = $request->title;
		$rating->comments = $request->comments;
		$rating->rating_by = \Auth::user()->id;

		$rating->save();

		$item = \App\InventoryItem::find($request->inventory_item_id);
		$item->status = 'approved';
		$item->save();

    return redirect()->back()->with('success', 'Supplier Rating Added.');
	}
}
