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
        $equipments = Equipment::query()
            ->where('is_disposal', false)
            ->orderBy('name')
            ->get();
        $asset_types = AssetType::where('is_active', 1)->orderBy('descripton')->get();
        $departments = InventoryDepartment::where('module', 'organizational')->orderBy('name')->get();
        $asset_locations = AssetLocation::where('is_active', 1)->orderBy('name')->get();
        $employees = User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->where('is_support_staff', 0)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('layouts.equipment.reports.index', compact('equipments', 'asset_types', 'asset_locations', 'departments', 'employees', 'suppliers'));
    }

    public function show(Request $request)
    {
        $filter = $request->all();
        $filter['department'] = $request->department != 'all'
            ? (InventoryDepartment::find($request->department)?->name ?? $request->department)
            : $request->department;
        $filter['asset_location'] = $request->asset_location != 'all'
            ? (getAssetLocationByid($request->asset_location)?->name ?? $request->asset_location)
            : $request->asset_location;
        $filter['asset_type'] = $request->asset_type != 'all'
            ? (getAssetTypeById($request->asset_type)?->descripton ?? $request->asset_type)
            : $request->asset_type;
        $filter['equipment'] = $request->equipment_id != 'all'
            ? (getEquipmentById($request->equipment_id)?->name ?? $request->equipment_id)
            : $request->equipment_id;
        $filter['status'] = match ($request->status) {
            'disposed' => 'Disposed',
            'active' => 'Active',
            'all' => 'All',
            default => $request->status,
        };
        $filter['maintainance_status'] = $this->dueStatusLabel($request->maintainance_status);
        $filter['calibration_status'] = $this->dueStatusLabel($request->calibration_status);

        $data = Equipment::query()->with(['assetType', 'assetLocation', 'department']);
        $theads = [];
        $data = isset($request->equipment_id) && $request->equipment_id != 'all' ? $data->where('equipment.id', $request->equipment_id) : $data;
        $data = $this->applyDisposalStatusFilter($data, $request->status);
        $data = isset($request->asset_type) && $request->asset_type != 'all' ? $data->where('asset_type_id', $request->asset_type) : $data;
        $data = isset($request->asset_location) && $request->asset_location != 'all' ? $data->where('asset_location_id', $request->asset_location) : $data;
        $data = isset($request->department) && $request->department != 'all' ? $data->where('assigned_department', $request->department) : $data;

        $result = collect();

        if ($request->report_name == 'equipment_report') {
            $theads = ['Equipment', 'Status', 'Asset Type', 'Asset Location', 'Department', 'Next Maintainance', 'Next Calibration'];
            unset($filter['employee_id']);
            unset($filter['service_provider']);
            unset($filter['start_date']);
            unset($filter['end_date']);
            unset($filter['log_type']);
            unset($filter['type']);
            $result = $data->orderBy('name')->get();
            $result = $this->applyDueStatusFilters(
                $result,
                $request->maintainance_status,
                $request->calibration_status
            );
        }

        if ($request->report_name == 'maintainance_report') {
            $theads = ['Equipment', 'Description', 'Log', 'Type', 'Correction Factor', 'Uncertainty Of Measure', 'Service Performer', 'Asset Type', 'Asset Location', 'Department', 'Remarks'];
            unset($filter['maintainance_status']);
            unset($filter['calibration_status']);
            $result = collect($this->maintainanceLogReports($data, $request) ?? []);
        }

        $equipment_data = [];

        if (isset($request->group_by) && $request->group_by != 'none') {
            $group_name = $request->group_by;

            foreach ($result as $ed) {
                if (! isset($ed->$group_name)) {
                    if (! isset($equipment_data['verification'])) {
                        $equipment_data['verification'] = [];
                    }
                    $equipment_data['verification'][] = $ed;
                } else {
                    $group_attr = '';
                    $group_attr = $group_name == 'asset_type_id'
                        ? ($ed->relationLoaded('assetType')
                            ? ($ed->assetTypeLabel())
                            : (getAssetTypeById($ed->$group_name)?->descripton ?? $ed->$group_name ?? 'Unknown'))
                        : $group_attr;
                    $group_attr = $group_name == 'asset_location_id'
                        ? ($ed->relationLoaded('assetLocation') && $ed->assetLocation
                            ? $ed->assetLocation->name
                            : (getAssetLocationByid($ed->$group_name)?->name ?? $ed->$group_name ?? 'Unknown'))
                        : $group_attr;
                    $group_attr = $group_name == 'is_disposal' && ! $ed->$group_name ? 'Active' : $group_attr;
                    $group_attr = $group_name == 'is_disposal' && $ed->$group_name ? 'Disposed' : $group_attr;
                    $group_attr = $group_name == 'assigned_department'
                        ? ($ed->relationLoaded('department') && $ed->department
                            ? $ed->department->name
                            : (getInventoryDepartmentByid($ed->$group_name)?->name ?? $ed->$group_name))
                        : $group_attr;
                    $group_attr = $group_name == 'type' ? $ed->$group_name : $group_attr;
                    $group_attr = $group_name == 'maintainance_type' ? $ed->$group_name : $group_attr;

                    if (! isset($equipment_data[$group_attr])) {
                        $equipment_data[$group_attr] = [];
                    }
                    $equipment_data[$group_attr][] = $ed;
                }
            }
            $filter['group_by'] = $filter['group_by'] == 'asset_type_id' ? 'asset_type' : $filter['group_by'];
            $filter['group_by'] = $filter['group_by'] == 'asset_location_id' ? 'asset_location' : $filter['group_by'];
            $filter['group_by'] = $filter['group_by'] == 'is_disposal' ? 'equipment_status' : $filter['group_by'];
        } else {
            $equipment_data = $result;
        }

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

            $data->selectRaw('mcl.*,equipment.assigned_department,equipment.asset_type_id,equipment.asset_location_id,equipment.asset_code,equipment.asset_description,equipment.name');

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
            $data->selectRaw('mcl.*,equipment.assigned_department,equipment.asset_type_id,equipment.asset_location_id,equipment.asset_code,equipment.asset_description,equipment.name');

            $main_data = $data->get();
            $final = [];
            foreach ($main_data as $d) {
                $final[] = $d;
            }
            foreach ($result as $r) {
                $final[] = $r;
            }

            return $final;
        }

        return [];
    }

    /**
     * Map report form status labels to the boolean is_disposal column.
     */
    private function applyDisposalStatusFilter($data, ?string $status)
    {
        if (! isset($status) || $status === 'all') {
            return $data;
        }

        $statusMap = [
            'active' => false,
            'disposed' => true,
        ];

        if (! array_key_exists($status, $statusMap)) {
            return $data;
        }

        return $data->where('is_disposal', $statusMap[$status]);
    }

    /**
     * Filter equipment by computed maintainance/calibration due statuses.
     */
    private function applyDueStatusFilters($equipment, ?string $maintainanceStatus, ?string $calibrationStatus)
    {
        $equipment = collect($equipment);

        if ($maintainanceStatus && $maintainanceStatus !== 'all') {
            $target = $this->dueStatusBadge($maintainanceStatus);
            $equipment = $equipment->filter(function ($item) use ($target) {
                return ($item->maintainance_date()['status'] ?? null) === $target;
            })->values();
        }

        if ($calibrationStatus && $calibrationStatus !== 'all') {
            $target = $this->dueStatusBadge($calibrationStatus);
            $equipment = $equipment->filter(function ($item) use ($target) {
                return ($item->calibration_date()['status'] ?? null) === $target;
            })->values();
        }

        return $equipment;
    }

    private function dueStatusBadge(?string $status): ?string
    {
        return match ($status) {
            'overdue' => 'badge-danger',
            'due_soon' => 'badge-warning',
            'up_to_date' => 'badge-success',
            default => null,
        };
    }

    private function dueStatusLabel(?string $status): string
    {
        return match ($status) {
            'overdue' => 'Overdue',
            'due_soon' => 'Due Soon',
            'up_to_date' => 'Up to Date',
            'all', null, '' => 'All',
            default => (string) $status,
        };
    }

    public function getVerificationLogs($request)
    {
        $data = Equipment::query()->with(['assetType', 'assetLocation', 'department']);

        $data = isset($request->equipment_id) && $request->equipment_id != 'all' ? $data->where('equipment.id', $request->equipment_id) : $data;
        $data = $this->applyDisposalStatusFilter($data, $request->status);
        $data = isset($request->asset_type) && $request->asset_type != 'all' ? $data->where('asset_type_id', $request->asset_type) : $data;
        $data = isset($request->asset_location) && $request->asset_location != 'all' ? $data->where('asset_location_id', $request->asset_location) : $data;
        $data = isset($request->department) && $request->department != 'all' ? $data->where('assigned_department', $request->department) : $data;
        $data = $data->join('verification_logs as vl', 'vl.equipment_id', 'equipment.id');
        $data = isset($request->start_date) && $request->start_date != '' ? $data->where('vl.verification_date', '>=', $request->start_date) : $data;
        $data = isset($request->end_date) && $request->end_date != '' ? $data->where('vl.verification_date', '<=', $request->end_date) : $data;
        $data = isset($request->type) && $request->type != 'all' ? $data->where('vl.maintainance_type', $request->type) : $data;
        $data = isset($request->service_provider) && $request->service_provider != 'all' ? $data->where('vl.supplier_id', $request->service_provider) : $data;
        $data = isset($request->employee_id) && $request->employee_id != 'all' ? $data->where('vl.operator_id', $request->employee_id) : $data;
        $data->selectRaw('vl.*,equipment.assigned_department,equipment.asset_type_id,equipment.asset_location_id,equipment.asset_code,equipment.asset_description,equipment.name');

        return $data->get();
    }
}
