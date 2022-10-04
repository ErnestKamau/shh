<?php

namespace App\Http\Controllers;

use App\UnitOfMeasureConversion;
use Illuminate\Http\Request;

class UnitOfMeasureConversionController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}

	public function update(Request $request, $id)
	{
		if($request->has('conversion_item_id')){
			$conversion = UnitOfMeasureConversion::find($request->conversion_item_id);
		}
		else{
			$conversion = new UnitOfMeasureConversion;
		}
		$conversion->uom1 = $request->uom1;
		$conversion->uom2 = $request->uom2;
		$conversion->conversion = $request->conversion;
		$conversion->material_type_id = $id;
		$conversion->location_id = getCurrentUserLocation()->id;

		$conversion->save();

		return redirect()->back()->with('success', 'Item Conversions Updated.');
	}

	public function delete(Request $request){
		$conversion = UnitOfMeasureConversion::find($request->conversion_item_id)->delete();
		return redirect()->back()->with('success', 'Item Conversion Removed.');
	}
}
