@extends('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Equipment-Report </title>
<style type="text/css">

</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/equipment-home',
            'name' => 'Equipment',
            'icon' => null
        ),
        array(
            'link' => route('equipment-report-generate'),
            'name' => 'Reports',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => ucwords(str_replace('_', ' ', $filter['report_name'])),
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h5 class="p-4">
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Equipment Reports | {{ ucwords(str_replace('_', ' ', $filter['report_name'])) }}
    </h5>
    <div class="card" style="background-color: white !important;">
        <div class="card-header">
            <span class="card-title">
                <img src="{{$company->logo}}" alt="">
                <span class="float-right">
                    <b>Date: </b>{{getTodayDate()}} <br>
                    <b>Email: </b>{{$company->email}}
                </span>
            </span>
        </div>
        <div class="card-body">
            <b><u>Report Filters</u></b> <br>
            <div class="row p-2">
                @foreach($filter as $k=>$v)
                @if($k != '_token' && $k != 'equipment_id')
                
                <div class="col-md-3 col-lg-3 col-sm-6 mt-2">
                    <?php
                    $key = ucwords(str_replace('_', ' ', $k));
                    $value = ucwords(str_replace('_', ' ', $v));
                    ?>
                    @if($k == 'status')
                    <i class="mdi mdi-chevron-double-right"></i> {{$key}}: {{$value == 1 ? 'Disposed' : 'Active'}}
                    @else
                    <i class="mdi mdi-chevron-double-right"></i> {{$key}}: {{$value}}
                    @endif

                </div>
                @endif
                @endforeach
            </div>
            <hr>
            <div class="table-responsive mt-5">
                <table class="table table-condensed table-hover table-sm table-bordered" style="width:150%">
                    <thead class="bg-light">
                        <tr>
                            
                            @foreach($theads as $thead)
                            <th nowrap>{{$thead}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipment_data as $key=> $value)
                            @if($filter['group_by'] != 'none')
                                @if($filter['report_name'] == 'equipment_report')
                                    <tr class="bg-dark" style="color:white;font-weight:600" >
                                        <td>Group By</td>
                                        <td>{{$key}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        
                                    </tr>
                                @endif
                                @if($filter['report_name'] == 'maintainance_report')
                                <tr class="bg-dark" style="color:white;font-weight:600">
                                    <td>Group By</td>
                                    <td colspan="{{ max(count($theads) - 1, 1) }}">{{$key}}</td>
                                    
                                </tr>
                                @endif
                                @foreach($value as $data)
                                <tr>
                                    @if($filter['report_name'] == 'equipment_report')
                                    
                                    <td>{{$data->name}}</td>
        
        
                                    <td class="text-small">
                                        {!! $data->is_disposal == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                    </td>
                                    <td>{{getAssetTypeById($data->asset_type_id)->descripton}}</td>
                                    <td>{{getAssetLocationByid($data->asset_location_id)->name}}</td>
                                    <td>{{getInventoryDepartmentByid($data->assigned_department)?->name ?? $data->assigned_department }}</td>
                                    <td>
                                        {{ $data->maintainance_date()['date']->toDateString() }}
                                        <small class="ml-2 badge {{ $data->maintainance_date()['status'] }}"><i class="mdi mdi-plus"></i>{{ number_format(intval($data->maintainance_date()['remaining_days'])) }} days</small>
                                        <br>
                                        @if($data->maintainance_date()['status'] == 'text-warning')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Maintainance
                                        </small>
                                        @endif
                                        @if($data->maintainance_date()['status'] == 'text-danger')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert text-danger"></i> Equipment Maintainance Required
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $data->calibration_date()['date']->toDateString() }}
                                        <small class="ml-2 badge {{ $data->calibration_date()['status'] }}"><i class="mdi mdi-plus"></i>{{ number_format(intval($data->calibration_date()['remaining_days'])) }} days</small>
                                        <br>
                                        @if($data->calibration_date()['status'] == 'text-warning')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Calibration
                                        </small>
                                        @endif
                                        @if($data->calibration_date()['status'] == 'text-danger')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert text-danger"></i> Equipment Calibration Required
                                        </small>
                                        @endif
                                    </td>
                                    @endif
                                    @if($filter['report_name'] == 'maintainance_report')
                                    
                                    <td>{{$data->name}}</td>
                                    <td style="width: 20%;">{{isset($data->description) ?  $data->description ?? '-' : $data->procedure ?? '-'}}</td>
                                    <td>{{isset($data->type) ? $data->type : 'Verification' }} Log</td>
                                    <td>{{$data->maintainance_type}}</td>
                                    <td>{{ isset($data->correction_factor) && $data->correction_factor !== null ? $data->correction_factor : '-' }}</td>
                                    <td>{{ isset($data->uncertainty_of_measure) && $data->uncertainty_of_measure !== null ? $data->uncertainty_of_measure : '-' }}</td>
                                    <td>{{$data->maintainance_type == 'in-house'  ? getUserById($data->employee_id)->name ?? '-' : getSupplierByID($data->supplier_id)->name ?? '-'}}</td>
                                    <td>{{getAssetTypeById($data->asset_type_id)->descripton}}</td>
                                    <td>{{getAssetLocationByid($data->asset_location_id)->name}}</td>
                                    <td>{{getInventoryDepartmentByid($data->assigned_department)?->name ?? $data->assigned_department }}</td>
                                    <td style="width: 30% !important;">{{isset($data->notes) ? $data->notes ?? '-' : $data->remark ?? '-'}}</td>
                                    @endif
        
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    @if($filter['report_name'] == 'equipment_report')
                                    
                                    <td>{{$value->name}}</td>
        
        
                                    <td class="text-small">
                                        {!! $value->is_disposal == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                    </td>
                                    <td>{{getAssetTypeById($value->asset_type_id)->descripton}}</td>
                                    <td>{{getAssetLocationByid($value->asset_location_id)->name}}</td>
                                    <td>{{getInventoryDepartmentByid($value->assigned_department)?->name ?? $value->assigned_department }}</td>
                                    <td>
                                        {{ $value->maintainance_date()['date']->toDateString() }}
                                        <small class="ml-2 badge {{ $value->maintainance_date()['status'] }}"><i class="mdi mdi-plus"></i>{{ number_format(intval($value->maintainance_date()['remaining_days'])) }} days</small>
                                        <br>
                                        @if($value->maintainance_date()['status'] == 'text-warning')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Maintainance
                                        </small>
                                        @endif
                                        @if($value->maintainance_date()['status'] == 'text-danger')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert text-danger"></i> Equipment Maintainance Required
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $value->calibration_date()['date']->toDateString() }}
                                        <small class="ml-2 badge {{ $value->calibration_date()['status'] }}"><i class="mdi mdi-plus"></i>{{ number_format(intval($value->calibration_date()['remaining_days'])) }} days</small>
                                        <br>
                                        @if($value->calibration_date()['status'] == 'text-warning')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Calibration
                                        </small>
                                        @endif
                                        @if($value->calibration_date()['status'] == 'text-danger')
                                        <small class="p-3">
                                            <i class="mdi mdi-alert text-danger"></i> Equipment Calibration Required
                                        </small>
                                        @endif
                                    </td>
                                    @endif
                                    @if($filter['report_name'] == 'maintainance_report')
                                    
                                    <td>{{$value->name}}</td>
                                    <td style="width: 20%;">{{isset($value->description) ?  $value->description ?? '-' : $value->procedure ?? '-'}}</td>
                                    <td>{{isset($value->type) ? $value->type : 'Verification' }} Log</td>
                                    <td>{{$value->maintainance_type}}</td>
                                    <td>{{ isset($value->correction_factor) && $value->correction_factor !== null ? $value->correction_factor : '-' }}</td>
                                    <td>{{ isset($value->uncertainty_of_measure) && $value->uncertainty_of_measure !== null ? $value->uncertainty_of_measure : '-' }}</td>
                                    <td>{{$value->maintainance_type == 'in-house'  ? getUserById($value->employee_id)->name ?? '-' : getSupplierByID($value->supplier_id)->name ?? '-'}}</td>
                                    <td>{{getAssetTypeById($value->asset_type_id)->descripton}}</td>
                                    <td>{{getAssetLocationByid($value->asset_location_id)->name}}</td>
                                    <td>{{getInventoryDepartmentByid($value->assigned_department)?->name ?? $value->assigned_department }}</td>
                                    <td style="width: 25% !important;">{{isset($value->notes) ? $value->notes ?? '-' : $value->remark ?? '-'}}</td>
                                    @endif
        
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>
@endsection
@section('script2')




@endsection