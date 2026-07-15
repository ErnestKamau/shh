@extends('layouts.app',['select2'=>true])

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('user_profile') }}"><i class="mdi mdi-account"></i> Profile</a>
</li>
@endsection

@section('title')
<style type="text/css">
    #main-container-body {
        margin-left: 0 !important;
        width: 100% !important;
    }

    #sidebar-container {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="row" id="body-row">
    <div class="py-3" id="main-container-body">
        <div class="px-3 px-md-4">
            <?php
            $items = [
                ['link' => '/', 'name' => 'Home', 'icon' => null],
                ['link' => 'null', 'name' => Auth::user()->name ?? 'Profile', 'icon' => null],
            ];
            ?>
            <x-bread-crumb :items="$items"></x-bread-crumb>

            @livewire('personnel.personnel-user-profile-manager')
        </div>
    </div>
</div>
@endsection
