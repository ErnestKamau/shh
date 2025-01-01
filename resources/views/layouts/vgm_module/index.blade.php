@extends('layouts.vgm_module.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>{{ $module }} | Index</title>

<style>
	.form-part-toggler {
		margin: 0px 0px 5px 0px !important;
		padding: 6px 6px 6px 6px;
		border-bottom: 1px solid rgba(0, 0, 0, 0.09);
		cursor: pointer;
	}

	.form-part-toggler:hover {
		background-color: rgba(0, 0, 0, 0.08);
	}

	#sample-detail-rows .form-group {
		display: none;
	}

	#sample-detail-rows tr.selected-row {
		background-color: rgb(253, 220, 220);
	}

	#sample-detail-rows .text {
		display: unset;
	}

	#sample-detail-rows tr.editable .form-group {
		display: unset;
	}

	#sample-detail-rows tr.editable .text {
		display: none;
	}

	#sample-detail-rows tr {
		cursor: pointer;
	}

	.hidden {
		display: none;
	}

	.overdue-bg-color {
		background-color: rgba(240, 185, 83, 0.972) !important;
	}

	.upfront-bg-color {
		background-color: skyblue !important;
	}

	.ammend-bg-color {
		background-color: #fef764 !important;
	}

	.btn-white {
		background-color: white !important;
	}
</style>
@endsection
@section('content2')
<main>
	<?php
$items = array(
	array(
		'link' => route('vgm.index'),
		'name' => 'Dashboard',
		'icon' => null
	)
);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<span class="float-left"><i class="mdi mdi-file-find-outline"></i> VGM Certificates</span>
		<div class="btn-group float-right">
			<button type="button" class="btn btn-sm btn-white dropdown-toggle"
				style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;" type="button" id="dropdownMenuButton"
				data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				Actions
			</button>
			<div class="dropdown-menu dropdown-menu-right">
                <li>
                    <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#add-cert" data-action="bulk"><i class="mdi mdi-plus mr-2" data-toggle="tooltip" title="create Certificate"></i> Create Certificate</span>
                </li>
                <li>
                    <span class="btn btn-sm dropdown-item" data-toggle="modal" data-target="#add-cert" data-action="bulk"><i class="mdi md-cogs mr-2" data-toggle="tooltip" title="create Certificate"></i> Generate Pdf Certs</span>
                </li>
				
			</div>
		</div>
	</h4>
	
    <b>Apply Filter ?</b>
    <form style="background-color:white" class="p-3"  action="{{route('vgm.index')}}" method="get">
        @csrf
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="" class="control-label">From Date <small>(created at)</small></label>
                    <input type="date" name="from_date" id="" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="" class="control-label">To Date <small>(created at)</small></label>
                    <input type="date" name="to_date" id="" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="" class="control-label">Certificate No</label>
                    <input type="text" value="" name="cert_no" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="" class="control-label">Serial No</label>
                    <input type="text" value="" name="serial_no" class="form-control">
                </div>
            </div>
            <div class="col-md-12">
                <button class="btn btn-sm btn-outline-primary float-right"><i class="mdi mdi-filter-outline"></i>
                    Apply</button>
            </div>
        </div>
    </form>
	<div class="table-responsive bg-light mt-3 p-4">
		<table class="table table-condensed my-small-text table-bordered table-sm">
			<thead>
				<th></th>
				<th>Certificate No</th>
                <th>Serial No</th>
                <th>Certificate</th>
                <th>Carrier Booking No</th>
                <th>Container No</th>
                <th>Submission Date</th>
                <th>Shipper Company Name</th>
                <th>Place of Receipt</th>
                <th>Port of Depature</th>
                <th>Port of Discharge</th>
                <th>Final Destination</th>
			</thead>
			<tbody>
				@foreach ($certificates as $cert )
                    <tr>
                        <td>
                            <input type="checkbox" name="cert_id" value="{{$cert->id}}" data-cert="{{$cert->cert_no}}" id="">
                            <span class="btn btn-sm btn-default text-primary" data-target="#edit-cert" data-record="{{json_encode($cert)}}" data-toggle="modal"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                            <a href="{{route('vgm.show',['id'=>$cert->id])}}" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye" data-toggle="tooltip" title="View"></i></a>
                            <span class="btn btn-sm btn-default text-danger" data-toggle="modal" data-target="#delete-certificate" data-record="{{json_encode($cert)}}"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                        </td>
                        <td>{{$cert->cert_no}}</td>
                        <td>{{$cert->serial_no}}</td>
                        <td class="text-center">{!! $cert->pdf_url != '' ? '<a target="_blank" href="{{$cert->pdf_url}}" class="btn btn-sm btn-default text-primary"><i class="mdi mdi-download"></i></a>' : '-' !!} </td>
                        <td>{{$cert->carrier_booking_number}}</td>
                        <td>{{$cert->container_number}}</td>
                        <td>{{$cert->submission_date}}</td>
                        <td>{{$cert->shipper_company_name}}</td>
                        <td>{{$cert->place_of_receipt}}</td>
                        <td>{{$cert->port_of_depature}}</td>
                        <td>{{$cert->port_of_discharge}}</td>
                        <td>{{$cert->final_destination}}</td>
                    </tr>
                
                @endforeach
			</tbody>
		</table>
	</div>
</main>
@endsection

@section('script2')
<div class="modal fade" id="add-cert" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('vgm.store')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add VGM Certificate</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Approved VGM No</label>
                                <input type="text" name="approved_vgm_no" placeholder="Approved VGM No..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Authorized VGM Contact</label>
                                <input type="text" name="authorized_vgm_contact" placeholder="Authorized VGM Contact..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Phone No</label>
                                <input type="text" name="phone_no" id="" placeholder="Phone Number..." class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Email</label>
                                <input type="text" name="email" placeholder="example@gmail.com" id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p><u><b>Shipper Information :</b></u></p>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Submission date</label>
                                <input type="date" name="submission_date" id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Shipper Company Name</label>
                                <input type="text" name="shipper_company_name" id="" class="form-control" placeholder="Shipper Company Name...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Address</label>
                                <input type="text" name="address" placeholder="Address..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p><u><b>Shipper Authorized Contact</b></u></p>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Name</label>
                                <select name="shipper_authorirized_contact_id" id="" class="form-control">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Date</label>
                                <input type="date" name="date" id="" class="form-control">
                            </div>
                        </div>
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Sign</label>
                                <input type="file" name="signs" placeholder="Signature" id="">
                            </div>
                        </div> -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Carrier Booking No</label>
                                <input type="text" name="carrier_book_no" placeholder="Carrier Booking No..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Shipper Invoice No</label>
                                <input type="text" name="shipper_invoice_no" id="" class="form-control" placeholder="Shipper Invoice No...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Shipper P/O No</label>
                                <input type="text" name="shipper_po_no" placeholder="Shipper PO No..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Name of Booked Vessel</label>
                                <input type="text" name="name_booked_vessel" placeholder="Name of Booked Vessel..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Voyage Number</label>
                                <input type="text" name="voyage_number" placeholder="Voyage Number" id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">ETD / ETA</label>
                                <input type="text" name="etd" placeholder="ETD / ETA..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Place of Receipt</label>
                                <input type="text" name="place_receipt" placeholder="Place of Receipt..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Port of Depature</label>
                                <input type="text" name="port_depature" id="" class="form-control" placeholder="Port of Depature...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Port of Discharge</label>
                                <input type="text" name="port_discharge" placeholder="Port of Discharge..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Final Destination</label>
                                <input type="text" name="final_destination" placeholder="Final Destination..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Container Number</label>
                                <input type="text" name="container_number" placeholder="Container Number..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Seal Number(s) *if applicabble*</label>
                                <input type="text" name="seal_number" id="" class="form-control" placeholder="Seal Number...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Size of Container <small>(TEU / FEU)</small> </label>
                                <input type="text" name="size_container" placeholder="Size of Container..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="control-label">Description of Goods</label>
                                <textarea name="description_goods" placeholder="Description of Goods..." id="" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p><u><b>Verified Gross Mass (VGM) Weight in KGS</b></u></p>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Container Maximum Gross</label>
                                <input type="text" name="container_max_gross" placeholder="Container Maximum Gross..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Cargo Weight</label>
                                <input type="text" name="cargo_weight" placeholder="Cargo Weight..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Empty Container Weight</label>
                                <input type="text" name="empty_container_weight" id="" class="form-control" placeholder="Empty Container Weight...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Packaging Material Weight</label>
                                <input type="text" name="packaging_material_weight" placeholder="Packaging Material Weight..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Dunnage Weight</label>
                                <input type="text" name="dunnage_weight" placeholder="Dunnage Weight..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label">Total Verified Gross Mass</label>
                                <input type="text" name="total_verified_gross_mass" placeholder="Total Verified Gross Mass..." id="" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="" class="control-label">VGM Evaluation Method</label>
                            <textarea name="evaluation_method" id="" class="form-control"></textarea>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="contrl-label">
                                    1st Attending Surveyor
                                </label>
                                <select name="first_surveyor_id" id="" class="form-control">
                                    <option value="">Select Surveyor...</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Date</label>
                                <input type="date" name="first_date" id="" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="contrl-label">
                                    2nd Attending Surveyor
                                </label>
                                <select name="sec_surveyor_id" id="" class="form-control">
                                    <option value="">Select Surveyor...</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Date</label>
                                <input type="date" name="sec_date" id="" class="form-control">
                            </div>
                        </div>
                        <input type="hidden" name="detail_id" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-cert" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('vgm.delete')}}" method="post">
                @csrf 
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-thumb-up"></i> Yes, Delete</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

<script>
    $(()=>{
        var getdeletebody = (data)=>{
            var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty"></i>
                    <span class="pl-2">Confirm you want to delete VGM Certificate <b>Cert No - ${data.cert_no} | Serial No - ${data.serial_no} </b> </span>
                </div>
                <input type="hidden" name="detail_id" value="${data.id}">
                <div class="form-group">
                    <label for="" class="control-label">Reason for deleting</label>
                    <textarea name="reason" required id="" class="form-control"></textarea>
                </div>
            
            `).clone();
            return body;
        }
        $('#delete-cert').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = getdeletebody(data);
            $('#delete-cert').find('.modal-body').empty();
            $('#delete-cert').find('.modal-body').append(body);
        })
    })
</script>
@endsection