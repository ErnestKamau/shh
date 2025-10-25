@extends('layouts.inventory.suppliers.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> {{ $supplier->name }} | Supplier </title>
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
          'link' => route('supplier-dashboard-home'),
          'name' => 'Dashboard',
          'icon' => null
        ),
        array(
          'link' => route('get-rfqs-item',['id'=>$rfq->id]),
          'name' => 'Rfq-'.$rfq->request_code,
          'icon' => null
        ),
        array(
            'link' => route('rfq-item',['id'=>$rfq_item->id]),
            'name' => 'Rfq-item-'.$rfq_item->id,
            'icon' => null
          ),
          array(
            'link' => route('get-quotation',['id'=>$quotation->id]),
            'name' => 'Quote-'.$quotation->id,
            'icon' => null
          ),
				array(
          'link' => '#',
          'name' => $supplier->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
    <i class="mdi mdi-star-box-multiple-outline" ></i> Quotation For Item {{$rfq_item->id}} <i class="mdi mdi-star"></i> 
    </h2>
    
    
    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
                
                <li class="nav-item">
                    <a class="nav-link" id="quotation-tab" data-toggle="tab" href="#Quotations" role="tab" aria-controls="Quotations" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-star-box-multiple-outline"></i> Quotation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-toggle="tab" href="#Notes" role="tab" aria-controls="Notes" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-note-multiple"></i> Notes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="attachment-tab" data-toggle="tab" href="#Attachment" role="tab" aria-controls="Attachment" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-attachment"></i> Attachment</a>
                </li>
                
            </ul>
        </div>
        <div class="tab-content" id="quotation-tabs-content">
            <div class="tab-pane fade show active p-3" id="Quotations" role="tabpanel" aria-labelledby="one-tab">
                <div class="card container mt-5 bg-warning" style="width: 70%;">
                    <h5 class="card-title p-2"><i class="mdi mdi-star-box-multiple"></i> Quotation For Item {{$rfq_item->id}}</h5>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-condensed my-small-text table-striped table-banded no-header table-hover table-bordered table-sm">
                               <tr >
                                   <th><b>Supplier: </b></th>
                                    <td>{{$supplier->name}}</td>
                               </tr>
                               <tr>
                                   <th><b>Request For Quotation Code: </b></th>
                                   <td>{{$rfq->request_code}}</td>
                               </tr>
                               <tr>
                                   <th><b>Rfq Item ID: </b></th>
                                   <td>{{$rfq_item->id}}</td>
                               </tr>
                               <tr>
                                   <th><b>Quote Ammount: </b></th>
                                   <td> {{number_format($quotation->quote_amount)}}</td>
                               </tr>
                               <tr>
                                   <th><b>Created At: </b></th>
                                   <td>{{$quotation->created_at}}</td>
                               </tr>
                               <tr>
                                   <th>Registered By</th>
                                   <td>{{getUserById($quotation->registered_by)->name}}</td>
                                   
                               </tr>
                               <tr>
                                   <th><b>Awarded: </b></th>
                                   <td>{!! $quotation->is_awarded == 1 ? '<i class="mdi mdi-marker-check text-success"></i> Awarded' : '<i class="mdi mdi-close-circle text-danger"></i> Not Awarded' !!}</td>
                               </tr>
                               <tr>
                                   <th><b>Awarded At: </b></th>
                                   <td>{{$quotation->awarded_at}}</td>
                               </tr>
                            </table>
                        </div>
                    </div>

                </div>
     
            </div>
            <div class="tab-pane fade p-3" id="Notes" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">
                        <i class="mdi mdi-note-multiple"></i> Quotation Notes
                        <button class="btn btn-outline-info btn-sm float-right ml-3" data-toggle="modal" data-target="#add-quotation-notes"><i  class="mdi mdi-plus"></i> Add</button>
                    </h5>
                    <div class="table-responsive">
                        <table
                            class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th>RFQ Code</th>
                                    <th>RFQ Item ID</th>  
                                    <th>Registered By</th>                                
                                    <th>Created_at</th>
                                    <th>Description</th>
                                    <th></th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notes as $note)
                                <td>{{$loop->iteration}}</td>
                                <td>{{$rfq->request_code}}</td>
                                <td>{{$rfq_item->id}}</td>
                                <td>
                                    <?php
                                    $user = getUserById($note->registered_by);
                                    ?>
                                    {{$user->name}}
                                </td>
                                <td>{{$note->created_at}}</td>
                                <td>
                                    <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#note-description-{{$note->id}}"><i class="mdi mdi-message"></i></span>
                                    <div class="modal fade" id="note-description-{{$note->id}}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title">Quotaion Notes {{$loop->iteration}}</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>Description</h5>
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <p>{{$note->description}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="{{route('delete-notes',['id'=>$note->id])}}" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete-empty"></i></a></td>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            
            <div class="tab-pane fade p-3" id="Attachment" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">
                        <i class="mdi mdi-attachment"></i> Quotation Attachments
                        <button class="btn btn-outline-info btn-sm float-right ml-3" data-toggle="modal" data-target="#add-quotation-attachment"><i  class="mdi mdi-plus"></i> Add</button>
                    </h5>
                    <div class="table-responsive">
                        <table
                            class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th>RFQ Code</th>
                                    <th>RFQ Item ID</th>  
                                    <th>Registered By</th>                                
                                    <th>Created_at</th>
                                    <th>Attachment</th>
                                    <th>Description</th>
                                    <th></th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attachments as $attacho)
                                <td>{{$loop->iteration}}</td>
                                <td>{{$rfq->request_code}}</td>
                                <td>{{$rfq_item->id}}</td>
                                <td>
                                    <?php
                                    $user = getUserById($attacho->registered_by);
                                    ?>
                                    {{$user->name}}
                                </td>
                                <td>{{$attacho->created_at}}}</td>
                                <td style="text-align: center;"><a href="{{ $attacho->attachment }}" class="btn btn-sm btn-transparent" target="_blank"><i class="mdi mdi-download text-success"></i></a></td>
                                <td>
                                    <span class="btn btn-outline-dark btn-sm" data-target="#attachment-description-{{$attacho->id}}" data-toggle="modal" ><i class="mdi mdi-message"></i></span>
                                    <div class="modal fade" id="attachment-description-{{$attacho->id}}">
                                        <div class="modal-dialog">
                                        <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title">Quotaion Attachment Description {{$loop->iteration}}</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>Description</h5>
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <p>{{$attacho->description}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="{{route('delete-attachment',['id'=>$attacho->id])}}" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete-empty"></i></a></td>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>

    <div class="modal fade" id="add-quotation-notes">
        <div class="modal-dialog">
            <form action="{{ route('add-quotation-note') }}" method="post" class="modal-content">
                @csrf 
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-plus"></i> Add Quoation Notes
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea name="note" rows="6" class="form-control" placeholder="Quotation Notes..." required></textarea>
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Request ID</label>
                        <input type="number" name="request_id" value ={{$rfq->id}} class="form-control">
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Request Item ID</label>
                        <input type="number" name="request_item_id" value={{$rfq_item->id}} class="form-control">
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Quotation ID</label>
                        <input type="number" name="quotation_id" value={{$quotation->id}} class="form-control">
                        
                    </div>           
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Submit</button>
                <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="add-quotation-attachment">
        <div class="modal-dialog">
            <form action="{{route('add-attachments')}}" method="post" class="modal-content" enctype="multipart/form-data">
                @csrf 
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-plus"></i> Add Quoation Attachments
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Choose File</label>
                        <input type="file" name="attachment" class="form-control" required/>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea name="note" rows="6" class="form-control" placeholder="Quotation Notes..." required></textarea>
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Request ID</label>
                        <input type="number" name="request_id" value ={{$rfq->id}} class="form-control">
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Request Item ID</label>
                        <input type="number" name="request_item_id" value={{$rfq_item->id}} class="form-control">
                    </div>
                    <div class="form-group hidden">
                        <label class="control-label">Quotation ID</label>
                        <input type="number" name="quotation_id" value={{$quotation->id}} class="form-control">
                        
                    </div>           
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Submit</button>
                <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
			
	</main>
@endsection
@section('script2')



@endsection
