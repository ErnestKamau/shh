@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
<title> Lab-Stock-Movement-{{$category->name}} </title>
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
            'link' => route('stock-movement-index'),
            'name' => 'Stock-Movement',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $category->name,
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-3">
        <i class="mdi mdi-format-list-bulleted"></i> {{$category->name}}
    </h3>
    <div class="row no-gutters">
        <div class="col-xl-4 col-sm-4 p-2">
            <div class="card">

                <div class="card-body p-2">

                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" class="form-control" value="{{ $category->name }}" readonly="true">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea class="form-control" readonly="true">{{ $category->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Category</label>
                        <select class="form-control" disabled readonly="true">
                            <option value="0">Non Specific</option>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat->id}}" {!! $cat->id == $category->category_id ? 'selected': '' !!}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Image</label><br>
                        <img src="{{$category->image}}" style="width:100px;height:100px;" alt="">
                    </div>

                    <div class="form-group">
                        <label class="control-label">Rate</label>
                        <input type="text" class="form-control" value="{{ $category->rate }}" readonly="true" />
                    </div>
                    <div class="form-group">
                        <label class="control-label">Unit of Measure</label>
                        <select class="form-control" disabled readonly="true">
                            <option value="">Select Unit of Measure...</option>
                            @foreach (getReportingUnits() as $g)
                            <option value="{{ $g['id'] }}" {{ $category->reporting_unit == $g['id'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

            </div>
        </div>
        <div class="col-xl-8 col-sm-8 p-2">
            <span class="badge badge-pill p-2" style="background-color:white;"><i class="mdi mdi-package-variant-closed text-success" style="font-size: 16px;"></i> Available {{$category->stock ?? 0.0}} {{$report->name}}</span>
            <span class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-stock-movement" ><i class="mdi mdi-plus"></i> Stock In/Out</span>
            <div class="card p-3 mt-3">
                <div class="table-responsive bg-light p-2">
                    <table class="table table-condensed table-hover table-stripped table-bordered table-sm" style="width: 130%;">
                        <thead class="bg-light">
                            <th>No</th>
                            <th>Name</th>
                            <th>Stock In</th>
                            <th>Stock Out</th>
                            <th>UOM</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th>Description</th>
                        </thead>
                        <tbody>
                            @foreach($stock_movement as $sm)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td><img src="{{$category->image}}" style="width: 30px; height:30px" alt=""> {{$category->name}}</td>
                                <td>{{number_format($sm->stock_in,2)}}</td>
                                <td>{{number_format($sm->stock_out,2)}}</td>
                                <td>{{getReportingUnitsByID($sm->uom_id)->name}}</td>
                                <td>{{getUserById($sm->created_by)->name}}</td>
                                <td>{{date('Y-m-d h:i:sa',strtotime($sm->created_at))}}</td>
                                <td>{{$sm->description}}</td>
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
<div class="modal fade" id="add-stock-movement" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stock-movement-add') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header p-2">

                    <h5 class="modal-title">
                        <i class="mdi mdi-package-variant text-success"></i> Add Stock In / Out
                    </h5>
                </div>
                <div class="modal-body">
                    @if($category->stock > 0)
                        <div class="alert alert-success">
                        <i class="mdi mdi-package-variant-closed pull-left"></i> {{$category->name}} available stock {{$category->stock}} {{getReportingUnitsByID($category->reporting_unit)->name}} .
                        </div>
                    @elseif($category->stock <= 0)
                        <div class="alert alert-danger">
                        <i class="mdi mdi-package-variant  mr-2" style="font-size: 17px;"></i> {{$category->name}} available stock is {{$category->stock ?? 0}} .
                        </div>
                    @endif
                    <div id="message"></div>
                    <div class="form-group">
                        <label class="control-label">Stock Movement Type <span class="text-danger">*</span></label>
                        <select name="stock_type" id="stock-type" class="form-control" aria-placeholder="Select Either Stock In or Stock Out" required>
                            <option value="">Select either Stock In / Stock Out</option>
                            <option value="stock_in">Stock In</option>
                            <option value="stock_out">Stock Out</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Amount <span class="text-danger">*</span></label>
                        <input type="text" data-stock="{{json_encode($category->stock ?? 0)}}" id="stock-amount" name="amount"  class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Unit of Measure <span class="text-danger">*</span></label>
                        <select name="uom_id" class="form-control" required>
                            <option value="">Select UOM</option>
                            @foreach($uoms as $uom)
                                <option value="{{$uom->id}}">{{$uom->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea class="form-control" row="3" name="description" placeholder="Description..." required></textarea>
                    </div>
                    <input type="hidden" name="category_id" value="{{$category->id}}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(function(){
       
    });
   
</script>

@endsection