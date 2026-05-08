@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
  <title>{{$stage}}</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('customers-list'),
          'name' => 'CRM',
          'icon' => null
        ),
        array(
          'link' => route('crm.complaints-manager',['stage'=>$stage]),
          'name' => $stage,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-comment-alert"></i>{{$stage}}
      @if ($stage == "Open Complaints")
      <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-complaint"><i class="mdi mdi-plus"></i> Add</button>
      @endif
    </h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="equipment-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="complaints" data-toggle="tab" href="#complaints-tab" role="tab" aria-controls="complaints" aria-selected="true"><i style="font-size: 15px;" class="mdi mdi-comment-alert"></i> {{$stage}}</a>
				</li>
				
			</ul>
		</div>
		<div class="tab-content" id="complaints-tabs-content">
			<div class="tab-pane fade show active p-3" id="complaints-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-comment-alert-outline"></i>Complaints</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th>Priority</th>
              <th nowrap>Complaint Number</th>
              <th>Complaint Type</th>
							<th>Received From</th>
              <th>Registered_by</th>
              <th>Date</th>
              <th>Created At</th>
              @if($stage == 'Cancelled Complaints')
              <th>Reject Stage</th>
              @endif
              <th>Description</th>
              <th></th>

							<!-- ---  -->
							
						</tr>
						</thead>
						<tbody>
									@foreach ($complaints as $item)
									
										<tr>
											<td valign="center">{{ $loop->iteration }}</td>
											
                      <td>{!! $item->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$item->priority !!}</td>
                      <td>{{ $item->complaint_id }}</td>
                      <td>{{$item->type}}</td>
											<td>{{ $item->received_from }}</td>
                      <td>{{ $item->registered_by }}</td>
                      <td>{{$item->date}}</td>
                      <td>{{$item->created_at}}</td>
                      @if($stage == 'Cancelled Complaints')
                      <?php 
                        $workflows = getComplaintWorkflow()[$item->reject_workflow];
                        
                      ?>
                      <td>{{$workflows}}</td>
                      @endif
											<!-- -----  -->
											
											<td class="text-center">
												<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#complaint-description-{{$item->id}}"> <i class="mdi mdi-message-text"></i></span>
												<div id="complaint-description-{{$item->id}}" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<div class="modal-content" >
															
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-eye"></i>{{$item->complaint_id}} Complaint Description</h4>
															</div>
															<div class="modal-body">
                                <h5>Complaint Description.</h5>
                                <div class="pane panel-default">
                                    <div class="panel-body">
                                        {{$item->description}}
                                    </div>
                                </div>
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
															</div>
                            </div>
													</div>
												</div>
                      </td>
                      <td>
                      <a class="btn btn-outline-success btn-sm" href="{{ route('show-complaint', ['id'=> $item->id])}}">
                          <i class="mdi mdi-eye-outline"></i></a>
                      @if($item->complaint_workflow<5)
                    
                      <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-complaint-{{$item->id}}" > <i class="mdi mdi-pencil"></i> </span>
                      @endif
                      <div id="edit-complaint-{{$item->id}}" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                          <form action="{{route('edit-complaint',['id'=>$item->id])}}" method="post" class="modal-content" enctype="multipart/form-data">
                              @csrf
                              <div class="modal-header">
                                  <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$item->complaint_id}} Complaint</h4>
                              </div>
                              <div class="modal-body">
                                  
                                  <div class="form-group">
                                      <label class="control-label">Priority</label>
                                      <select name="priority" class="form-control" placeholder="Operator...">
                                          <option value="high"{{$item->priority == 'high' ? 'selected' : ''}}>High</option>
                                          <option value="medium"{{$item->priority == 'medium' ? 'selected' : ''}}>Medium</option>
                                          <option value="low" {{$item->priority == 'low' ? 'selected' : ''}}>Low</option>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label class="control-label">Complaint Type</label>
                                      <select name="type" class="form-control" placeholder="Recieved From...">
                                          @foreach($complaint_types as $type)
                                          <option value="{{$type->name}}" {{$item->type == $type->name ? 'selected' : ''}}>{{$type->name}}</option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label class="control-label">Recieved From</label>
                                      <select name="received_from" class="form-control" placeholder="Recieved From...">
                                          @foreach($customers as $customer)
                                          <option value="{{$customer->name}}" {{$item->received_from == $customer->name ? 'selected' : ''}}>{{$customer->name}}</option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label">Date</label>
                                    <?php
                                      $date = date("Y-m-d",strtotime($item->date));
                                    ?>
                                    <input type="date" name="date" placeholder="Complaint Date..."value="{{$date}}" class="form-control" required>
                                  </div>
                                  <div class="form-group" >
                                      <label class="control-label">Description</label>
                                      <textarea class="form-control" rows="4" name="description"value="" placeholder="Compliant Description..." required>{{$item->description}}</textarea>
                                  </div> 
                              </div>
                              <div class="modal-footer">
                                  <button type="submit" class="btn btn-primary"> <i class="md mdi-content-save"></i> Update</button>
                                  <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                              </div>
                          </form>
                        </div>
                      </div>
                      </td>
										</tr>
									
									@endforeach
								</tbody>
					</table>
				</div>
			</div>
			<!-- ---------  -->
			
			<!-- -----  -->
		</div>
	</div>
	<!-- -------------end----- -->
    
  </main>
@endsection

@section('script2')
@if($stage == "Open Complaints")
  <div id="add-complaint" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-complaint') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Complaint</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label">Priority</label>
                <select name="priority" id="assign-status" class="form-control" readonly="true" placeholder="Assign Priority...">
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Complaint Type <span class="text-danger">*</span></label>
                <select name="type" class="form-control" required placeholder="Recieved From...">
                    @foreach($complaint_types as $type)
                    <option value="{{$type->name}}" >{{$type->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Received from <span class="text-danger">*</span></label>
                <select name="received_from" id="assign-employee" required class="form-control" readonly="true" placeholder="Received from...">
                    @foreach ($customers as $customer)
                    <option value="{{$customer->name}}">{{$customer->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
              <label class="control-label">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" value="" placeholder="Complaint Date..." class="form-control" required>
            </div>
            <div class="form-group" >
                <label class="control-label">Complaint description <span class="text-danger">*</span></label>
                <textarea class="form-control" rows="4" name="description"value="" placeholder="Compliant Description..." required></textarea>
            </div> 
           
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
@endif
@endsection