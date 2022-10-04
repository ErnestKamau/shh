<?php

namespace App\Http\Controllers;

use App\MethodReagent;
use Illuminate\Http\Request;

class MethodReagentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function modify(Request $request, $method_id){
		$reagent_ids = array();
		foreach($request->reagent as $i=>$r){
			$reagent = MethodReagent::where('method_id', $method_id)->where('inventory_sub_category_id', $r)->first() ?? new MethodReagent;
			$reagent->method_id = $method_id;
			$reagent->inventory_sub_category_id = $r;
			$reagent->reporting_unit = $request->unit_type[$i];
			$reagent->quantity = $request->quantity[$i];
			$reagent->save();

			$reagent_ids[] = $r;
		}
		// remove all reagents that are missing from the posted list of reagents
		MethodReagent::whereNotIn('inventory_sub_category_id', $reagent_ids)->where('method_id', $method_id)->delete();

		return redirect()->back()->with('success', 'Method Reagents Updated!');
	}
}
