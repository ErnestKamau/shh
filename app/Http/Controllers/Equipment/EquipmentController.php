<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\EquipmentNotifications;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\Models\Equipments\PartsRepaired;
use App\Supplier;
use App\Models\Equipments\VerificationLog;
use App\Models\Equipments\Equipment;
use Illuminate\Http\File;
use App\EquipmentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\InventoryDepartment;

use App\Http\Controllers\Controller;
class EquipmentController extends Controller
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
	public function index()
	{
		$equipment = Equipment::where('company_id', getUserCompany())->orderBy('name', 'asc')->get();
		$employees = getUsers();
		$statuses = getStatus();
		$departments = InventoryDepartment::where('module','organizational')->get();
		return view('layouts.equipment.index', compact('equipment','employees','statuses','departments'));
	}

	public function show($id)
	{
		$equipment = Equipment::find($id);
		$parts = PartsRepaired::all();
		$employees = getUsers();
		$attachments = EquipmentAttachment::where('equipment_id',$equipment->id)->get();
		$suppliers = Supplier::all();
		$departments = InventoryDepartment::where('module','organizational')->get();
		$statuses = getStatus();
		$verifys = VerificationLog::all();
		$notifications = EquipmentNotifications::where('equipment_id',$id)->get();
		return view('layouts.equipment.show', compact('equipment','parts','employees','suppliers','verifys','statuses','attachments','departments','notifications'));
	}

	public function add(Request $request){
		$equipment = new Equipment;

		// return response()->json($request->all(), 200);

		$equipment->name = $request->name;
		$equipment->equipment_number = $request->equipment_number;
		$equipment->description = $request->description;
		if ($request->hasFile('photo')){
      $path = $request->photo->path();
      $file = Storage::putFile('equipment', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/equipment/'.urlencode(end($file));

      $equipment->picture = (String) $fName;
    }
		$equipment->make = $request->make;
		$equipment->model = $request->model;
		$equipment->date_purchased = $request->date_purchased;
		$equipment->maintainance_days = $request->maintainance_in_days;
		$equipment->maintainance_days = $request->maintainance_notification_in_days;
		$equipment->calibration_days = $request->calibration_in_days;
		$equipment->calibration_days = $request->calibration_notification_in_days;
		$equipment->company_id = getUserCompany();
		// -------------
		$equipment->serial_number = $request->serial;
		$equipment->barcode_number = $request->barcode;
		$equipment->manufacturer = $request->manufacturer;
		$equipment->status = $request->status;
		$equipment->condition = $request->condition;
		$equipment->assigned_department = $request->department;
		$equipment->assigned_employee_id = $request->employee;
		$equipment->warranty_date = $request->warranty;
		$equipment->asset_type_id = $request->asset_type_id;
		$equipment->asset_location_id = $request->location_id;
		// --------------
		$equipment->active = 1;

		$equipment->save();

		return redirect()->back()->with('success', 'Equipment has been added.');
	}

	public function edit(Request $request, $id){
		$equipment = Equipment::find($id);

		// return response()->json($request->all(), 200);

		$equipment->name = $request->name;
		$equipment->equipment_number = $request->equipment_number;
		$equipment->description = $request->description;
		if ($request->hasFile('photo')){
      $path = $request->photo->path();
      $file = Storage::putFile('equipment', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/equipment/'.urlencode(end($file));

      $equipment->picture = (String) $fName;
    }
		$equipment->serial_number = $request->serial;
		$equipment->barcode_number = $request->barcode;
		$equipment->manufacturer = $request->manufacturer;
		$equipment->status = $request->status;
		$equipment->condition = $request->condition;
		$equipment->assigned_department = $request->department;
		$equipment->assigned_employee_id = $request->employee;
		$equipment->warranty_date = $request->warranty;

		$equipment->make = $request->make;
		$equipment->model = $request->model;
		$equipment->date_purchased = $request->date_purchased;
		$equipment->maintainance_days = $request->maintainance_in_days;
		$equipment->maintainance_notification_in_days = $request->maintainance_notification_in_days;
		$equipment->calibration_days = $request->calibration_in_days;
		$equipment->calibration_notification_in_days = $request->calibration_notification_in_days;
		$equipment->company_id = getUserCompany();
		$equipment->asset_type_id = $request->asset_type_id;
		$equipment->asset_location_id = $request->location_id;
		$equipment->active = $request->active ?? 0;
		$equipment->requires_daily_log = $request->requires_daily_log ?? 0;

		$equipment->save();

		return redirect()->back()->with('success', 'Equipment has been edited.');
	}
	public function dispose(Request $request,$id){
		$equipment = Equipment::find($id);

		$equipment->employee_dispose_id = $request->employee;
		$equipment->dispose_date = $request->date;
		$equipment->comment = $request->comment;
		$equipment->is_disposal = 1;

		$equipment->save();

		return redirect()->back()->with('success', 'Equipment disposed!');
	}
	public function revert($id){
		$equipment = Equipment::find($id);
		$equipment->is_disposal = 0;
		$equipment->save();

		return redirect()->back()->with('success', 'Equipment Reverted!');

	}
	public function addEquipmentNotification(Request $request){
		$equipment = Equipment::find($request->equipment_id);

		
		$notification  = EquipmentNotifications::find($request->notification_id) ?? new EquipmentNotifications();
		$notification->equipment_id = $request->equipment_id;
		$notification->value = $request->value;
		$notification->frequency = $request->frequency;
		$notification->notification_type = $request->notification_type;

		if($request->notification_type == 'calibration'){
			if($equipment->calibration_days == ""){
				return redirect()->back()->with('error','Kindly set equipment calibration days');
			}
			if($equipment->calibration_days < $request->value){
				return redirect()->back()->with('error','Ensure the notification days is less than the equipment calibration days');
			}
			$daysToAdd = $equipment->calibration_days - $request->value;

			$last_calibration = MaintainanceCalibrationLog::where('equipment_id',$request->equipment_id)->where('type','Calibration')->orderBy('id','DESC')->first();
			if(isset($last_calibration->id)){
				$notification->next_date = \Carbon\Carbon::parse($last_calibration->date)->addDays($daysToAdd);
			}else{
				$notification->next_date = \Carbon\Carbon::parse($equipment->date_purchased)->addDays($daysToAdd);
			}
		}
		if($request->notification_type == 'maintanance'){
			if($equipment->maintainance_days == ""){
				return redirect()->back()->with('error','Kindly set equipment maintainance days');
			}
			if($equipment->maintainance_days < $request->value){
				return redirect()->back()->with('error','Ensure the notification days is less than the equipment maintainance days');
			}
			$daysToAdd = $equipment->maintainance_days - $request->value;

			$last_maintain = MaintainanceCalibrationLog::where('equipment_id',$request->equipment_id)->where('type','Maintainance')->orderBy('id','DESC')->first();
			if(isset($last_maintain->id)){
				$notification->next_date = \Carbon\Carbon::parse($last_maintain->date)->addDays($daysToAdd);
			}else{
				$notification->next_date = \Carbon\Carbon::parse($equipment->date_purchased)->addDays($daysToAdd);
			}
		}
		if($request->notification_type == 'verification'){
			if($equipment->verification_days == ""){
				return redirect()->back()->with('error','Kindly set equipment verification days');
			}
			if($equipment->verification_days < $request->value){
				return redirect()->back()->with('error','Ensure the notification days is less than the equipment verification days');
			}
			$daysToAdd = $equipment->verification_days - $request->value;

			$last_verification = VerificationLog::where('equipment_id',$request->equipment_id)->orderBy('id','DESC')->first();
			if(isset($last_calibration->id)){
				$notification->next_date = \Carbon\Carbon::parse($last_verification->date)->addDays($daysToAdd);
			}else{
				$notification->next_date = \Carbon\Carbon::parse($equipment->date_purchased)->addDays($daysToAdd);
			}
		}
		$notification->save();

		return redirect()->back()->with('success','Equipment Notification added successfully');

	}
	public function deleteEquipmentNotification(Request $request){
		EquipmentNotifications::find($request->notification_id)->delete();
		return redirect()->back()->with('success','Equipment deleted successfully');
	}


}
