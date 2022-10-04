<?php

namespace App\Http\Controllers;

use App\SupplierQuote;
use Illuminate\Http\Request;

class SupplierQuoteController extends Controller
{
  public function edit(Request $request, $id){
		$quote = SupplierQuote::find($id);

		$quote->quote_amount = $request->amount;

		$quote->save();

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
		$quote = SupplierQuote::find($id);

		$quote->delete();

		return redirect()->back()->with('error', 'Supplier Quote Removed.');
	}
}
