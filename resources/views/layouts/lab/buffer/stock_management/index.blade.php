@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Lab-Stock-Monitoring </title>
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

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
        <i class="mdi mdi-format-list-bulleted"></i> Stock Management

        <span class="btn btn-sm btn-transparent text-success float-right" data-target="#add-sub-category" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Item</span>
        <span class="btn btn-sm btn-transparent text-warning float-right" id="add-filter"><i class="mdi mdi-filter-menu"></i> Filter</span>
        <div class="filter_form card p-2 mt-3" id="Filter-Form">

            <form action="{{route('filter_data_category')}}" method="post" class="float-right">
                @csrf
                <i class="mdi mdi-filter-variant-plus"></i> <b style="font-size: 10px;">Choose category to filter your data?</b>
                <button type="submit" class="btn btn-sm bg-light float-right"><i class="mdi mdi-filter"></i> Apply</button>
                <hr>
                <div class="row">

                    @foreach($categories as $category)
                    <div class="col-md-4">
                        <input type="checkbox" {{in_array($category->id,$selected) ? 'checked': ''}} id="category_id" name="category[]" value="{{$category->id}}"> <span style="font-size: 13px;">{{$category->name}}</span>
                    </div>
                    @endforeach
                </div>
            </form>
        </div>
    </h3>
    <div class="p-4 bg-light">
        <div class="table-responsive">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                <thead class="bg-light p-2">
                    <tr>
                        <th>No</th>
                        <th nowrap>Image</th>
                        <th nowrap>Name</th>
                        <th nowrap>Category</th>
                        <th nowrap>Rate</th>
                        <th>Unit Measure</th>
                        <th>Status</th>
                        <th nowrap>Description</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if($subcategory->count() > 0)
                    @foreach($subcategory as $element)
                    <tr>
                        <td valign="center">{{ $loop->iteration }}</td>
                        <td><img src="{{ $element->image }}" style="width: 125px; height:65px" /></td>
                        <td nowrap>{{ $element->name }}</td>
                        <td nowrap>{{$element->category_name}}</td>
                        <td>{{ $element->rate }}</td>
                        <td>
                            {{$element->reporting_name}}
                        </td>
                        <td class="text-small">{!! $element->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                        <td>{{ $element->description }}</td>
                        <td nowrap>
                            <button class="btn btn-outline-primary btn-sm" data-target="#edit-analyte-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                            <a href="{{route('show_lab_sub_category',['id'=>$element->id])}}" class="btn btn-outline-success btn-sm"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small></a>
                            <!-- {{-- <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button> --}} -->
                            <button class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-sub-category-{{$element->id}}"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">delete</small> </button>
                            <button class="btn btn-sm btn-outline-warning" data-target="#clone-sub-{{$loop->iteration}}" data-toggle="modal"><i class="mdi mdi-content-duplicate"></i></button>
                            <div class="modal fade" role="dialog" id="clone-sub-{{$loop->iteration}}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('clone_sub_category') }}" method="post">
                                            @csrf 
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-content-duplicate text-warning"></i> Clone Item {{$element->name}}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="sub_category_id" value="{{$element->id}}">
                                                <div class="card bg-warning text-center pt-2" style="height: 55px;">
                                                    Confirm you want to clone sub category {{$element->name}}?
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-outline-warning btn-sm"><i class="mdi mdi-content-duplicate"></i> Confirm</button>
                                                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="delete-sub-category-{{$element->id}}" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="{{ route('delete_sub_category') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-delete-empty text-danger"></i> Delete Category Item {{$element ->name}}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="sub_category_id" value="{{$element->id}}">
                                                <div class="card p-2 text-center" style="background-color:red; height:55px">
                                                    Kindly confirm you want to delete Lab Category Item {{$element->name}} ?
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-outline-danger"><i class="mdi mdi-delete-empty"></i> Confirm</button>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div id="edit-analyte-{{ $loop->iteration }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <form class="modal-content" method="POST" action="{{ route('edit_lab_sub_category') }}" enctype="multipart/form-data">
                                        @csrf

                                        <div class="modal-header">
                                            <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Sub Category</h4>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="sub_category_id" value="{{$element->id}}">
                                            <div class="form-group">
                                                <label class="control-label">Name</label>
                                                <input type="text" class="form-control" name="name" value="{{ $element->name }}" placeholder="Name..." required />
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Description</label>
                                                <textarea class="form-control" name="description" placeholder="Description..." required>{{ $element->description }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Category</label>
                                                <select class="form-control" name="category_id" placeholder="Select Category...">
                                                    <option value="0">Non Specific</option>
                                                    @foreach ($categories as $category)
                                                    <option value="{{ $category->id}}" {!! $category->id == $element->category_id ? 'selected': '' !!}>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Image</label>
                                                <input type="file" class="form-control" name="image" />
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label">Unit of Measure</label>
                                                <select class="form-control" name="reporting_unit">
                                                    <option value="">Select Unit of Measure...</option>
                                                    @foreach (getReportingUnits() as $g)
                                                    <option value="{{ $g['id'] }}" {{ $element->reporting_unit == $g['id'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Rate</label>
                                                <input type="text" class="form-control" name="rate" value="{{ $element->rate }}" placeholder="Rate of Production..." required />
                                            </div>


                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
            @if($subcategory->count() == 0)
            <div class="alert alert-info">
                <i class="mdi mdi-alert"></i> No Category items added yet.
            </div>
            @endif
        </div>
    </div>

</main>
<div id="add-sub-category" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form class="modal-content" method="POST" action="{{ route('add_lab_sub_inventory_categories') }}" enctype="multipart/form-data">
            @csrf

            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Sub Category</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
                </div>
                <div class="form-group">
                    <label class="control-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="control-label">Category <span class="text-danger">*</span></label>
                    <select class="form-control" name="category_id" placeholder="Select Category..." required>
                        <option value="0">Non Specific</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id}}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label">Image</label>
                    <input type="file" class="form-control" name="image" />
                </div>


                <div class="form-group">
                    <label class="control-label">Unit of Measure <span class="text-danger">*</span></label>
                    <select class="form-control" name="reporting_unit" required>
                        <option value="">Select Unit of Measure...</option>
                        @foreach (getReportingUnits() as $g)
                        <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label">Rate <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="rate" value="" placeholder="Rate of Production..." required />
                </div>


            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('script2')

<script>
    $(function() {
        $('#add-filter').click(function(event) {
            $('#Filter-Form').toggle();
        });
    });
    $(document).ready(function() {
          $('input[type="checkbox"]').click(function() {
                $(':checkbox:checked').each(function(i){
                    console.log(this.val());
                })
            //   if($(this).prop("checked") == true) {
            //       $('#category_id').val();
               
            //     console.log($('#category_id').val());
            //   }
            //   else if($(this).prop("checked") == false) {
            //     // console.log(this.value);
            //   }
            });
        });
</script>

@endsection