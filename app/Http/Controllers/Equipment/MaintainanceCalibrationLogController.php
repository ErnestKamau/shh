<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\PartsRepaired;
use App\Models\Equipments\MaintainanceCalibrationLog;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\EquipmentAttachment;

use App\Http\Controllers\Controller;
class MaintainanceCalibrationLogController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request, $id)
	{
		$log = new MaintainanceCalibrationLog;
		$log->equipment_id = $id;
		// $log->service_provider = isset($request->service_provider) && $request->service_provider != '' ? $request->service_provider : '';
		$log->notes = $request->notes;
		$log->description = $request->description;
		$log->type = $request->type;
		$log->date = $request->date;
		$log->reference_number = $request->reference;
		if (isset($request->maintainance_type) && $request->maintainance_type == 'external'){
			// return response()->json($request->supplier,200);
			$log->supplier_id =  (int)$request->supplier;
			$log->maintainance_type = "external";
			
		}elseif(isset($request->maintainance_type) && $request->maintainance_type == 'in_house' ){
			$log->maintainance_type = "in-house";
			$log->employee_id = $request->employee;
		}
		$log->overseen_by = \Auth::user()->id;
		
		if ($request->hasFile('certificate')){
      $path = $request->certificate->path();
      $file = Storage::putFile('certificate', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/certificate/'.urlencode(end($file));

      $log->certificate = (String) $fName;
    }
		$log->save();
		if($log->type === "Repairement"){
			$loop = 0;
			foreach($request->part as $part){
				$part_repaired = new PartsRepaired;
				$part_repaired->log_id = $log->id;
				$part_repaired->user_id = \Auth::user()->id;
				$part_repaired->name = $request->part[$loop];
				$part_repaired->comment = $request->comment[$loop];
				$part_repaired->save();
				++$loop;
			}
			
		}

		return redirect()->back()->with('success', 'New '.$request->type.' log created');
	}

	public function delete_logs(Request $request){
		$log = MaintainanceCalibrationLog::find((int) $request->item_id);
		if(isset($log->id)){
			$type = $log->type;
			if($log->type == 'Repairement'){
				$parts = PartsRepaired::where('log_id',$log->id)->get();
				foreach($parts as $part){
					$part->delete();
				}
			}
			$log->delete();
			return redirect()->back()->with('success',$type.' Log deleted successfully.');
		}
		return redirect()->back()->error('error','No Log with specified ID');
	}

	public function edit(Request $request)
	{
		$log = MaintainanceCalibrationLog::find($request->item_id);
		if(isset($log->id)){
			// return response()->json($log,200);

			$log->notes = $request->notes;
			$log->description = $request->description;
			$log->type = $request->type;
			$log->date = $request->date;
			$log->reference_number = $request->reference;
			if (isset($request->maintainance_type) && $request->maintainance_type == 'external'){
				// return response()->json($request->supplier,200);
				$log->supplier_id =  (int)$request->supplier;
				$log->maintainance_type = "external";
				
			}elseif(isset($request->maintainance_type) && $request->maintainance_type == 'in_house' ){
				$log->maintainance_type = "in-house";
				$log->employee_id = $request->employee;
			}
			$log->edit_by = \Auth::user()->id;
			if ($request->hasFile('certificate')){
		  $path = $request->certificate->path();
		  $file = Storage::putFile('certificate', new File($path));
		  $file = explode('/', $file);
	
		  $fName = '/storage/certificate/'.urlencode(end($file));
	
		  $log->certificate = (String) $fName;
		}
			$log->edit_by = \Auth::user()->id;
			$log->save();
			if($log->type === 'Repairement'){
				$repaired_parts = PartsRepaired::where('log_id',$log->id);
				$parts_no = $request->parts_repaired_no;
				if ((int)$parts_no > 0){
					$range_no = range(0,(int)$parts_no);
					foreach($range_no as $number){
						$part_id = $request->partID[(int)$number];
						$part_repaired = PartsRepaired::find((int)$part_id);
						$part_repaired->name = $request->part[$number];
						$part_repaired->comment = $request->comment[$number];
						$part_repaired->save();
					}
				}else{
					$part_id = $request->partID[0];
					$part_repaired = PartsRepaired::find((int)$part_id);
					$part_repaired->name = $request->part[0];
					$part_repaired->comment = $request->comment[0];
					$part_repaired->save();
				}
			}
			
			return redirect()->back()->with('success', 'New '.$request->type.' log updated');
		}else{
			return redirect()->back()->with('error','No log with the specified ID!');
		}

	}
	public function delete(Request $request){
		$part_repaired = PartsRepaired::find($request->id);
		$part_repaired->is_delete = 1;
		$part_repaired->save();
		return response()->json('success');
	}
	public function add_equipment_attachment(Request $request){
		$attach = EquipmentAttachment::find($request->attach_id) ??  new EquipmentAttachment();
		$attach->title = $request->title;
		$attach->description = $request->description;
		$attach->equipment_id = $request->equipment_id;
		if(!isset($attach->id)){
			$attach->upload_by  = auth()->user()->id;
		}
		$attach->edit_by = isset($attach->id) ? auth()->user()->id : '';
		if($request->hasFile('attachment')){
			$path = $request->attachment->path();
			$file = Storage::putFile('equipmentAttachment', new File($path));
			$file = explode('/', $file);

			$fName = '/storage/equipmentAttachment/'.urlencode(end($file));

			$attach->attachment= (String) $fName;
		}
		$attach->save();
		return redirect()->back()->with('success','Equipment attachment saved successfully!');
	}
}
