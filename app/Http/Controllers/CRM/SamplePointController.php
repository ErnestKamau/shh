<?php

namespace App\Http\Controllers\CRM;

use App\Models\CRM\SamplePoint;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
class SamplePointController extends Controller
{
	public function __construct()
	{
	  $this->middleware('auth');
	}

	public function add(Request $request){
		$product = new SamplePoint;
		$product->name = $request->name;
    	$product->crm_company_unit_id = $request->unit;
		$product->active = $request->active ?? 0;

		$longitude = (string)$request->long;
		$latitude = (string)$request->lat;
		$gps = $longitude.','.$latitude;
		// return response()->json($gps, 200);
		$product->gps = (string) $gps;
		 

		$product->save();

		if ($request->wantsJson()) {
			return response()->json([
				'id' => $product->id,
				'name' => $product->name,
				'success' => 'Added successfully.'
			]);
		}

    return redirect()->back()->with('success', 'Added successfully.');
	}

	public function edit(Request $request, $id){
		$product = SamplePoint::find($id);
		$product->name = $request->name;
    $product->crm_company_unit_id = $request->unit;
		$product->active = $request->active ?? 0;

		$product->save();

    return redirect()->back()->with('success', 'Edit was successful.');
	}
}
