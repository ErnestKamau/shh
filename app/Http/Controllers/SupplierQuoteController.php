<?php

namespace App\Http\Controllers;

use App\EntityNote;
use App\SupplierQuote;
use Illuminate\Http\Request;

class SupplierQuoteController extends Controller
{
  public function edit(Request $request, $id){
		$ids = explode(',', $id);
		$items = [];
		$suppliers = [];
		$previousAmount = 0;
		foreach($ids as $i){
			$quote = SupplierQuote::find($i)->load(['supplier', 'request_item.sub_category']);

			$items[] = $quote->request_item->sub_category->name;
			$suppliers[] = $quote->supplier->name;
			$previousAmount = $quote->quote_amount;
			$quote->quote_amount = $request->amount;
			$quote->save();
		}

		$note = new EntityNote;
		$note->type = "Quote Edit Reason";
		$note->title = "Amount changed from ".$previousAmount." to ".$quote->quote_amount;
		$note->description = "Item(s) ".implode(", ",array_unique($suppliers))." from ".implode(", ",array_unique($suppliers))." quote changes <br>Reason <br>".$request->reason;
		$note->model = $entity->request_type;
		$note->model_id = $entity->id;
		$note->created_by = \Auth::user()->id;
		$note->save();

		return redirect()->back()->with('success', 'Supplier Quote Updated.');
	}

	public function undo_supplier_award(Request $request, $id){
		$quote = SupplierQuote::find($id);
		$quote->is_awarded = 0;
		$quote->awarded_at = null;
		$quote->save();

		SupplierQuote::where('request_id', $quote->request_id)->where('request_item_id', $quote->request_item_id)->update(
			[
				'is_awarded'=>0,
				'awarded_at'=>null
			]
		);

		return redirect()->back()->with('success', 'Supplier Awarding Undone.');
	}

  public function remove(Request $request, $id){
		$ids = explode(',', $id);

		foreach($ids as $i){
			$quote = SupplierQuote::find($i);
			$quote->delete();
		}

		return redirect()->back()->with('error', 'Supplier Quote Removed.');
	}
}
