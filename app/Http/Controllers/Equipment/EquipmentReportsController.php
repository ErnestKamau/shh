<?php

namespace App\Http\Controllers\Equipment;

use App\Models\Equipments\PartsRepaired;
use App\Models\Equipments\Verificationdata;
use App\Models\Equipments\Equipment;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use App\Models\Equipments\MaintainanceCalibrationdata;
use App\InventoryDepartment;
use App\User;
use App\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EquipmentReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // return response()->json('test');
        $equipments = Equipment::all();
        $asset_types = AssetType::where('is_active', 1)->get();
        $departments = InventoryDepartment::where('module', 'organizational')->get();
        $asset_locations = AssetLocation::where('is_active', 1)->get();
        $employees = User::where('is_client', 0)->where('supplier_id', 0)->where('active',1)->where('is_support_staff',0)->get();
        $suppliers = Supplier::all();

        return view('layouts.equipment.reports.index', compact('equipments', 'asset_types', 'asset_locations', 'departments', 'employees', 'suppliers'));
    }
    public function show(Request $request)
    {
        $filter = $request->all();
        $filter['department'] = $request->department != 'all' ? InventoryDepartment::find($request->department)->name : $request->department;
        $filter['asset_location'] = $request->asset_location != 'all' ? getAssetLocationByid($request->asset_location)->name :  $request->asset_location;
        $filter['asset_type'] = $request->asset_type != 'all' ?  getAssetTypeById($request->asset_type)->descripton :  $request->asset_type;
        $filter['equipment'] = $request->equipment_id != 'all' ? getEquipmentById($request->equipment_id)->name : $request->equipment_id;

        $data = Equipment::query();
        $theads = [];
        $data = isset($request->equipment_id) && $request->equipment_id != 'all' ? $data->where('equipment.id', $request->equipment_id) : $data;
        $data = isset($request->status) && $request->status != 'all' ? $data->where('is_disposal', $request->status) : $data;
        $data = isset($request->asset_type) && $request->asset_type != 'all' ? $data->where('asset_type_id', $request->asset_type) : $data;
        $data = isset($request->asset_location) && $request->asset_location != 'all' ? $data->where('asset_location_id', $request->asset_location) : $data;
        $data = isset($request->department) && $request->department != 'all' ? $data->where('assigned_department', $request->department) : $data;
        if ($request->report_name == 'equipment_report') {
            $theads = ['Equipment', 'Status', 'Asset Type', 'Asset Location', 'Department', 'Next Maintainance', 'Next Calibration'];
            unset($filter['employee_id']);
            unset($filter['service_provider']);
            unset($filter['start_date']);
            unset($filter['end_date']);
            unset($filter['log_type']);
            unset($filter['type']);
            $result =  $data->get();
        }
        if ($request->report_name == 'maintainance_report') {
            $theads = ['Equipment', 'Description', 'Log', 'Type', 'Service Performer', 'Asset Type', 'Asset Location', 'Department', 'Remarks',];
            $data = $this->maintainanceLogReports($data, $request);
            $result = $data;
        }

        $equipment_data = [];

        if (isset($request->group_by) && $request->group_by != 'none') {
            $group_name = $request->group_by;

            foreach ($result as $ed) {
                if(!isset($ed->$group_name)){
                  !isset($equipment_data['verification']) ?  $equipment_data['verification'] = []: '';
                  array_push($equipment_data['verification'],$ed);
                }else{
                    $group_attr = '';
                    $group_attr = $group_name == 'asset_type_id' ? getAssetTypeById($ed->$group_name)->descripton : $group_attr;
                    $group_attr = $group_name == 'asset_location_id' ? getAssetLocationByid($ed->$group_name)->name : $group_attr;
                    $group_attr = $group_name == 'is_disposal' && $ed->$group_name == 0 ? 'Active' : $group_attr;
                    $group_attr = $group_name == 'is_disposal' && $ed->$group_name == 1 ? 'Disposed' : $group_attr;
                    $group_attr = $group_name == 'assigned_department' ? getInventoryDepartmentByid($ed->$group_name)->name : $group_attr;
                    $group_attr = $group_name == 'type' ? $ed->$group_name : $group_attr;
                    $group_attr = $group_name == 'maintainance_type' ? $ed->$group_name : $group_attr;
                    !isset($equipment_data[$group_attr]) ? $equipment_data[$group_attr] = [] : '';
                    array_push($equipment_data[$group_attr], $ed);
                }
            }
            $filter['group_by'] = $filter['group_by'] == 'asset_type_id' ? 'asset_type' : $filter['group_by'];
            $filter['group_by'] = $filter['group_by'] == 'asset_location_id' ? 'asset_location' : $filter['group_by'];
            $filter['group_by'] = $filter['group_by'] == 'is_disposal' ? 'equipment_st' : $filter['group_by'];


        } else {
            $equipment_data = $result;
        }

        // return response()->json($equipment_data);


        $company = getActiveCompany();

        return view('layouts.equipment.reports.show', compact('equipment_data', 'filter', 'company', 'theads'));
    }
    private function maintainanceLogReports($data, $request)
    {
        
        if (isset($request->log_type) && $request->log_type != 'Verification' && $request->log_type != 'all') {
            $data = $data->join('maintainance_calibration_logs as mcl', 'mcl.equipment_id', 'equipment.id');
            $data = isset($request->start_date) && $request->start_date != '' ? $data->where('mcl.date', '>=', $request->start_date) : $data;
            $data = isset($request->end_date) && $request->end_date != '' ? $data->where('mcl.date', '<=', $request->end_date) : $data;
            $data = isset($request->type) && $request->type != 'all' ? $data->where('mcl.maintainance_type', $request->type) : $data;
            $data = isset($request->log_type) && $request->log_type != 'all' ? $data->where('mcl.type', $request->log_type) : $data;
            $data = isset($request->service_provider) && $request->service_provider != 'all' ? $data->where('mcl.supplier_id', $request->service_provider) : $data;
            $data = isset($request->employee_id) && $request->employee_id != 'all' ? $data->where('mcl.employee_id', $request->employee_id) : $data;

            $data->selectRaw('mcl.*,equipment.assigned_department,equipment.asset_type_id,equipment.asset_location_id,equipment.name');
            return $data->get();
        } elseif (isset($request->log_type) && $request->log_type == 'Verification') {
            return $this->getVerificationLogs($request);
        } elseif (isset($request->log_type) && $request->log_type == 'all') {
            $result = $this->getVerificationLogs($request);
            $data = $data->join('maintainance_calibration_logs as mcl', 'mcl.equipment_id', 'equipment.id');
            $data = isset($request->start_date) && $request->start_date != '' ? $data->where('mcl.date', '>=', $request->start_date) : $data;
            $data = isset($request->end_date) && $request->end_date != '' ? $data->where('mcl.date', '<=', $request->end_date) : $data;
            $data = isset($request->type) && $request->type != 'all' ? $data->where('mcl.maintainance_type', $request->type) : $data;
            $data = isset($request->log_type) && $request->log_type != 'all' ? $data->where('mcl.type', $request->log_type) : $data;
            $data = isset($request->service_provider) && $request->service_provider != 'all' ? $data->where('mcl.supplier_id', $request->service_provider) : $data;
            $data = isset($request->employee_id) && $request->employee_id != 'all' ? $data->where('mcl.employee_id', $request->employee_id) : $data;
            $data->selectRaw('mcl.*,equipment.assigned_department,equipment.asset_type_id,equipment.asset_location_id,equipment.name');
            
            $main_data = $data->get();
            $final = [];
            foreach($main_data as $d){
                array_push($final,$d);
            }
            foreach($result as $r){
                array_push($final,$r);
            }

            return $final;    
        }
    }

    public function getVerificationLogs($request)
    {
        $data = Equipment::query();
        
        $data = isset($request->equipment_id) && $request->equipment_id != 'all' ? $data->where('equipment.id', $request->equipment_id) : $data;
        $data = isset($request->status) && $request->status != 'all' ? $data->where('is_disposal', $request->status) : $data;
        $data = isset($request->asset_type) && $request->asset_type != 'all' ? $data->where('asset_type_id', $request->asset_type) : $data;
        $data = isset($request->asset_location) && $request->asset_location != 'all' ? $data->where('asset_location_id', $request->asset_location) : $data;
        $data = isset($request->department) && $request->department != 'all' ? $data->where('assigned_department', $request->department) : $data;
        $data = $data->join('verification_logs as vl', 'vl.equipment_id', 'equipment.id');
        $data = isset($request->start_date) && $request->start_date != '' ? $data->where('vl.verification_date', '>=', $request->start_date) : $data;
        $data = isset($request->end_date) && $request->end_date != '' ? $data->where('vl.verification_date', '<=', $request->end_date) : $data;
        $data = isset($request->type) && $request->type != 'all' ? $data->where('vl.maintainance_type', $request->type) : $data;
        $data = isset($request->service_provider) && $request->service_provider != 'all' ? $data->where('vl.supplier_id', $request->service_provider) : $data;
        $data = isset($request->employee_id) && $request->employee_id != 'all' ? $data->where('vl.operator_id', $request->employee_id) : $data;
        $data->selectRaw('vl.*,equipment.assigned_department,equipment.asset_type_id,equipment.asset_location_id,equipment.name');
        return $data->get();
    }
}
