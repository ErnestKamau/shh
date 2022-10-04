@extends('layouts.workorder.layout.app', ['dataTable'=>false])

@section('title2')
  <title>Working Days Scheduler</title>
@endsection
@section('content2')
  <main>
    <?php
			$items = array(
				array(
					'link' => route('workorder-home'),
					'name' => 'Work Order Management',
					'icon' => null
				),
				array(
					'link' => '#',
					'name' => 'Working Days Scheduler',
					'icon' => null
				)
			);
		?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
      <i class="mdi mdi-toolbox-outline"></i> Working Days Scheduler
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-working-schedule"><i class="mdi mdi-plus"></i> Add</button>
		</h3>
		<div class="bg-light p-4">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th>Name</th>
							<th>Type</th>
							<th>Day</th>
							<th>Start</th>
							<th>End</th>
							<th>Action</th>
							<th>Duration</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@if(count($working_schedules) > 0)
							@foreach($working_schedules as $working_schedule)
								<tr>
									<td valign="center">{{ $loop->iteration }}</td>
									<td>{{ $working_schedule->name }}</td>
									<td>{{ $working_schedule->title }}</td>
									<td>{{ $working_schedule->day }}</td>
									<td>{{ $working_schedule->start_timeslot }}</td>
									<td>{{ $working_schedule->end_timeslot }}</td>
									<td>{{ $working_schedule->slot_type }}</td>
									<td>{{ $working_schedule->duration }}</td>
									<td>
										<span class="btn btn-trnsparent btn-sm text-primary" data-service="{{ json_encode($working_schedule) }}" data-target="#update-working-schedule-modal" data-toggle="modal">
											<i class="mdi mdi-pencil"></i>
										</span>
										<span class="btn btn-trnsparent btn-sm text-danger" data-service="{{ json_encode($working_schedule) }}" data-target="#remove-working-schedule-modal" data-toggle="modal">
											<i class="mdi mdi-delete"></i>
										</span>
									</td>
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
  </main>
@endsection
@section('script2')
	<?php
		$titles = ["Weekday", "Weekend", "Holiday", "Other"];
		$actions = ["Include", "Exclude"];
		$durations = ["Once", "Forever"];
	?>
  <div id="add-working-schedule" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('update-working-schedule') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Working Scheduler</h4>
        </div>
        <div class="modal-body">
					<div class="row">
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label>Name</label>
								<input type="text" name="name" placeholder="Schedule Name..." class="form-control" />
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Type</label>
								<select name="title" class="form-control">
									<option value="">Select Type...</option>
									@foreach ($titles as $title)
										<option value="{{ $title }}">{{ $title }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Day</label>
								<span class="day-fields">
									<span class="form-control">Select type first...</span>
								</span>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Action</label>
								<select name="action" class="form-control">
									<option value="">Select Action...</option>
									@foreach ($actions as $action)
										<option value="{{ $action }}">{{ $action }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Start Timeslot</label>
								<input type="time" class="form-control" placeholder="Start Timeslot..." name="start_timeslot" />
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">End Timeslot</label>
								<input type="time" class="form-control" placeholder="End Timeslot..." name="end_timeslot" />
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Duration</label>
								<select name="duration" class="form-control">
									<option value="">Select Duration...</option>
									@foreach ($durations as $duration)
										<option value="{{ $duration }}">{{ $duration }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div id="allow-dups"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="update-working-schedule-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST"  enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Timeslot</h4>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="remove-working-schedule-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST"  enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Timeslot</h4>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Yes, Remove it</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
	<script>
		$(function(){
			var dayTypeToDays = {
				"Weekday": {
					"field": "select",
					"days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
				},
				"Weekend": {
					"field": "select",
					"days": ["Saturday", "Sunday"]
				},
				"Holiday": {
					"field": "date",
					"days": []
				},
				"Other": {
					"field": "date",
					"days": []
				}
			}

			var getModalHTML = function(data){
				var $data = $(`
					<div class="row">
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label>Name</label>
								<input type="text" name="name" placeholder="Schedule Name..." value="${data.name}" class="form-control" />
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Type</label>
								<select name="title" id="titled-thing" class="form-control">
									<option value="">Select Type...</option>
									@foreach ($titles as $title)
										<option value="{{ $title }}" ${data.title == '{{ $title }}' ? 'selected' : '' }>{{ $title }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Day</label>
								<span class="day-fields">
									<span class="form-control">Select type first...</span>
								</span>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Action</label>
								<select name="action" class="form-control">
									<option value="">Select Action...</option>
									@foreach ($actions as $action)
										<option value="{{ $action }}" ${data.slot_type == '{{ $action }}' ? 'selected' : '' }>{{ $action }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Start Timeslot</label>
								<input type="time" class="form-control" placeholder="Start Timeslot..." value="${data.start_timeslot}" name="start_timeslot" />
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">End Timeslot</label>
								<input type="time" class="form-control" placeholder="End Timeslot..." value="${data.end_timeslot}"  name="end_timeslot" />
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label class="control-label">Duration</label>
								<select name="duration" class="form-control">
									<option value="">Select Duration...</option>
									@foreach ($durations as $duration)
										<option value="{{ $duration }}" ${data.duration == '{{ $duration }}' ? 'selected' : '' }>{{ $duration }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
				`);

				return $data;
			}

			var titleNameChanged = function($that){

			}

			$('#remove-working-schedule-modal').on('show.bs.modal', function(e){
				var target = $(e.relatedTarget).data('service');

				$(this).find('.modal-body').html(`
					<div class="alert alert-danger"><i class="mdi mdi-alert"></i> Are you sure that you want to remove the
						<strong>${target.name} <small>[${target.title}]</small> ${target.day} ${target.start_timeslot} to ${target.end_timeslot} </strong> timeslot?</div>
				`);

				$(this).find('form').attr('action', '/remove-working-schedule/'+target.id);
				$(this).find('form').prop('action', '/remove-working-schedule/'+target.id);
			});

			$('#update-working-schedule-modal').on('show.bs.modal', function(e){
				var target = $(e.relatedTarget).data('service');

				var modalHTML = getModalHTML(target);

				$(this).find('.modal-body').empty();
				$(this).find('.modal-body').append(modalHTML);

				$(this).find('.modal-body').find('#titled-thing').trigger('change');
				$(this).find('.modal-body').find('[name="selected_day"], [name="day[]"]').val(target.day);

				$(this).find('form').attr('action', '/update-working-schedule/'+target.id);
				$(this).find('form').prop('action', '/update-working-schedule/'+target.id);
			});

			$(document).on('change', 'select[name="title"]', function(){
				var typ = $(this).val();
				var field = dayTypeToDays[typ]['field'];
				var days = dayTypeToDays[typ]['days'];
				var modalP = $(this).parents('.modal');


				modalP.find('.day-fields').empty();
				$('#allow-dups').empty();

				if(field == 'select'){
					var $field = $(`<select class="form-control" name="selected_day" id="${Math.random()}" required><option>Select...</option></select>`);
					var daysDup = $(`<span></span>`);

					$.each(days, function(d, day){
						$field.append(`<option value="${day}">${day}</option>`);
						daysDup.append(`<span class="m-1" style="font-size:14px"><label><input type="checkbox" id="${day}-checkbox" name="day[]" value="${day}"> ${day}</label></span>`);
					});

					$field.on('change', function(){
						var daySelected = $(this).val();
						console.log(daySelected);
						var id = daySelected+"-checkbox";
						$('#allow-dups').html(`<strong>Apply working schedule to:</strong><br><hr>`);
						$('#allow-dups').append(daysDup);
						$('#allow-dups').find('#'+id).attr("checked", true);
						$('#allow-dups').find('#'+id).prop("checked", true);
					});
				}

				if(field == 'date'){
					var $field = $(`<input type="date" class="form-control" name="day[]" required />`);
				}

				modalP.find('.day-fields').append($field);

			});
		});
	</script>
@endsection
