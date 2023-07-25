@extends('layouts.app', ['select2' => true])

@section('module-name')
    <li class="nav-item">
        <a class="nav-link module-name" style="font-size:12px !important" href="{{ route('home') }}"><i class="mdi mdi-file-document"></i> Customerr Focus</a>
    </li>
@endsection

@section('title')
    <style type="text/css">

    </style>
@endsection


@section('content')

    <div id="body-row" class="p-3" style="width:100%;height:100vh">
        <!-- Sidebar -->

        <!-- sidebar-container END -->

        <!-- MAIN -->
        <div class="" id="main-container-body">
            <div id="message-section" style="padding: 10px 10px 0px 10px !important">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (\Session::has('success') || \Session::has('error'))
                    @if (\Session::has('success'))
                        <div class="alert alert-success center text-lg alert-callout">
                            <i class="fas fa-thumbs-up"></i> {{ Session::get('success') }}
                        </div>
                    @endif
                    @if (\Session::has('error'))
                        <div class="alert alert-danger center text-lg alert-callout">
                            <i class="fas fa-exclamation-triangle"></i> {{ Session::get('error') }}
                        </div>
                    @endif
                @endif
            </div>
            <div class="">
                <?php
                $items = [
                    [
                        'link' => '/',
                        'name' => 'Home',
                        'icon' => null,
                    ],
                    [
                        'link' => 'null',
                        'name' => 'Customer Focus Signing',
                        'icon' => null,
                    ],
                ];
                ?>
                <x-bread-crumb :items="$items"></x-bread-crumb>
               
                
                <center>
                    <div class="card mt-5" style="width: 90%">
                        <div class="card-header" style="font-size:20px">
                            <i class="mdi mdi-file-document"></i> Customer Focus Signing
                        </div>
                        <div class="card-body">
                            <form autocomplete="off" action="{{ route('generateTabletCustomerFocusIndex') }}" method="get"
                                class="p-3" enctype="multipart/form-data">
                                @csrf


                                <div class="alert alert-primary p-2 d-flex">
                                    <i class="mdi mdi-alert-decagram-outline" style="font-size:30px"></i>
                                    <span class="p-2">
                                        Kindly provide the sample / job number below to get the Customer Focus of the
                                        specified sample
                                    </span>
                                </div>

                                <div class="form-group mt-5">
                                    <label for="" class="control-label">Sample Number</label>
                                    <input type="text" name="sample_no" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="" class="control-label"><input type="checkbox" name="is_clustered" value="1" id=""> Is Clustered?</label>
                                </div>
                                <div class="submit-area" style="margin-top:30%">
                                    <button type="submit" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"
                                        class="btn btn-block btn-default"><i
                                            class="mdi mdi-cloud-search-outline"></i> Search</button>
                                </div>


                            </form>


                        </div>
                    </div>
                </center>
            </div>
        </div>
        <!-- Main Col END -->
    </div>


@endsection

@section('script')
    <script></script>
@endsection
