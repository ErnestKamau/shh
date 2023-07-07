@extends('layouts.lab.layout.app', ['dataTable'=>true, 'datePicker'=>true, 'select2'=>true])

@section('title2')
<title>Inter Laboratory Transfer Log(s)</title>

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
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ),
       
        array(
            'link' => route('interLabTransferIndex'),
            'name' => 'Inter Lab Logs',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h4 class="p-4">
        <span class="float-left"><i class="mdi mdi-swap-horizontal-bold"></i> Inter Laboratory Transfer Log(s)</span>
        <div class="btn-group float-right">
            <button type="button" class="btn btn-sm btn-white dropdown-toggle" style="box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Actions
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <li>
                    <span class="btn btn-sm dropdown-item" data-action="bulk" data-toggle="modal" disabled data-target="#change-interlab-status">
                        <i class="mdi mdi-thumbs-up-down mr-2"></i> Approve / Reject Inter Lab Log(s)
                    </span>
                </li>

                <li>
                    <span class="btn btn-sm dropdown-item" data-toggle="modal" disabled data-target="#delete-interlab">
                        <i class="mdi mdi-delete-empty mr-2"></i> Delete Inter Lab Log(s)
                    </span>
                </li>
                <li>
                    <a href="{{route('interLabTransferIndex',['is_archived'=>1])}}" class="dropdown-item"><i class="mdi mdi-cogs mr-2"></i> Pull Archive</a>
                </li>


            </div>
        </div>

    </h4>
    <div class="table-responsive p-2 card mt-4">
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="width:200%" id="interlabbookingtable">
            <thead class="bg-light">
                <tr>
                    <th>
                        <input type="checkbox" name="selected_inter_lab_all" class="selected_inter_lab_all" id="">
                    </th>
                    <th>Status</th>
                    <th>Batch</th>
                    <th>Sample/Job No</th>
                    <th>From Lab</th>
                    <th>To Lab</th>
                    <th>Sample Type</th>
                    <th>Qty</th>
                    <th>Submitted By</th>
                    <th>Date Submitted</th>
                    <th>Recieved By</th>
                    <th>Date Received</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($interlabs as $ilabs)
                <tr>
                    <td style="width:5% !important">
                        @if($ilabs->status == 0)
                        <input type="checkbox" value="{{$ilabs->id}}" data-code="{{$ilabs->sample_code}}" name="selected_inter_lab" class="selected_inter_lab" id="">
                        <span class="btn btn-sm btn-default text-warning" data-toggle="modal" data-record="{{json_encode($ilabs)}}" data-target="#change-interlab-status" data-action="single"><i class="mdi mdi-thumbs-up-down" data-toggle="tooltip" title="Approve / Rejected Inter Lab"></i></span>
                        <span class="btn btn-default text-primary btn-sm initiate-interlab" data-record="{{json_encode($ilabs)}}" data-toggle="modal" data-target="#inter-lab-add" data-action="edit"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit Inter Lab"></i></span>
                        @endif
                    </td>
                    <td style="width:8% !important">
                        @if($ilabs->status == 0)
                        <span class="badge badge-pill p-2 badge-primary"><i class="mdi mdi-alert-decagram-outline"></i> Awaiting Approval</span>
                        @elseif($ilabs->status == 1)
                        <span class="badge badge-pill p-2 badge-success"><i class="mdi mdi-thumb-up"></i> Approved</span>
                        @else
                        <span class="badge badge-pill p-2 badge-danger"><i class="mdi mdi-alert-decagram-outline"></i> Rejected</span>
                        @endif 
                    </td>
                    <td>{{$ilabs->batch_code}}</td>
                    <td style="width:7% !important">{{$ilabs->sample_code}}</td>
                    <td style="width:10% !important">{{$ilabs->from_lab_section_id > 0 ? $ilab->from_lab_name : 'Reception'}}</td>
                    <td style="width:10% !important">{{$ilabs->to_lab_code}} - {{$ilabs->to_lab_name}}</td>
                    <td style="width:7% !important">{{$ilabs->sample_type_name}}</td>
                    <td>{{$ilabs->quantity}}</td>
                    <td>{{$ilabs->submitted_by_name}}</td>
                    <td>{{$ilabs->date_submitted}}</td>
                    <td>{{$ilabs->received_by_name}}</td>
                    <td>{{$ilabs->date_received}}</td>
                    <td>{{$ilabs->remarks}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection

@section('script2')
<div class="modal fade" id="delete-interlab" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('deleteInterLabTransferLogs')}}" method="post">
                @csrf  
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-sm btn-outline-danger btn affect-button"><i class="mdi mdi-delete-empty"></i> Yes, Delete</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="change-interlab-status" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('changeInterLabLogStatus')}}" method="post">
				@csrf  
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn-sm btn-outline-success btn affect-button"><i class="mdi mdi-content-save"></i> Yes, Affect</button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Cancel</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="inter-lab-add" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('create_sample_inter_lab_log')}}" method="post">
				@csrf  
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<Button type="submit" class="btn btn-sm submit-button"><i class="mdi mdi-swap-horizontal-bold"></i> Initiate</Button>
					<span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Cancel</span>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script type="text/javascript">
    $(()=>{
        $('.selected_inter_lab_all').on('change',(e)=>{
			if($('.selected_inter_lab_all').is(':checked')){
				$.each($('.selected_inter_lab'),(i,obj)=>{
					$(obj).attr('checked',true)
				})
			}
		});

        var deleteInterLabBody = (data)=>{
            var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty" style="font-size: 30px;"></i>
                    <span class="p-2">Confirm You want to delete the following Inter Laboratory Transfer Log(s):</span>
                </div>
                <input type="hidden" name="inter_lab_ids" value="${data['bulk']}">
                <p>Inter Lab Transfer Log(s):</p>
                <div class="row p-2" id="row-data">

                </div>
            `).clone();
            return body;
        }
        $('#delete-interlab').on('show.bs.modal',(e)=>{
            var record = [];
            var sample_codes = [];
            $.each($('.selected_inter_lab:checked'),(i,obj)=>{
                record.push($(obj).val());
                sample_codes.push($(obj).data('code'));
            })
            var data = {
                "sample_codes":sample_codes,
                "bulk": record.join(',')
            }

            if(sample_codes.length == 0){
                var body = `
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-alert-decagram-outline" style="font-size: 30px;"></i>
                    <span class="p-2">
                        Kindly select the Inter Laboratory Transfer Log(s) you want to affect their status
                    </span>
                </div>
                `;
                $('#delete-interlab').find('.affect-button').addClass('hidden');
            }else{
                var body = deleteInterLabBody(data);
                $('#delete-interlab').find('.affect-button').removeClass('hidden');

            }
                
            $('#delete-interlab').find('.modal-body').empty();
            $('#delete-interlab').find('.modal-body').append(body);

            if(sample_codes.length > 0){
                console.log(sample_codes);
                $.each(sample_codes,(i,obj)=>{
                    console.log(obj);
                    var colBody = `
                    <div class="col-md-4 p-1">
                        <span><i class="mdi mdi-chevron-right"></i> ${obj}</span>
                    </div>
                    `;
                    $('#delete-interlab').find('.modal-body').find('#row-data').append(colBody);
                });
            }
        })


		var getLabs = (analysis_type_ids,callback)=>{
			$.ajax({
				url:'/get/Labs-By-Analysis/Type-Id-Ajax',
				method:"GET",
				data:{
					ids : analysis_type_ids
				},
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			})

		}

		var getChangeInterLabStatusBody = (data,bulk = false)=>{
			if(bulk){
				var body = $(`
				<div class="alert alert-success p-2 d-flex">
					<i class="mdi mdi-thumbs-up-down" style="font-size: 30px;"></i>
					<span class="p-2">
						Affect the Inter Lab Log(s) status for the following sample(s) below by providing the following information: 
					</span>
				</div>
				<div class="label control-label">
					<label for="" class="control-label">Status</label>
					<select name="status" id="" class="form-control status-field">
						<option value="">Select Status ...</option>
						<option value="1">Approve Inter Lab Log</option>
						<option value="2">Reject Inter Lab Log</option>
					</select>
				</div>
				
				<input type="hidden" name="inter_lab_ids" value="${bulk['bulk']}">
				<p class="p-2"><u>Inter Lab Transfer Log(s): </u></p>
				<div class="row" id="row-data">
					
				</div>
				`).clone();
			}else{
				var body = $(`
					<div class="alert alert-success p-2 d-flex">
						<i class="mdi mdi-thumbs-up-down" style="font-size: 30px;"></i>
						<span class="p-2">
							Affect the Inter Lab Log status for sample ${data.sample_code} below:
						</span>
					</div>
					<div class="form-group">
						<label for="" class="control-label">From Lab</label>
						<input type="text" readonly class="form-control" value="${data.from_lab_section_id > 0 ? data.from_lab_name : 'Reception' } ">
					</div>
					<div class="form-group">
						<label for="" class="control-label">To Lab</label>
						<input type="text" readonly class="form-control" value="${data.to_lab_name}">
					</div>
					<div class="label control-label">
						<label for="" class="control-label">Status</label>
						<select name="status" id="" class="form-control status-field">
							<option value="">Select Status ...</option>
							<option value="1">Approve Inter Lab Log</option>
							<option value="2">Reject Inter Lab Log</option>
						</select>
					</div>
					<input type="hidden" name="inter_lab_id" value="${data.id}">
					
	
	
				`).clone();

			}
			$(body).find('.status-field').select2();
			return body;
		}

		$('#change-interlab-status').on('show.bs.modal',(e)=>{
			var action = $(e.relatedTarget).data('action');

			if(action == 'bulk'){
				var record = [];
				var sample_codes = [];
				$.each($('.selected_inter_lab:checked'),(i,obj)=>{
					record.push($(obj).val());
					sample_codes.push($(obj).data('code'));
				})
				var data = {
					"sample_codes":sample_codes,
					"bulk": record.join(',')
				}

				if(sample_codes.length == 0){
					var body = `
					<div class="alert alert-primary p-2 d-flex">
						<i class="mdi mdi-alert-decagram-outline" style="font-size: 30px;"></i>
						<span class="p-2">
							Kindly select the Inter Laboratory Transfer Log(s) you want to affect their status
						</span>
					</div>
					`;
					$('#change-interlab-status').find('.affect-button').addClass('hidden');
				}else{
					var body = getChangeInterLabStatusBody("no data",data);
					$('#change-interlab-status').find('.affect-button').removeClass('hidden');

				}
					
				$('#change-interlab-status').find('.modal-body').empty();
				$('#change-interlab-status').find('.modal-body').append(body);

				if(sample_codes.length > 0){
					console.log(sample_codes);
					$.each(sample_codes,(i,obj)=>{
						console.log(obj);
						var colBody = `
						<div class="col-md-4 p-1">
							<span><i class="mdi mdi-chevron-right"></i> ${obj}</span>
						</div>
						`;
						$('#change-interlab-status').find('.modal-body').find('#row-data').append(colBody);
					});
				}

				
			}else{

				var record = $(e.relatedTarget).data('record');
				var body = getChangeInterLabStatusBody(record);
				$('#change-interlab-status').find('.modal-body').empty();
				$('#change-interlab-status').find('.modal-body').append(body);
			}
		})
        var getSampleCurrentLab = (id,callback)=>{
			$.ajax({
				url:`/getSampleCurrentLabSection/${id}`,
				method:'GET',
				success:(data)=>{
					callback(data);
				},
				error:(data)=>{
					console.log(data);
				}
			});
		}

		var getInterLabBody = (analysis_type_id,sample_id,sample_code,action,data = false)=>{
            var body = $(`
                <div class="alert alert-primary d-flex p-2">
                    <i class="mdi mdi-swap-horizontal-bold" style="font-size: 30px;"></i>
                    <span class="p-2">
                        Edit Inter Laboratory Transfer for sample <b>${data.sample_code}</b> by providing the information below.
                    </span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">From Lab</label>
                    <input type="text" class="form-control"  value="${data.from_lab_name  || 'Reception'}" readonly>
                </div>
                <input type="hidden" name="interlab_id" value="${data.id}">
                <input type="hidden" name="sample_id" value="${data.sample_id}">

                <div class="form-group">
                    <label for="" class="control-label">To Lab</label>
                    <select name="to_lab_section_id" id="" class="form-control to_lab_section_id">
                        
                    </select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Quantity</label>
                    <input type="text" name="quantity" value="${data.quantity}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Expected Date</label>
                    <input type="date" name="expected_date" value="${data.expected_date}" id="" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Notify</label>
                    <select name="notify_user" id="" class="form-control notify_user">
                        @foreach($users as $user)
                        <option value="{{$user->id}}">{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Notify</label>
                    <select name="also_notify[]" multiple id="" class="form-control also_notify">
                        @foreach($users as $user)
                        <option value="{{$user->id}}">{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
            `).clone();
        
			
			if(!data){
				getSampleCurrentLab(data ? data.sample_id : sample_id,(obj)=>{
					$(body).find('.from_lab_section').val(obj);
				});
			}
			getLabs(analysis_type_id,(labdata)=>{
				$.each(labdata,(i,obj)=>{
					var option = `<option value="${obj.id}">${obj.code} - ${obj.name}</option>`
					$(body).find('.to_lab_section_id').append(option);
				});
			});
			if(data){
				$(body).find('.to_lab_section_id').val(data.to_lab_section_id)
			}
			$(body).find('.to_lab_section').select2();
			$(body).find('.notify_user').select2();
			$(body).find('.also_notify').select2();

			return body;
		}

		$('#inter-lab-add').on('show.bs.modal',(e)=>{
			var record_id = $(e.relatedTarget).data('sample');
			var record_code = $(e.relatedTarget).data('samplecode');
			var action = $(e.relatedTarget).data('action');
			var data = action == 'add' ? false :  $(e.relatedTarget).data('record');
			var analysis_types = action == 'add' ? $(e.relatedTarget).data('analysistype') : data.analysis_type_id;
			action == 'add' ? $('#inter-lab-add').find('.submit-button').addClass('btn-outline-warning') : $('#inter-lab-add').find('.submit-button').addClass('btn-outline-primary');

			action == 'add' ? $('#inter-lab-add').find('.submit-button').removeClass('btn-outline-primary') : $('#inter-lab-add').find('.submit-button').removeClass('btn-outline-warning');

			var body = getInterLabBody(analysis_types.split(','),record_id,record_code,action,data)
			$('#inter-lab-add').find('.modal-body').empty();
			$('#inter-lab-add').find('.modal-body').append(body);
			
		})
    })
</script>
@endsection