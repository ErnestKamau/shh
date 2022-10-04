<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\EquipmentOperator;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
class EquipmentOperatorController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}

	public function add(Request $request, $equipment){
		// return response()->json($request->operators, 200);
		foreach($request->operators as $operator){
			$op = EquipmentOperator::where('user_id', $operator)->where('equipment_id', $equipment)->first() ?? new EquipmentOperator;
			$op->user_id = $operator;
			$op->equipment_id = $equipment;
			$op->save();
		}
		return redirect()->back()->with('success', 'Equipment operator added!');
	}

	public function destroy($id){
		EquipmentOperator::find($id)->delete();
		return redirect()->back()->with('success', 'Equipment operator removed!');
	}
}
