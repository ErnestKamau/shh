<?php

namespace App\Http\Controllers;

use App\RequisitionLocation;
use Illuminate\Http\Request;

class RequisitionLocationController extends Controller
{
	public function add(Request $request){
		$nloc = new RequisitionLocation;
		$nloc->name = $request->name;
		$nloc->save();

		return redirect()->back()->with('success', 'New Location Added');
	}
	public function remove($id){
		$nloc = RequisitionLocation::find($id);
		$nloc->delete();

		return redirect()->back()->with('error', 'Location removed successfully!');
	}
}
