@extends('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
  <title>Personnel Certification</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
          'name' => 'Personnel',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Certification',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-file-certificate"></i>Certifications
      
      <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-qualification"><i class="mdi mdi-plus"></i> Add</button>
      
    </h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="qualifications-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="qualifications" data-toggle="tab" href="#all-qualification-tab" role="tab" aria-controls="qualifications" aria-selected="true"><i style="font-size: 15px;" class="mdi mdi-file-certificate"></i> Cerifications</a>
                </li>
                <li class="nav-item">
					<a class="nav-link " id="archivedqualifications" data-toggle="tab" href="#archived-qualification-tab" role="tab" aria-controls="archivedqualifications" aria-selected="true"><i style="font-size: 15px;color:red" class="mdi mdi-file-certificate"></i> Archive Certifications</a>
				</li>
				
			</ul>
		</div>
		<div class="tab-content" id="complaints-tabs-content">
			<div class="tab-pane fade show active p-3" id="all-qualification-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-certificate-outline"></i>Certifications</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
                            <th nowrap>Name</th>
                           
                            <th>Created</th>
                            <th>Status</th>
                            <th>Edit By</th>
                            <th nowrap>Description</th>
                            <th></th>

                            <!-- ---  -->
                            
						</tr>
						</thead>
						<tbody>
                            @foreach ($qualifications as $item)
                            @if($item->status== 0 )
                            
                                <tr>
                                    <td valign="center">{{ $loop->iteration }}</td>
                                    
                                    <td>{{ $item->name }}</td>
                                    
                                    <td>{{$item->created_at}}</td>           
                                    <td class="text-small">{!! $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                    <td>{!! $item->edited_by == '' ? 'N/a':$item->edited_by !!}</td>
                                    <td class="text-center">
                                        <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#qualification-description-{{$item->id}}"> <i class="mdi mdi-message-text"></i></span>
                                        <div id="qualification-description-{{$item->id}}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- Modal content-->
                                                <div class="modal-content" >
                                                    
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-eye"></i> {{$item->name}} Description</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                    <h5>Certification Description.</h5>
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
                                    
                                    
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-qualification-{{$item->id}}" > <i class="mdi mdi-pencil"></i> </span>
                                    
                                    <div id="edit-qualification-{{$item->id}}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <form action="{{route('edit-qualification',['id'=>$item->id])}}" method="post" class="modal-content" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Certification {{$loop->iteration}}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{$item->name}}" placeholder="Qualification Name...">
                                                </div>
                                                <div class="form-group hidden">
                                                    <label class="control-label">Current Name</label>
                                                    <input type="text" name="current" class="form-control" value="{{$item->name}}">
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label">Status</label>
                                                    <select name="status" class="form-control" placeholder="Operator...">
                                                        <option value=0 {{$item->status == 0 ? 'selected' : ''}}>Active</option>
                                                        <option value=1 {{$item->status == 1 ? 'selected' : ''}}>Archived</option>
                                                        
                                                    </select>
                                                </div>      
                                                <div class="form-group" >
                                                    <label class="control-label">Description</label>
                                                    <textarea class="form-control" rows="4" name="description"value="" placeholder="Qualification Description..." required>{{$item->description}}</textarea>
                                                </div> 
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                    </td>
										</tr>
									@endif
									@endforeach
								</tbody>
					</table>
				</div>
			</div>
			<!-- ---------  -->
			<div class="tab-pane fade show p-3" id="archived-qualification-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-certificate-outline"></i>Archived Certifications</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
                            <th nowrap>Name</th>
                            <th>Created</th>                 
                            <th>Status</th>
                            <th>Edited_by</th>
                            <th nowrap>Description</th>
                            <th></th>

                            <!-- ---  -->
                            
						</tr>
						</thead>
						<tbody>
                            @foreach ($qualifications as $item)
                            @if($item->status == 1)
                                <tr>
                                    <td valign="center">{{ $loop->iteration }}</td>
                                    
                                    <td>{{ $item->name }}</td>
                                    <td>{{$item->created_at}}</td> 
                                    <td class="text-small">{!! $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>          
                                    <td>{{$item->edited_by}}</td>
                                    <td class="text-center">
                                        <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#description-qualification-{{$item->id}}"> <i class="mdi mdi-message-text"></i></span>
                                        <div id="description-qualification-{{$item->id}}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- Modal content-->
                                                <div class="modal-content" >
                                                    
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-eye"></i> {{$item->name}} Description</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                    <h5>Certification Description.</h5>
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
                                    
                                    
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-qualifications-{{$item->id}}" > <i class="mdi mdi-pencil"></i> </span>
                                    
                                    <div id="edit-qualifications-{{$item->id}}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <form action="{{route('edit-qualification',['id'=>$item->id])}}" method="post" class="modal-content" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$item->name}} Certification</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{$item->name}}" placeholder="Qualification Name...">
                                                </div>
                                                <div class="form-group hidden">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" name="current" class="form-control" value="{{$item->name}}" >                                                
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Status</label>
                                                    <select name="status" class="form-control" placeholder="Operator...">
                                                        <option value = 0 {{$item->status == 0 ? 'selected' : ''}}>Active</option>
                                                        <option value = 1 {{$item->status == 1 ? 'selected' : ''}}>Archived</option>
                                                        
                                                    </select>
                                                </div>
                                              
                                                <div class="form-group" >
                                                    <label class="control-label">Description</label>
                                                    <textarea class="form-control" rows="4" name="description"value="" placeholder="Qualification Description..." required>{{$item->description}}</textarea>
                                                </div> 
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                    </td>
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

  <div id="add-qualification" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-qualification') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Certification</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="Qualification Name...">
            </div>
            
            <div class="form-group" >
                <label class="control-label">Description</label>
                <textarea class="form-control" rows="4" name="description"value="" placeholder="Qualification Description..." required></textarea>
            </div> 
            <div class="form-group hidden">
                <label class="control-label">Module Code</label>
                <input type="number" name="module" value=1 class="form-control" placeholder="Module Code">
            </div>
        </div>

        
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>

@endsection