<?php

namespace App\Http\Controllers;

use App\ItemState;
use Illuminate\Http\Request;

class ItemStateController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function update(Request $request, $id)
	{
		if($request->has('state_item_id')){
			$state = ItemState::find($request->state_item_id);
		}
		else{
			$state = new ItemState;
		}

		$state->name = $request->name;
		$state->uom = $request->uom;
		$state->material_type_id = $id;
		$state->is_default = $request->is_default ?? 0;

		if(!$request->has('state_item_id')){
			$state->location_id = getCurrentUserLocation()->id;
		}

		$state->save();

		if($request->has('is_default')){
			ItemState::where('material_type_id', $id)->whereNotIn('id', [$state->id])->update(['is_default'=>0]);
		}

		return redirect()->back()->with('success', 'Item States Updated.');
	}

	public function delete(Request $request){
		$state = ItemState::find($request->state_item_id)->delete();
		return redirect()->back()->with('success', 'Item State Removed.');
	}
}
