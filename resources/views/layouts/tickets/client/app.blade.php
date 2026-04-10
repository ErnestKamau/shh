@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('tickets.dashboard') }}"><i class="mdi mdi-ticket"></i>Help Desk</a>
</li>
@endsection

@section('title')
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
</style>
@yield('title2')
@endsection

@section('content')
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-3 col-lg-2">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-ticket fa-3x"></i><br>
				<span class="text-lg text-bold">Help Desk</span>
			</div>
			
			{{-- Dashboard --}}
			<a href="{{ route('tickets.dashboard') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('tickets.dashboard') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-view-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>

			{{-- All Tickets --}}
			<a href="{{ route('tickets.index') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('tickets.index') || request()->routeIs('tickets.show') || request()->routeIs('tickets.create') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-between align-items-center">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class="mdi mdi-ticket-account fa-fw mr-3"></span>
						<span class="menu-collapsed">All Tickets</span>
					</div>
					@if(isset($totalUnread) && $totalUnread > 0)
						<span class="badge badge-danger badge-pill">{{ $totalUnread }}</span>
					@endif
				</div>
			</a>

			{{-- Categories --}}
			<a href="{{ route('tickets.categories') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('tickets.categories') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-tag fa-fw mr-3"></span>
					<span class="menu-collapsed">Categories</span>
				</div>
			</a>

			{{-- Archived Tickets --}}
			<a href="{{ route('tickets.deleted') }}" class="bg-dark list-group-item list-group-item-action {{ request()->routeIs('tickets.deleted') ? 'active' : '' }}">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-archive fa-fw mr-3"></span>
					<span class="menu-collapsed">Archived Tickets</span>
				</div>
			</a>

			<div class="list-group-item copyright-lims p-4 text-center text-white" style="bottom:0">
				Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
			</div>
		</ul>
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="col-sm-8 col-lg-10 py-3" id="main-container-body">
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
		@yield('content2')
	</div>
	<!-- Main Col END -->
</div>
@endsection

@section('script')
<script>
// Livewire event listener for showing messages
document.addEventListener('livewire:init', () => {
    Livewire.on('show-message', (event) => {
        const type = event.type || 'info';
        const message = event.message || 'Action completed';
        showAlert(message, type);
    });
});

// Function to show alerts as toast
function showAlert(message, type) {
    var icon, alertClass;
    
    switch(type) {
        case 'success':
            icon = 'mdi-check-circle';
            alertClass = 'alert-success';
            break;
        case 'error':
        case 'danger':
            icon = 'mdi-alert-circle';
            alertClass = 'alert-danger';
            break;
        case 'warning':
            icon = 'mdi-alert';
            alertClass = 'alert-warning';
            break;
        default:
            icon = 'mdi-information';
            alertClass = 'alert-info';
    }
    
    // Remove any existing alerts
    $('#message-section .alert').remove();
    
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        '<i class="mdi ' + icon + '"></i> ' + message +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
    
    // Add the alert to the message section
    $('#message-section').prepend(alertHtml);
    
    // Auto-hide the alert after 5 seconds
    setTimeout(function() {
        $('#message-section .alert').fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
    
    // Scroll to top to show the message
    $('html, body').animate({ scrollTop: 0 }, 300);
}
</script>
@yield('script2')
@endsection

