@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Lab-Quotations </title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .my-small-text {
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
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
    }
</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/lab-dashboard',
            'name' => 'Dashboard',
            'icon' => null
        ),


        array(
            'link' => '/billing-quotation',
            'name' => 'Quotations',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $stage,
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h4 class="p-4">
        <i class="mdi mdi-file-cad"></i>Billing | Quotations - {{$stage}}
        @if($stage == 'Quote In Preparation')
        <span class="btn btn-outline-info btn-sm float-right" data-toggle="modal" data-target="#add-quotation"><i class="mdi mdi-plus"></i> Add</span>
        @endif
        <div class="dropleft float-right">

            <span style="font-size:15px;border-radius: 3em;border-color: white;background-color:white;position: 0 0;" class="float-right mr-5 p-2 btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Drafts
                <small class="float-right badge badge-pill badge-primary mt-1 ml-1">{{$drafts->count()}}</small></span>

            <div class="dropdown-menu p-2" style="max-height: 70vh; width:300%; overflow:auto" aria-labelledby="dropdownMenuButton">
                @foreach($drafts as $draft)
                <a class="dropdown-item card mb-2" style="box-shadow: 2px 2px 2px 2px;width:100%; height:40%; font-size:15px" href="{{route('add-qoute-details-view',['id'=>$draft->id,'stage'=>$draft->status])}}">
                    <div class="card-bodys">
                        Quotation {{$draft->quote_number}}

                        <i class="float-right mb-0 mt-3" style="font-size: 12px;">({{$draft->created_at}})</i>
                    </div>
                </a>

                @endforeach

            </div>
        </div>




    </h4>
    @if($stage == 'All Quotations' )
    <div class="filter">
        <div class="p-3"> <u><b>Apply Filter ?</b></u</div>
        <div class="card mb-5">
            <form action="{{route('filterQuotations')}}" method="POST">
                <div class="card-body">
                    @csrf
                    <div class="row">
    
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Quote Type</label>
                                <select name="quote_type" id="quote_type" class="form-control">
                                    <option value="">Choose Quotation Type</option>
                                    <option value="General">General Quotation</option>
                                    <option value="Analysis">Analysis Quotation</option>
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 sample_type_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Sample Type</label>
                                <select name="sample_type_id" id="sample_type_id" class="form-control">
                                    @foreach($sample_types ?? [] as $st)
                                    <option value="{{$st->id}}">{{$st->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 analysis_type_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Analysis Type</label>
                                <select name="analysis_type_id" id="analysis_type_id" class="form-control"></select>
                            </div>
                        </div>
                      
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">End Date</label>
                                <input type="date" name="end_date" id="" class="end_date form-control">
                            </div>
                        </div>
                        <div class="col-md-12 bg-light p-2 item_description_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Item Description</label>
                                <textarea class=" form-control" value="" name="item_description" rows="1" placeholder=""> </textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-filter-variant-plus"></i> Apply Filter</button>
                </div>
            </form>

        </div>
    </div>
    @endif

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">

                <li class="nav-item">
                    <a class="nav-link" id="invoice-tab" data-toggle="tab" href="#Invoice" role="tab" aria-controls="Invoice" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-file-cad"></i> Quotations</a>
                </li>

            </ul>
        </div>
        <div class="tab-content" id="Invoice-tabs-content">

            <div class="tab-pane fade show active p-3" id="Invoice" role="tabpanel" aria-labelledby="one-tab">

                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="width: 130% !important;">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Quote No</th>
                                <th>Quote Type</th>
                                @if($stage == 'All Quotations')
                                <th>Status</th>
                                @endif
                                <th>Quote Date</th>
                                <th>Expiry Date</th>
                                <th>Customer</th>
                                <th>Customer Contact</th>
                                <th>Prepared By</th>
                                <th>Email Customer</th>

                                <th>Pricelist</th>
                                <th>Total</th>
                                <th></th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotations as $quotation)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>
                                    <a href="{{ route('add-qoute-details-view',['id'=>$quotation->id]) }}">{{$quotation->quote_number}}</a>
                                </td>
                                <td>{{$quotation->quotation_type}}</td>
                                @if($stage == 'All Quotations')
                                <td>{{$quotation->status}}</td>
                                @endif
                                <td>{{$quotation->quote_date}}</td>
                                <td>{{$quotation->expiring_date}}</td>
                                <td>{{$quotation->customer}}</td>
                                <td>{{$quotation->contact}}</td>
                                <td>{{$quotation->prepared_by_name}}</td>
                                <td>{{$quotation->email_to_customer == '' ? '-':$quotation->email_to_customer}}</td>
                                <td>{{$quotation->pricelist}}</td>
                                <td>
                                    <p class="float-right mt-2"> {{number_format($quotation->total_amount,2) }}</p>
                                </td>

                                <!-- <a class="btn btn-outline-primary btn-sm" href=""><i class="mdi mdi-pencil"></i></a>
                                    <a class="btn btn-outline-warning btn-sm" href=""><i class="mdi mdi-content-duplicate"></i></a> -->

                                <td>
                                    <a href="{{ route('add-qoute-details-view',['id'=> $quotation->id,'stage'=>'Quote In Reception']) }}" class="btn btn-sm btn-outline-primary " data-toggle="tooltip" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                    <a href="{{ route('add-qoute-details-view',['id'=>$quotation->id]) }}" class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="View"><i class="mdi mdi-eye"></i></a>
                                    <a href="{{ route('clone_quotation',['id'=>$quotation->id]) }}" class="btn btn-sm btn-outline-warning"><i class="mdi mdi-content-duplicate" data-toggle="tooltip" title="Clone"></i></a>
                                </td>

                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>
@include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers])
@endsection
@section('script2')
<script>
    $(function() {
        $('#quote_type').on('change',(e)=>{
            $quote_type = $('#quote_type').val();
            if($quote_type == 'General'){
                $('.sample_type_field').addClass('hidden');
                $('.analysis_type_field').addClass('hidden');
                $('.item_description_field').removeClass('hidden');
            }else if($quote_type == 'Analysis'){
                
                $('.sample_type_field').removeClass('hidden');
                $('.analysis_type_field').removeClass('hidden');
                $('.item_description_field').addClass('hidden');
            }
            
            
        });
        $('#sample_type_id').on('change',(e)=>{
            $value  = $('#sample_type_id').val()
        })
    });
</script>
@include('layouts.lab.invoice.partials.add-quotation-modal-scripts')


@endsection