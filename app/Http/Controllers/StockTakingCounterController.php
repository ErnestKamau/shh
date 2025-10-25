<?php

namespace App\Http\Controllers;

use App\StockTakingCounter;
use Illuminate\Http\Request;

class StockTakingCounterController extends Controller
{
	public function add(Request $request, $id){
		$counters = $request->counter;
		if(count($counters) == 0){
			return redirect()->back()->with('error', 'No counters were selected');
		}

		foreach($counters as $counter){
			$c = new StockTakingCounter;
			$c->stock_taking_id = $id;
			$c->counter_id = $counter;
			$c->save();
		}

		return redirect()->back()->with('success', 'Counters were added successfully.');
	}

	public function remove(Request $request, $id){
		$counter = StockTakingCounter::where('stock_taking_id', $id)->where('counter_id', $request->counter_id)->delete();
		return redirect()->back()->with('success', 'Counter removed.');
	}
}
