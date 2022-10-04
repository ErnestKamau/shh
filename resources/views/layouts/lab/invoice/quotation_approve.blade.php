@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Lab-Invoice </title>
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
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null
        ),
        array(
            'link' => route('quotation-index'),
            'name' => 'Billing-Quotation',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $header->quote_number,
            'icon' => null
        ),


    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi-file-table"></i>Billing | Quotations

    </h2>
    <div class="card">

        <h3 class=" text-center card-header">
            <span data-target="#save-draft" data-toggle="modal" class="btn btn-outline-warning btn-sm float-left"><i class="mdi mdi-download-outline"></i> Save As Draft</span>
            <span data-target="#delete-quotation" data-toggle="modal" class="btn btn-outline-danger ml-2 btn-sm float-left"><i class="mdi mdi-delete-empty"></i> Delete Quotation</span>

            @if($header->payments != '')
            <a href="{{ route('view_quotation_final',['id'=>$header->id])}}" class="btn btn-success btn-sm float-right"><i class="mdi mdi-share-circle"></i> Next</a>
            @else
            <a href="{{ route('view_quotation_final',['id'=>$header->id])}}" style="pointer-events:none; cursor:default;" class="btn btn-outline-success btn-sm float-right"><i class="mdi mdi-share-circle"></i> Next</a>
            @endif


            <i class="mdi mdi-check-decagram mb-1" style="position: absolute;left:47.4%"></i><br> Quotation | {{$header->quote_number}}

        </h3>

        <div class="card-body">

            <div class="row no-gutter">

                <div class="col-xl-4 col-sm-4">
                    <form action="{{route('add-quotation-header')}}" method="POST" class="bg-light" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body p-2">
                            <div class="form-group">
                                <label class="control-label">Quotation Number</label>
                                <input type="text" name="quote_code" readonly value="{{$header->quote_number}}" id="" class="form-control">
                                <input type="hidden" name="quote_id" value="{{$header->id}}">
                            </div>

                            <div class="form-group">
                                <label class="control-label">Client *</label>
                                <select name="client" class="form-control" id="select-client" data-contact= "{{json_encode($header->crm_customer_contact_id)}}" aria-placeholder="Choose Client..." required>
                                    <option value="" disabled selected>Choose Client...</option>
                                    @foreach($customers as $customer)
                                    <option value="{{$customer->name}}" {{$customer->id == $header->crm_customer_id ? 'selected':''}}>{{$customer->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group contacts" >
                                <label class="control-label">Client Contact *</label>
                                <select name="client_contact" id="select-client-contact" class="form-control" aria-placeholder="Select Client Contact..." required>
                                    <?php
                                    $client_contacts = getCrmCustomerContacts($header->crm_customer_id);
                                    ?>
                                    @if(sizeof($client_contacts)>0)
                                    @foreach($client_contacts as $contact)
                                    <option value="{{$contact->id}}" {{$contact->id == $header->crm_customer_contact_id ? 'selected':''}}>{{$contact->first_name}} {{$contact->middle_name}} {{$contact->last_name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                

                            </div>
                            
                            <div class="form-group">
                                <label class="control-label">Quotation Type</label>
                                <select name="quotation_type" required id="" class="form-control">
                                    <option value="">Choose Quotation Type</option>
                                    <option value="General" {{$header->quotation_type == 'General' ? 'selected' : '' }}>General Quotation</option>
                                    <option value="Analysis" {{$header->quotation_type == 'Analysis' ? 'selected' : '' }}>Analysis Quotation</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Quotation Date *</label>
                                <input type="date" name="quotation_date" class="form-control" placeholder="Quotation Date..." value="{{$header->quote_date}}" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Expiry Date *</label>
                                <input type="date" name="expire_date" class="form-control" placeholder="Expiration Date..." value="{{$header->expiring_date}}" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Pricelist Code</label>
                                <input type="text" name="pricelist_code" readonly value="{{$pricelist->code ?? '-'}}" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Prepared By</label>
                                <input type="text" name="prepared_by" readonly value="{{$header->prepared_by_name}}" id="" class="form-control">
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            <button style="width:70%;" class="btn btn-outline-success" type="submit">Save</button>
                        </div>
                    </form>


                </div>




                <div class="col-xl-8 col-sm-8">
                    <form action="{{ route('add_quotation_detail',['id'=>$header->id]) }}" method="POST" class="bg-light p-1">
                        @csrf
                        @if($header->quotation_type == 'General')
                        <button type="submit" class="btn btn-outline-success btn-sm float-left mr-2 mb-2">Save <i class="mdi mdi-share-circle"></i></button>
                        <!-- <span class="btn btn-outline-info float-right btn-sm mb-2" data-toggle="modal" onclick=" addrowgeneral()"><i class="mdi mdi-plus"></i></span> -->
                        <div class="table-responsive mb-3">

                            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="min-width: 180%;">
                                <thead class="bg-light">
                                    <th></th>
                                    <th style="min-width: 10%;" >Part No</th>
                                    <th  nowrap style="min-width: 25%;">Item*</th>
                                    <th nowrapstyle="min-width: 30%;">Description*</th>
                                    <th nowrap style="min-width: 8%;">item Image</th>
                                    <th nowrap  style="min-width: 8%;">Quantity*</th>
                                    <th nowrap  style="min-width: 8%;">Unit Price</th>
                                    <th nowrap  style="min-width: 8%;">Tax</th>
    
                                </thead>
                                <tbody>
                                    @foreach($details as $detail)
                                    <tr>
                                        <td style="display: flex;border:0px ">
                                            <span style="font-size:11px; flex:1" data-toggle="modal" data-target="#edit-detail-quotation-{{$loop->iteration}}" class="btn mdi mdi-pencil " data-toggle="tooltip" title="Edit"></span>
                                            <span style="font-size:12px;flex:1 ;border-bottom:0px" data-toggle="modal" data-target="#delete-detail-{{$detail->id}}" class="btn mdi mdi-delete-empty text-danger" data-toggle="tooltip" title="Delete"></span>
    
    
                                        </td>
                                        <td>{{$detail->part_no}}</td>
                                        
                                        <td>
                                            <div class="form-group">
                                                <input type="text" value="{{$detail->item_name}}" class="form-control" disabled>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                            <textarea class="form-control" rows="2" readonly  style="min-height: 100px;">{{$detail->description}}</textarea>
                                            </div>
                                        </td>
                                        <td>
    
                                            <div class="form-group">
                                            <img src="{{$detail->photo_url}}" alt="Item Image" style="height: 100px;;width:auto">
    
                                            </div>
    
                                        </td>
                                        <td>
                                            <div class="form-group" id="">
                                                <input type="text" class="form-control" value="{{$detail->quantity}}" readonly id="">
    
                                            </div>
                                        </td>
    
                                        <td>
    
                                            <div class="form-group">
                                                <input type="float" class="form-control" value="{{$detail->unit_price}}" disabled>
                                            </div>
    
                                        </td>
                                        <td>
    
                                            <div class="form-group" id="">
                                                <input type="text" value="{{$detail->tax}}" readonly class="form-control">
                                            </div>
    
    
    
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @else

                        <button type="submit" class="btn btn-outline-success btn-sm float-left mr-2 mb-2">Save <i class="mdi mdi-share-circle"></i></button>
                        <!-- <span class="btn btn-outline-info float-right btn-sm mb-2" data-toggle="modal" onclick=" addrow()"><i class="mdi mdi-plus"></i></span> -->
                        <div class="table-responsive mb-3">

                            <table class="table table-condensed my-small-text table-striped table-hover table-bordered">
                                <thead class="bg-light">
                                    <th></th>
                                    <th >Part No</th>
                                    <th nowrap>Analysis*</th>
                                    <th nowrap>Sample Type</th>
                                    <th nowrap>Quantity*</th>
                                    <th nowrap>Unit Price</th>
                                    <th nowrap>Tax</th>
    
                                </thead>
                                <tbody>
                                    @foreach($details as $detail)
                                    <tr>
                                        <td style="display: flex;border:0px ">
                                            <span style="font-size:11px; flex:1" data-toggle="modal" data-target="#edit-detail-quotation-{{$loop->iteration}}" class="btn mdi mdi-pencil " data-toggle="tooltip" title="Edit"></span>
                                            <span style="font-size:12px;flex:1 ;border-bottom:0px" data-toggle="modal" data-target="#delete-detail-{{$detail->id}}" class="btn mdi mdi-delete-empty text-danger" data-toggle="tooltip" title="Delete"></span>
    
    
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" value="{{$detail->part_no}}" class="form-control" disabled>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <script>
                                                    var count = <?php echo $loop->iteration ?>
                                                </script>
                                                <select id="select-analyte" class="form-control" disabled>
                                                    <option value="" disabled>Choose Analyte</option>
                                                    @foreach($pricelist_items as $item)
                                                    <option value="{{$item}}" {{$item->analysis_id == $detail->analyte_id ? 'selected':''}}>{{$item->analyte_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
    
                                            <div class="form-group" id="">
                                                <input type="text" value="{{$detail->sample_type}}" readonly id="">
    
                                            </div>
    
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="number" id="" class="form-control" value="{{$detail->quantity}}" disabled>
                                            </div>
                                        </td>
                                        <td>
    
                                            <div class="form-group">
                                                <input type="float" class="form-control" value="{{$detail->unit_price}}" disabled>
                                            </div>
    
                                        </td>
                                        <td>
    
                                            <div class="form-group" id="">
                                                <input type="text" value="{{$detail->tax}}" readonly class="form-control" disabled>
                                            </div>
                                            <div class="form-group hidden">
                                                <input type="number" value="{{$detail->analyte_id}}" required>
                                            </div>
    
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                        
                    </form>
                </div>

            </div>
        </div>
    </div>



</main>
<div class="modal fade" id="delete-quotation" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('delete_quotation',['id'=>$header->id])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">

                        <i class="mdi mdi-delete-empty text-danger"></i> Delete Item {{$header->quote_number}}
                    </h4>
                </div>
                <div class="modal-body text-center">

                    <input type="hidden" name="header_id" value="{{$header->id}}" class="form-control">
                    Are you sure you want to delete Quotation {{$header->quote_number}}?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="save-draft" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('save_draft',['id'=>$header->id])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-download-outline text-warning"></i> Save As Draft Quotation {{$header->quote_number}}</h4>
                </div>
                <div class="modal-body">
                    <div class="card p-3" style="background-color: turquoise;">
                        Ensure you have saved all the details first before saving as draft.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i>Proceed Save</button>
                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
@foreach($details as $detail)
<div class="modal fade" id="delete-detail-{{$detail->id}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('delete_quotation_detail',['id'=>$detail->id])}}" method="get">
                <div class="modal-header">
                    <h4 class="modal-title">

                        <i class="mdi mdi-delete-empty text-danger"></i> Delete Item {{$loop->iteration}}
                    </h4>
                </div>
                <div class="modal-body text-center">
                    @if($header->quotation_type == 'General')
                    <div class="alert alert-danger">
                        Are you sure you want to delete item {{$detail->item_name}}
                    </div>
                    @else
                    <?php $analysiss = getAnalysisTypeID($detail->analyte_id) ?>

                    Are you sure you want to delete {{$analysiss->name}} analysis ?
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-detail-quotation-{{$loop->iteration}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('edit_quotation_detail',['id'=>$detail->id])}}" method="post" enctype="multipart/form-data">

                @csrf
                <div class="modal-header">


                    <h4 class="modal-title"><i class="mdi mdi-pencil text-info"></i> Edit Quotation Detail {{$loop->iteration}}</h4>
                </div>
                <div class="modal-body">
                    @if($header->quotation_type == 'General')
                    <div class="form-group">
                        <label class="control-label">item Name</label>
                        <input type="text" name="item_name" value="{{$detail->item_name}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Decription</label>
                        <textarea class="form-control" name="description" value=" {{$detail->description}}" rows="1"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Photo</label>
                        <input type="file" name="photo" id="" class="form-control">
                    </div>
                    @else
                    <div class="form-group">
                        <label class="control-label">Analysis</label>

                        <script>
                            var looped = <?php echo  $pricelist_items ?>;
                        </script>

                        <select name="analyte_edit[]" id="{{$detail->count}}" class="form-control" onclick="getPricelistedit(this.id)" required>

                            <option value="" disabled>Choose Analysis</option>
                            @foreach($pricelist_items as $item)
                            <option value="{{$item}}" {{$detail->analyte_id == $item->analysis_id ? 'selected':''}}>{{$item->analyte_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="">
                        <label class="control-label">Sample Type</label>
                        <input type="text" class="form-control" name="sample_type_edit[]" value="{{$detail->sample_type}}" readonly id="">

                    </div>
                    <div class="form-group hidden">
                        <input type="number" name="analysis_id_edit[]" value="{{$detail->analyte_id}}" required>
                    </div>
                    @endif


                    <div class="form-group">
                        <label class="control-label">Part No</label>
                        <input type="text" name="part_no_edit[]" value="{{$detail->part_no}}" id="" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="control-label">Quantity</label>
                        <input type="number" name="quantity_edit[]" id="" class="form-control" value="{{$detail->quantity}}" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Unit Price</label>
                        <input type="float" name="unit_price_edit[]" class="form-control" value="{{$detail->unit_price}}" required>
                    </div>
                    <div class="form-group" id="">
                        <label class="control-label">Tax</label>
                        <input type="text" name="tax_edit[]" value="{{$detail->tax}}" readonly class="form-control" required>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>


    </div>
</div>
@endforeach
@endsection
@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>

    $(function(){
        $('#select-client').on('change',function(){
            var client = $(this).val();
            var contact = $(this).data('contact');
            console.log();
            $.ajax({
                url:'/fetch-customer-contacts/'+client,
                beforeSend: function(){
                    $('#select-client-contact').empty();
                },
                success: function(data){
                    // console.log(data);
                    $.each(data,function(j,s){
                        console.log(s);
                        var $option = $(`
                            <option value = "${s.id}" ${s.id === contact ? 'selected' :''}>${s.first_name} ${s.middle_name ?? ''} ${s.last_name ?? ''}</option>
                        `);
                        $('#select-client-contact').append($option);
                    })
                },
                error:function(data){
                    console.log(data);
                }
            })
        })
    });
    
    function getCustomer() {
        var customer = document.getElementsByName('client')[0].value;

        var text = document.getElementById(customer);
        var text2 = document.getElementsByClassName('contacts')
        for (i = 0; i < text2.length; i++) {
            text2[i].style.display = 'none';
        }

        text.style.display = "block";
    }

    function getPricelist(index) {

        var count = index - 1;
        var analyte = document.getElementsByName('analyte[]')[count].value;
        var my = JSON.parse(analyte);
        var sample_type = document.getElementsByName('sample_type[]')[count].value = my.sample_type_name;
        var tax = document.getElementsByName('tax[]')[count].value = my.tax;
        var unit_price = document.getElementsByName('unit_price[]')[count].value = my.selling_price;
        var analyte_id = document.getElementsByName('analysis_id[]')[count].value = my.analysis_id;




    }

    function getCustomer() {
        var customer = document.getElementsByName('client')[0].value;

        var text = document.getElementById(customer);
        var text2 = document.getElementsByClassName('contacts')
        for (i = 0; i < text2.length; i++) {

            $(text2[i]).addClass('hidden');
        }


        $(text).removeClass('hidden');
    }

    function deleterow(index) {
        var detail = '.detail-' + index;
        var row = $(detail).parent('tr');

        if (confirm("Are you sure you want to delete this row?")) {
            $(row).remove();
        }
    }

    function getPricelistedit(indexed) {
        var counted = indexed - 1;
        var analyte = document.getElementsByName('analyte_edit[]')[counted].value;
        console.log(analyte);
        var my = JSON.parse(analyte);
        var sample_type = document.getElementsByName('sample_type_edit[]')[counted].value = my.sample_type_name;
        var tax = document.getElementsByName('tax_edit[]')[counted].value = my.tax;
        var unit_price = document.getElementsByName('unit_price_edit[]')[counted].value = my.selling_price;
        var analyte_id = document.getElementsByName('analysis_id_edit[]')[counted].value = my.analysis_id;

    }

    function addrow() {

        var len = $('#part_number').length;

        var current = len + 1;
        // console.log(len2);
        var $row = $(`
        <tr>
                               <td class="text-center detail-${current}">${current}
                               <i class="mdi mdi-minus-circle-outline text-danger btn " onclick="deleterow(${current})"></i>
                               </td> 
                               <td>
                                   <div class="form-group">
                                       <input type="text" id="part_number" name="part_no[]" value="" class="form-control">
                                   </div>
                               </td>
                               <td >
                                   <div class="form-group">
                                       <select name="analyte[]" id="select-analyte" class="form-control" onclick="getPricelist(${current})" required>
                                           <option value="" disabled>Choose Analyte</option>
                                           @foreach($pricelist_items as $item)
                                           <option value="{{$item}}">{{$item->analyte_name}}</option>
                                           @endforeach
                                       </select>
                                   </div>
                               </td>
                               <td>
                                   
                                   <div class="form-group id="">
                                       <input type="text" name="sample_type[]" value=""  readonly id="">
                                      
                                   </div>
                                   
                               </td>
                               <td>
                                   <div class="form-group">
                                       <input type="number" name="quantity[]" id="" class="form-control" value="" required>
                                   </div>
                               </td>
                               <td>
                                   
                                   <div class="form-group">
                                    <input type="float" name="unit_price[]" class="form-control" value="" required>
                                </div>
                                   
                               </td>
                               <td >
                                  
                                  <div class="form-group">
                                  <input type="text" name="tax[]" value="" readonly class="form-control" required>
                                  <div class="form-group hidden">
                                  <input type="number" name="analysis_id[]" value="" required>
                                  </div>
                                  
                                </div>
                                  
                               </td>
                            </tr>
        `).clone();
        $('tbody').append($row);
    }

    function addrowgeneral() {

        var len = $('#part_number').length;

        var current = len + 1;
        // console.log(len2);
        var $row = $(`
        <tr>
                               <td class="text-center detail-${current}">${current}
                               <i class="mdi mdi-minus-circle-outline text-danger btn " onclick="deleterow(${current})"></i>
                               </td> 
                               <td>
                                   <div class="form-group">
                                       <input type="text" id="part_number" name="part_no[]" value="" class="form-control">
                                   </div>
                               </td>
                               <td >

                               <div class="form-group">
                                       <input type="text" name="item[]" value=""  id="" required>
                                      
                                   </div>
                                  
                               </td>
                               <td >
                                    <div class="form-group" id="quotation-description">
                                    <textarea class="form-control" name="description[]" placeholder="Comments..." required></textarea>
                                       
                                    </div>
                                </td>
                                <td >

                                    <div class="form-group">
                                        <input type="file" name="photo[]" value=""  id="">
                                    
                                    </div>
                                    
                                </td>
                               
                               <td>
                                   <div class="form-group">
                                       <input type="number" name="quantity[]" id="" class="form-control" value="" required>
                                   </div>
                               </td>
                               <td>
                                   
                                   <div class="form-group">
                                    <input type="float" name="unit_price[]" class="form-control" value="" required>
                                </div>
                                   
                               </td>
                               <td >
                                  
                                  <div class="form-group">
                                  <input type="text" name="tax[]" value="" class="form-control" required>
                                 
                                  
                                </div>
                                  
                               </td>
                            </tr>
        `).clone();
        $('tbody').append($row);
    }
</script>

@endsection