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
    $items = [
        [
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Stock Movement',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Lab Stock',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
        <i class="mdi mdi-finance"></i> Lab Stock     
    </h3>
    <div class="bg-light p-4">
        <div class="table-responsive">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                <thead class="bg-light p-2">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sub_category as $sub)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>
                            <a href="{{ route('solution-movement-show',['id'=>$sub->id]) }}">{{$sub->name}}</a>
                        </td>
                        <td>{{$sub->category_name}}</td>
                        <td>{{$sub->description}}</td>
                        <td>
                            <img src="{{$sub->image}}" style="height: 100px;width:100px" alt="">
                        </td>
                        <td>
                            <a href="{{ route('solution-movement-show',['id'=>$sub->id]) }}" data-target="tooltip" title="View Stock" class="btn btn-outline-success btn-sm"><i class="mdi mdi-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</main>


@endsection
@section('script2')



@endsection