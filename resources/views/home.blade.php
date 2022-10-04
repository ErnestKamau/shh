@extends('layouts.app')

@section('title')
  <title>LIMS HOME</title>

	<link rel="stylesheet" href="{{ asset('css/default.css') }}">
	<link rel="stylesheet" href="{{ asset('css/w3.css') }}">
@endsection

@section('content')
  <div id="apps-board">
    <div id="power-off">
      <div class="dropdown">
        <button type="button" class="btn btn-transparent dropdown-toggle" data-toggle="dropdown">
          <i class="mdi mdi-account-circle"></i> {{ Auth::user()->name }}
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 200px">
          <a class="dropdown-item" href="/system-user/{{ Auth::user()->id }}"><i class="mdi mdi-account-details text-primary"></i> &nbsp;&nbsp;My Profile</a>
          <a class="dropdown-item" href="http://127.0.0.1:8000/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="text-danger mdi mdi-power"></i> &nbsp;&nbsp;Sign-Out
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
      </div>
    </div>
    <div id="apps-holder">
			<div class="w3-padding-large" style="width: 100% !important">
				<?php $active = getActiveCompany()?>
				<center><img src="{{$active->logo}}" style="width: 250px; margin-bottom: 30px;"  class="w3-round w3-padding-large"/></center>
			</div>
			<a class="app" href="/lab-dashboard">
				<div class="icon w3-green"><i class="mdi mdi-flask"></i></div>
				<div class="small w3-padding-small">Laboratory</div>
			</a>
			<a class="app" href="/inventory-home">
				<div class="icon w3-blue"><i class="mdi mdi-package-variant"></i></div>
				<div class="small w3-padding-small">Inventory</div>
			</a>
			<a class="app"  href="/equipment-home">
				<div class="icon w3-brown"><i class="mdi mdi-tools"></i></div>
				<div class="small w3-padding-small">Equipment</div>
			</a>
			<!-- <a class="app"  href="/document-manager">
				<div class="icon w3-deep-orange"><i class="mdi mdi-book-open-page-variant"></i></div>
				<div class="small w3-padding-small">Asset Booking</div>
			</a> -->
			<a class="app"  href="/crm-home">
				<div class="icon w3-cyan"><i class="mdi mdi-account-multiple-outline"></i></div>
				<div class="small w3-padding-small">CRM</div>
			</a>
			<a class="app"  href="/personnel-home">
				<div class="icon w3-red"><i class="mdi mdi-account-group"></i></div>
				<div class="small w3-padding-small">Personnel</div>
			</a>
			<a class="app"  href="/full-calendar/view">
				<div class="icon w3-amber"><i class="mdi mdi-calendar"></i></div>
				<div class="small w3-padding-small">System Planner</div>
			</a>
			<!-- <a class="app"  href="/personnel-home">
				<div class="icon w3-grey"><i class="mdi mdi-tools"></i></div>
				<div class="small w3-padding-small">Work Orders</div>
			</a> -->
			@if(\App::environment() == 'local')
			<a class="app" href="/system-settings">
				<div class="icon w3-black"><i class="fas fa-cogs"></i></div>
				<div class="small w3-padding-small">System Settings</div>
			</a>
			@endif
		</div>
  </div>
@endsection
