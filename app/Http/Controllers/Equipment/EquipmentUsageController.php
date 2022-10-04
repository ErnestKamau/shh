<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\EquipmentUsage;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
class EquipmentUsageController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}

	public function update(Request $request, $id, $equipment)
	{
		$existingUsage = EquipmentUsage::find($id);
		$usage = $existingUsage ?? new EquipmentUsage;

		if(isset($existingUsage->operator) && $existingUsage->operator != \Auth::user()->id){
			return redirect()->back()->with('error', 'You are not the same operator that created this sample test!');
		}

		if(isset($existingUsage->operator)){
			$usage->end_date = date('Y-m-d H:i:s');
			$endMsg = "This Sample Analysis is marked as completed!";
		}

		$usage->operator = \Auth::user()->id;
		$usage->sample_header = $request->sample;
		$usage->equipment_id = $equipment;
		$usage->save();

		return redirect()->back()->with('success', $endMsg ?? 'Sample Analysis test started successfully');
	}
}
