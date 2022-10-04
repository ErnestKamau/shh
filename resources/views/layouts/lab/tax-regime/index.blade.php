@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
	<title> Tax-Regime </title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
		}
		/* Default mode */
		.tab-card-header > .nav-tabs {
			border: none;
			margin: 0px;
		}
		.tab-card-header > .nav-tabs > li {
			margin-right: 2px;
		}
		.tab-card-header > .nav-tabs > li > a {
			border: 0;
			border-bottom:2px solid transparent;
			margin-right: 0;
			color: #737373;
			padding: 2px 15px;
		}

		.tab-card-header > .nav-tabs > li > a.show {
			border-bottom:2px solid #007bff;
			color: #007bff;
		}
		.tab-card-header > .nav-tabs > li > a:hover {
			color: #007bff;
		}

		.tab-card .nav-link.active{
			background-color: #dadccd !important;
			border: 1px solid #cccebf !important;
		}

		.tab-card-header > .tab-content {
			padding-bottom: 0;
		}

		.my-small-text{
			font-size: 13px !important;
		}

		.removeThis {
			z-index: 12;
			position: absolute;
			cursor: pointer;
			top: 0px;
			right: 2px;
			padding: 1px 4px;
			font-size: 12px;
			background-color: red;
			border-radius: 50%;
			color: #fff;
			box-shadow: 0px 0px 5px rgba(0,0,0,0.08);
		}

	</style>
@endsection
@section('content2')
	<main>
		<?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Tax Regime',
          'icon' => null
        ),
			
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h2 class="p-4">
        <i class="fas fa-money-bill-alt" ></i> Tax Regime
       
		</h2>
        
        <div class="row no-gutter">
            <div class="col-xl-4 col-sm-6 p-3">
                <div class="card p-3">
                    <form action="{{ route('add-tax') }}" method="post">
                        @csrf 
                        <h4 class="card-title">
                            <i class="mdi mdi-plus text-info"></i> Add Tax value!
                        </h4>
                        <hr>
                        <div class="card-body">
                            
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="control-label">Tax value (%)</label>
                                    <input type="number" name="value" class="form-control" required/>
                                </div>
                                <div class="form-group">
                                <label class="control-label"><input type="checkbox" value="1" name="active" /> Set As Default Tax ?</label>
                                </div>
                            </div> 
                        </div>
                        <button class="btn btn-outline-success btn-sm bg-light" style="width: 100%;font-size:15px;font-weight:600;color:black" type="submit"> Save</button>
                        
                    </form>
                </div>
            </div>
            <div class="col-xl-8 col-sm-6">
                <div class="card tab-card">
                    <div class="card-header tab-card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
                            
                            <li class="nav-item">
                                <a class="nav-link" id="tax-tab" data-toggle="tab" href="#Regime" role="tab" aria-controls="Regime" aria-selected="true"><i style="font-size: 20px;" class="fas fa-money-bill-alt"></i> Tax Regime</a>
                            </li>
                            
                        </ul>
                    </div>
                    <div class="tab-content" id="Invoice-tabs-content">
                        
                        <div class="tab-pane fade show active p-3" id="Invoice" role="tabpanel" aria-labelledby="one-tab">
                            <h5 class="card-title"><i class="fas fa-money-bill-alt"></i> Tax Regime </h5>
                            <div class="table-responsive">
                                <table
                                    class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                                    <thead class="bg-light p-2">
                                        <tr>
                                            <th>No</th>
                                            <th>Created At</th>
                                            <th>Registered By</th>
                                            <th>Value</th>
                                            <th>Active</th>
                                            <th>End Date</th>
                                            <th></th>
                             
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($taxes as $tax)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$tax->created_at}}</td>
                                            <?php
                                            $user = getUserById($tax->registered_by);
                                            ?>
                                            <td>{{$user->name}}</td>
                                            <td>{{$tax->value}}%</td>
                                            <td nowrap>{!! $tax->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-marker-check text-muted"></i>' !!}</td>
                                            <td>{!! $tax->end_date == '' ? 'To Date':$tax->end_date !!}</td>
                                            <td>
                                                <span class="btn btn-outline-default btn-sm" data-toggle="modal" data-target="#edit-tax-{{$tax->id}}"><i class="mdi mdi-pencil text-info"></i></span>
                                                <div class="modal fade" id="edit-tax-{{$tax->id}}">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('edit-tax',['id'=>$tax->id]) }}" method="post" class="modal-content">
                                                            @csrf 
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Tax {{$loop->iteration}}</h5>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label class="control-label">Tax value (%)</label>
                                                                    <input type="number" name="value" value={{$tax->value}} class="form-control" required/>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label"><input type="checkbox" value="1" {{ $tax->active == 1 ? 'checked':'' }} name="active" /> Set As Default Tax ?</label>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
                                                                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
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
                    </div>
                </div>
            </div>
        </div>			
	</main>
@endsection
@section('script2')




@endsection
