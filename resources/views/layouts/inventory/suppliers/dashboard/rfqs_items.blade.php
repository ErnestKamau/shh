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
          'link' => null,
          'name' => 'rfq-'.$current->request_code,
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
        <i class="mdi mdi-star-box-multiple-outline" ></i> Request For Quotation | {{$current->request_code}} <i class="mdi mdi-star"></i> 
		</h2>
		
		
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
                    
                    <li class="nav-item">
                        <a class="nav-link" id="Categories-tab" data-toggle="tab" href="#Activity" role="tab" aria-controls="Categories" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-star-box-multiple-outline"></i> Rfqs Items</a>
                    </li>
                    
                </ul>
            </div>
            <div class="tab-content" id="Orders-tabs-content">
                
                <div class="tab-pane fade show active p-3" id="Activity" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title"><i class="mdi mdi-star-box-multiple"></i> Request For Quotation Items </h5>
                    <div class="table-responsive">
                        <table
                            class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th>RFQS</th>
                                    <th>Store</th>
                                    <th>Slot</th>
                                    <th>Inventory Sub Category</th>
                                    <th>Quantity</th>
                                    <th>Net Value</th>
                                    <th>Created_at</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>Gr_Expiry</th>
                                    <th></th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                
                                    @foreach($rfqs_items as $item)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <?php
                                            $rfq = getRfqsById($item->request_id);
                                           
                                            $store = getStoreById($item->store_id);
                                            $slot = getSlotById($item->slot_id);
                                            $sub = getInventorySubCatByID((int)$item->inventory_sub_category_id);                        
                                            ?>
                                            <td>{{$rfq->request_code}}</td>
                                            <td>{{$store->name}}</td>
                                            <td>{{$slot->name}}</td>
                                            <td>{{$sub->name}}</td>
                                            <td>{{number_format($item->quantity)}}</td>
                                            <td>{{number_format( $item->net_value)}}</td>
                                            
                                            <td>{{$item->created_at}}</td>
                                            <td>{{$item->action}}</td>
                                            <td>{{$item->status}}</td>
                                            <td>{{$item->gr_expiry}}</td>
                                            <td><a href="{{route('rfq-item',['id'=>$item->id])}}" class="btn btn-outline-success btn-sm"><i class="mdi mdi-eye"></i></a></td>
                                                                            
                                            </tr>
                                    @endforeach
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
			
	</main>
@endsection
@section('script2')



@endsection
