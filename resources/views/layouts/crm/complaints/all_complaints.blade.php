@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
  <title>All Complaints</title>
@endsection
@section('content2')
  <main>
    <?php
        $workflows = getComplaintWorkflow();
      $items = array(
        array(
            'link' => route('customers-list'),
            'name' => 'CRM',
            'icon' => null
          ),
        array(
            'link' => null,
            'name' => 'All complaints',
            'icon' => null
          )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-comment-alert"></i>All Complaints
      
    </h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="complaints-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="all-complaint" data-toggle="tab" href="#all-complaints-tab" role="tab" aria-controls="all-complaints" aria-selected="true"><i style="font-size: 15px;" class="mdi mdi-comment-alert"></i>All Complaints</a>
				</li>
				<li class="nav-item">
                	<a class="nav-link " id="solved-complaints" data-toggle="tab" href="#solved-complaints-tab" role="tab" aria-controls="solved-complaints" aria-selected="true"><i style="color:green;font-size:15px" class="mdi mdi-comment-alert"></i>Approved Complaints</a>
                </li>
                <li class="nav-item">
                	<a class="nav-link " id="cancelled-complaints" data-toggle="tab" href="#cancelled-complaints-tab" role="tab" aria-controls="canceled-complaints" aria-selected="true"><i style="color: red;font-size:15px" class="mdi mdi-comment-alert"></i>Rejected Complaints</a>
                </li>
			</ul>
		</div>
		<div class="tab-content" id="complaints-tabs-content">
			<div class="tab-pane fade show active p-3" id="all-complaints-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">Complaints </h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
                            <th>Priority</th>
                            <th>Complaint No</th>
                            <th>Complaint Type</th>
							<th>Received From</th>
							<th nowrap>Registred_By</th>
                            <th>Date</th>
                            <th>Created At</th>
							<th>Status</th>
							<!-- ---  -->
							<th nowrap>WorkFlow Stage</th>
							
							<th>Description</th>
							<th></th>
							
						</tr>
						</thead>
						<tbody>
									@foreach ($complaints as $item)
										<tr>
											<td valign="center">{{ $loop->iteration }}</td>
											
                                            <td>
                                                {!! $item->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$item->priority !!}
                                            </td>
                                            <td>{{ $item->complaint_id}}</td>
                                            <td>{{$item->type}}</td>
											<td nowrap>{{ $item->received_from}}</td>
											<td>{{ $item->registered_by}}</td>
                                            <td>{{ $item->date }}</td>
                                            <td>{{$item->created_at}}</td>
											<!-- -----  -->
											<td class="text-small">{!! $item->rejected == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
											
											<td>{{$workflows[$item->complaint_workflow]}}</td>
												
											
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
                                            <td> <a class="btn btn-outline-success btn-sm" href="{{ route('complaint-show', ['id'=> $item->id])}}"><i class="mdi mdi-eye-outline"></i></a></td>
										</tr>
									
									@endforeach
								</tbody>
					</table>
				</div>
			</div>
			<!-- ---------  -->
			<div class="tab-pane fade show  p-3" id="solved-complaints-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">Approved Complaints</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
                            <th>No</th>
                            <th>Priority</th>
                            <th>Complaint No</th>
                            <th>Complaint Type</th>
							<th>Received From</th>
                            <th nowrap>Registred_By</th>
                            
                            <th>Date</th>
                            <th>Created At</th>
							<th>Status</th>
							<!-- ---  -->
							<th nowrap>WorkFlow Stage</th>
							
							<th>Description</th>
							<th></th>
							<!-- ------  -->
							
							
						</tr>
						</thead>
						<tbody>
                            @foreach ($complaints as $item)
                            @if($item->rejected == 0)
                            <tr>
                                <td valign="center">{{ $loop->iteration }}</td>
                                
                                <td>{!! $item->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$item->priority !!}</td>
                                <td>{{ $item->complaint_id}}</td>
                                <td>{{$item->type}}</td>
                                <td nowrap>{{ $item->received_from}}</td>
                                <td>{{ $item->registered_by}}</td>
                                
                                <td>{{ $item->date }}</td>
                                <td>{{$item->created_at}}</td>
                                <!-- -----  -->
                                <td class="text-small">{!! $item->rejected == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                
                                <td>{{$workflows[$item->complaint_workflow]}}</td>
                                    
                                
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
                                <td> <a class="btn btn-outline-success btn-sm" href="{{ route('complaint-show', ['id'=> $item->id])}}"><i class="mdi mdi-eye-outline"></i></a></td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
					</table>
                </div>
			</div>
            <div class="tab-pane fade show  p-3" id="cancelled-complaints-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">Cancelled Complaints</h5>
				<div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
                            <th>No</th>
                            <th>Priority</th>
                            <th>Complaint No</th>
                            <th>Complaint Type</th>
							<th>Received From</th>
                            <th nowrap>Registered_By</th>
                            
                            
                            <th>Date</th>
                            <th>Created At</th>
							<th>Status</th>
							<!-- ---  -->
							<th nowrap>WorkFlow Stage</th>
							
							<th>Description</th>
							<th></th>
							<!-- ------  -->
							
							
						</tr>
						</thead>
						<tbody>
                            @foreach ($complaints as $item)
                            @if($item->rejected == 1)
                            <tr>
                                <td valign="center">{{ $loop->iteration }}</td>
                                
                                <td>{!! $item->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$item->priority !!}</td>
                                <td>{{ $item->complaint_id}}</td>
                                <td>{{$item->type}}</td>
                                <td nowrap>{{ $item->received_from}}</td>
                                <td>{{ $item->registered_by}}</td>
                                
                                <td>{{ $item->date }}</td>
                                <td>{{$item->created_at}}</td>
                                <!-- -----  -->
                                <td class="text-small">{!! $item->rejected == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                
                                <td>{{$workflows[$item->complaint_workflow]}}</td>
                                    
                                
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
                                <td> <a class="btn btn-outline-success btn-sm" href="{{ route('complaint-show', ['id'=> $item->id])}}"><i class="mdi mdi-eye-outline"></i></a></td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
					</table>
				</div>
			</div>
			<!-- -----  -->
		</div>
	</div>
	<!-- -------------end----- -->
    
  </main>
@endsection

@section('script2')
  
@endsection