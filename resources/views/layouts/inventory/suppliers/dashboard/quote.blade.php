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
          'link' => route('get-rfqs-item',['id'=>$request_item->request_id]),
          'name' => 'rfq-'.$current->request_code,
          'icon' => null
        ),
        array(
            'link' => route('rfq-item',['id'=>$request_item->id]),
            'name' => 'rfq-item-'.$request_item->id,
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
   
    <div class="card mt-4 mb-3" style="width: 80%;top:50%;left:10%;box-shadow: 5px 5px 5px 5px grey">
        <?php
            $rfq = getRfqsById($request_item->request_id);
            $store = getStoreById($request_item->store_id);
            $slot = getSlotById($request_item->slot_id);
            $sub = getInventorySubCatByID((int)$request_item->inventory_sub_category_id);                        
            // $quote = checkSupplierQuote($request_item->id);
        ?>
            <h5 class="p-1 card-title bg-light p-4 " style="height:100px;">
            <i class="mdi mdi-star-box"></i>  Request For Quotation | {{$current->request_code}}  Item | {{$request_item->id}}<i class="mdi mdi-star"></i> 
                @if(sizeof( $quote) == 0)
                <button class="btn btn-outline-success btn-sm float-right ml-3" data-toggle="modal" data-target="#add-quotation"><i style="color: green;" class="far fa-check-circle"></i> Send Quotation</button>
                @else
                <a href="{{route('get-quotation',['id'=>$request_item->id])}}" class="btn btn-outline-warning btn-sm float-right ml-3">View Quotation <i style="color: green;" class="far fa-check-circle"></i></a>
                @endif
            </h5>
            <br>
            <div class="card-body container">

                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-banded no-header table-hover table-bordered table-sm">


                        <tr>
                            <th><b>Item No: </b></th>
                            <td>{{$request_item->id}}</td>
                        </tr>
                        <tr>
                            <th><b>Request For Quotation Code: </b></th>
                            <td>{{$rfq->request_code}} </td>
                        </tr>
                        <tr>
                            <th><b>Created at: </b></th>
                            <td>{{$request_item->created_at}}</td>
                        </tr>
                        <tr>
                            <th><b>Store: </b></th>
                            <td>{{$store->name}}</td>
                        </tr>
                        <tr>
                            <th><b>Slot: </b></th>
                            <td>{{$slot->name}}</td>
                        </tr>
                        <tr>
                            <th><b>Inventory Category: </b></th>
                            <td>{{$sub->name}}</td>
                        </tr>
                        <tr>
                            <th><b>Quantity: </b></th>
                            <td>{{$request_item->quantity}}</td>
                        </tr>
                        <tr>
                            <th><b>Net Value: </b></th>
                            <td>{{$request_item->net_value}}</td>
                        </tr>
                        <tr>
                            <th><b>Action: </b></th>
                            <td>{{$request_item->action}}</td>
                        </tr>
                        <tr>
                            <th><b>Status: </b></th>
                            <td>{{$request_item->status}}</td>
                        </tr>
                        <tr>
                            <th><b>Expiry Date: </b></th>
                            <td>{{$request_item->gr_expiry}}</td>
                        </tr>
                    
                    </table>
                   
                </div>

          
            </div>

        </div>		
	</main>
@endsection
@section('script2')
<div class="modal fade" id="add-quotation">
    <div class="modal-dialog">
        <form action="{{route('add-supplier-quote',['id'=>$request_item->id])}}" method="post" class="modal-content">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title"><i style="color: green ;" class="far fa-check-circle"></i>  Send Quotation For Item {{$request_item->id}}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Quote Amount</label>
                    <input type="number" min=0 value="" name="amount" class="form-control" required/>
                </div>
                <div class="form-group hidden">
                    <label class="control-label">Request id</label>
                    <input type="number" name="request_id"  class="form-control"  value={{$request_item->request_id}}>
                </div>
                <div class="form-group hidden">
                    <label class="control-label">Request Item</label>
                    <input type="number" name="request_item_id" class="form-control" value={{$request_item->id}}>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Submit</button>
                <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>


@endsection
