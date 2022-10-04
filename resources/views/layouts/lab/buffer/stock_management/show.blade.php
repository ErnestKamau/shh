@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
<title> Lab-Stock-Management-{{$subcategory->name}} </title>
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
            'link' => null,
            'name' => 'Stock Monitoring',
            'icon' => null
        ),
        array(
            'link' => route('stock_management_index'),
            'name' => 'Stock-Management',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $subcategory->name,
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-3">
        <i class="mdi mdi-format-list-bulleted"></i> {{$subcategory->name}}
    </h3>
    <div class="row no-gutters">
        <div class="col-xl-4 col-sm-4">
            <form action="{{ route('edit_lab_sub_category') }}" method="post" class="bg-light card">
                @csrf
                <div class="card-body p-2">
                    <input type="hidden" name="sub_category_id" value="{{$subcategory->id}}">
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $subcategory->name }}" placeholder="Name..." required />
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea class="form-control" name="description" placeholder="Description..." required>{{ $subcategory->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Category</label>
                        <select class="form-control" name="category_id" placeholder="Select Category...">
                            <option value="0">Non Specific</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id}}" {!! $category->id == $subcategory->category_id ? 'selected': '' !!}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Image</label>
                        <input type="file" class="form-control" name="image" />
                    </div>

                    <div class="form-group">
                        <label class="control-label">Rate</label>
                        <input type="text" class="form-control" name="rate" value="{{ $subcategory->rate }}" placeholder="Rate of Production..." required />
                    </div>
                    <div class="form-group">
                        <label class="control-label">Unit of Measure</label>
                        <select class="form-control" name="reporting_unit">
                            <option value="">Select Unit of Measure...</option>
                            @foreach (getReportingUnits() as $g)
                            <option value="{{ $g['id'] }}" {{ $subcategory->reporting_unit == $g['id'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                </div>
            </form>
        </div>
        <div class="col-xl-8 col-sm-8">
            <form action="{{route('add_lab_category_item')}}" method="post" class="bg-light p-2">
                @csrf
                <input type="hidden" name="subcategory_id" value="{{$subcategory->id}}">
                <button type="submit" class="btn btn-outline-success btn-sm float-left mr-2">Save <i class="mdi mdi-share-circle"></i></button>
                <span class="btn btn-outline-info float-right btn-sm mb-2" data-toggle="modal" onclick=" addrow()"><i class="mdi mdi-plus"></i></span>
                <div class="table-responsive">

                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered">
                        <thead class="bg-light">
                            <th>No</th>
                            <th>Reagent</th>
                            <th>Reporting Unit</th>
                            <th>Amount Used</th>
                        </thead>
                        <tbody>

                            @foreach($category_items as $item)
                            <tr>
                                <td style="display: flex;border:0px ">
                                    <span style="font-size:11px; flex:1" data-toggle="modal" data-target="#edit-item-{{$loop->iteration}}" class="btn mdi mdi-pencil "></span>

                                    <span style="font-size:12px;flex:1 ;border-bottom:0px" data-toggle="modal" data-target="#delete-item-{{$loop->iteration}}" class="btn mdi mdi-delete-empty text-danger"></span>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select name="reagent" id="Reagents" disabled="disabled" class="form-control">
                                            @foreach($reagents as $reagent)
                                            <option value="{{$reagent->id}}" {{ $reagent->id == $item->reagent_id ? 'selected' : '' }}>{{$reagent->batch_code}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select name="reporting_unit" id="" disabled="disabled" class="form-control">
                                            @foreach(getReportingUnits() as $g)
                                            <option value="{{ $g['name'] }}" {{ $item->unit_measure_id == $g['id'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" name="amount_used" id="" class="form-control" value="{{$item->amount_used}}" disabled>
                                    </div>
                                </td>
                            </tr>
                            <div class="modal fade" id="delete-item-{{$loop->iteration}}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('delete_show_lab_category_item') }}" method="post">
                                            @csrf

                                            <div class="modal-header">
                                                <h4 class="moadal-title"><i class="mdi mdi-delete-empty text-danger"></i> Delete Item {{$item->reagent_name}}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="item_id" value="{{$item->id}}">
                                                <div class="card text-center pt-2" style="background-color:red; height:55px">
                                                    Confirm you want to delete item {{$item->reagent_name}}?
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-content-save"></i> Confirm</button>
                                                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" role="dialog" id="edit-item-{{$loop->iteration}}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('edit_lab_category_item') }}" method="post" enctype="multipart/form-data">
                                        @csrf 
                                            <div class="modal-header">
                                                <h4 class="modal-title">Edit Category Item {{$item->reagent_name}}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="item_id" value="{{$item->id}}">

                                                <div class="form-group">
                                                    <label class="control-label">Reagent</label>
                                                    <select name="reagent_id" id="" class="form-control">
                                                        @foreach($reagents as $reagent)
                                                        <option value="{{$reagent->id}}" {!! $reagent->id == $item->reagent_id ? 'selected':'' !!}>{{$reagent->batch_code}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Unit of Measure</label>
                                                    <select name="reporting_unit" id="" class="form-control">
                                                        @foreach(getReportingUnits() as $g)
                                                        <option value="{{ $g['id'] }}" {{ $item->unit_measure_id == $g['id'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Amount Used</label>
                                                    <input type="text" name="amount_used" value="{{$item->amount_used}}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </tbody>
                    </table>
                </div>

            </form>
        </div>
    </div>
</main>
<script>
    function addrow() {
        var len = document.getElementsByName('ammount_used[]');
        var t = len.length;
        var current = t + 1;
        var $row = $(`
            <tr>
                <td class="text-center item-${current}">${current}
                    <i class="mdi mdi-minus-circle-outline text-danger btn" onclick="deleterow(${current})"></i>
                </td>
                <td>   
                    <div class="form-group">
                        <select name="reagent_id[]" id="Reagents" class="form-control" required>
                            @foreach($reagents as $reagent)
                            <option value="{{$reagent->id}}">{{$reagent->batch_code}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                </td>
                <td>
                    <div class="form-group">
                        <select name="reporting_unit[]" id="" required class="form-control">
                            @foreach(getReportingUnits() as $g)
                            <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                </td>
                <td>
                    <div class="form-group">
                    <input type="text" name="amount_used[]" class="form-control" />
                    </div>
                </td>
            </tr>
        `).clone();
        $('tbody').append($row);
    }

    function deleterow(index) {
        console.log('test');
        var item = '.item-' + index;
        var row = $(item).parent('tr');
        if (confirm("Are you sure you want to delete this row?")) {
            $(row).remove();
        }
    }
</script>
@endsection