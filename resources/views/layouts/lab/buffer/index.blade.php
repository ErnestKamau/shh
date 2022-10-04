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
            'link' => null,
            'name' => 'Categories',
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
        <i class="mdi mdi-format-list-bulleted-type"></i>Categories
        <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-category"><i class="mdi mdi-plus"></i> Add</button>
    </h3>
    <div class="bg-light p-4">
        <div class="table-responsive">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                <thead class="bg-light p-2">
                    <tr>
                        <th>No</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Available</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($categories) > 0)
                    @foreach($categories as $category)
                    <tr>
                        <td valign="center">{{ $loop->iteration }}</td>
                        <td><img src="{{ $category->image }}" style="width: 125px;height:125px" /></td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description }}</td>
                        <td>
                            @if(intval($category->minimum_level) > intval($category->available()['available']))
                            <span class="pl-2 pr-2 pt-1" title="Requires Restocking">
                                <i class="mdi mdi-alert text-danger"></i>
                            </span>
                            @endif
                            {{ number_format($category->available()['available'])." ".$category->unit_type }} <small class="text-muted">(+{{ number_format($category->available()['pending'])." ".$category->unit_type }} pending)</small>
                        </td>
                        <td nowrap>
                            <button class="btn btn-outline-primary btn-sm" data-target="#edit-inventory-category-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                            {{-- <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button> --}}
                            <button class="btn btn-outline-danger btn-sm" data-target="#delete-category-{{$category->id}}" data-toggle="modal"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">delete</small> </button>
                                <div class="modal fade" id="delete-category-{{$category->id}}" role="dialog">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="get" action="{{ route('delete_category', ['id'=>$category->id]) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-delete-empty text-danger"></i> Delete Category {{$category->name}}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="card p-2 text-center" style="background-color:red;height:55px">
                                                        Kindly confirm you want to delete Lab Category {{$category->name}} ?
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
                                <div id="edit-inventory-category-{{ $loop->iteration }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">
                                        <!-- Modal content-->
                                        <form class="modal-content" method="POST" action="{{ route('edit-inventory-category', ['id'=>$category->id]) }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Inventory Category</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" class="form-control" name="name" value="{{ $category->name }}" placeholder="Name..." required />
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Description</label>
                                                    <textarea class="form-control" name="description" placeholder="Description..." required>{{ $category->description }}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Image</label>
                                                    <input type="file" class="form-control" name="image" />
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
            @if(count($categories) == 0)
            <div class="alert alert-info">
                <i class="mdi mdi-alert"></i> No Inventory Categories added yet.
            </div>
            @endif
        </div>
    </div>

</main>
<div id="add-inventory-category" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form class="modal-content" method="POST" action="{{route('add_lab_inventory_categories')}}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Category</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Name</label>
                    <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
                </div>
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="control-label">Image</label>
                    <input type="file" class="form-control" name="image" required />
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
@section('script2')



@endsection